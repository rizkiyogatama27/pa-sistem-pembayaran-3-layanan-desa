<?php

namespace App\Http\Controllers;

use App\Models\DonationPayment;
use App\Models\EventDonasi;
use App\Models\EventDonasiKontribusi;
use App\Models\Warga;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DonationPaymentController extends Controller
{
    public function __construct(private readonly PaymentGatewayManager $paymentGatewayManager)
    {
    }

        /**
         * Show payment page for donation
         */
        public function pay(DonationPayment $donationPayment): View|RedirectResponse
        {
            // Ensure donation is still pending
            if ($donationPayment->status === 'paid') {
                return back()->with('error', 'Donasi ini sudah diproses.');
            }

            $donationPayment->loadMissing(['eventDonasi', 'warga']);

            if (! $donationPayment->warga || empty($donationPayment->warga->no_hp)) {
                return back()->with('error', 'Nomor HP warga belum diisi. Lengkapi dulu agar notifikasi bisa dikirim.');
            }

            return $this->processDonationPayment($donationPayment);
        }

    /**
     * Initiate donation payment - user wants to donate to an event
     */
    public function create(Request $request, EventDonasi $eventDonasi): View|RedirectResponse
    {
        $validated = $request->validate([
            'nominal' => ['required', 'integer', 'min:1000'],
            'catatan' => ['nullable', 'string'],
        ]);

        if ($eventDonasi->status !== 'aktif') {
            return back()->with('error', 'Event ini belum aktif atau sudah selesai.');
        }

        $warga = $this->resolveWarga();

        if (!$warga) {
            return back()->with('error', 'Data warga belum terhubung ke akun ini.');
        }

        if (empty($warga->no_hp)) {
            return back()->with('error', 'Nomor HP warga belum diisi. Lengkapi dulu agar notifikasi bisa dikirim.');
        }

        // Create donation payment record
        $donationPayment = DonationPayment::create([
            'event_donasi_id' => $eventDonasi->id,
            'warga_id' => $warga->id,
            'is_anonymous' => $request->boolean('anonim'),
            'jumlah' => (int) $validated['nominal'],
            'catatan' => trim((string) ($validated['catatan'] ?? '')),
            'status' => 'pending',
        ]);

        return $this->processDonationPayment($donationPayment);
    }

    /**
     * Process payment through gateway
     */
    private function processDonationPayment(DonationPayment $donationPayment): RedirectResponse|View
    {
        // Create Pembayaran-like object for gateway compatibility
        $donationPayment->load(['eventDonasi', 'warga']);

        // Generate invoice if not exist
        if (!$donationPayment->invoice) {
            $donationPayment->invoice = 'DON-' . now()->format('YmdHis') . '-' . $donationPayment->id . '-' . random_int(100, 999);
            $donationPayment->save();
        }

        try {
            $checkout = $this->paymentGatewayManager->driver()->createDonationCheckout($donationPayment);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal terhubung ke metode pembayaran. Cek koneksi lalu coba lagi.');
        }

        if (($checkout['type'] ?? 'view') === 'redirect') {
            return redirect()->away((string) ($checkout['url'] ?? ''));
        }

        return view((string) ($checkout['view'] ?? 'bayar-donasi'), $checkout['data'] ?? []);
    }

    /**
     * Payment gateway callback for donations
     */
    public function callback(Request $request): JsonResponse
    {
        if (!$request->filled('order_id') && !$request->filled('signature_key')) {
            return response()->json(['message' => 'callback reachable']);
        }

        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key');

        $localSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($localSignature, $signatureKey)) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $donationPayment = DonationPayment::where('invoice', $orderId)->first();

        if (!$donationPayment) {
            return response()->json(['message' => 'ok']);
        }

        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status');

        $isPaid = $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if ($donationPayment->status === 'paid' && !$isPaid) {
            return response()->json(['message' => 'ok']);
        }

        if ($isPaid) {
            $updateData = ['status' => 'paid'];

            if (!$donationPayment->tanggal_bayar) {
                $updateData['tanggal_bayar'] = now();
            }

            $donationPayment->fill($updateData);
        } else {
            $donationPayment->status = 'pending';
        }

        $donationPayment->save();

        // Create contribution record when payment is confirmed
        if ($isPaid && !EventDonasiKontribusi::where('invoice', $orderId)->first()) {
            EventDonasiKontribusi::create([
                'event_donasi_id' => $donationPayment->event_donasi_id,
                'warga_id' => $donationPayment->warga_id,
                'is_anonymous' => (bool) $donationPayment->is_anonymous,
                'tanggal_donasi' => $donationPayment->tanggal_bayar?->toDateString() ?? now()->toDateString(),
                'nominal' => $donationPayment->jumlah,
                'metode' => 'online',
                'status' => 'paid',
                'catatan' => $donationPayment->catatan,
                'invoice' => $orderId,
            ]);
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * Payment completion page
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = (string) $request->input('order_id', '');

        if ($orderId === '') {
            return redirect()->route('user.event-donasi.index')
                ->with('error', 'Data transaksi tidak ditemukan.');
        }

        $donationPayment = DonationPayment::where('invoice', $orderId)->first();

        if (!$donationPayment) {
            return redirect()->route('user.event-donasi.index')
                ->with('error', 'Data donasi tidak ditemukan.');
        }

        // Finalize status
        $this->paymentGatewayManager->driver()->finishDonation($request, $donationPayment);

        if ($donationPayment->status === 'paid') {
            // Create contribution if not exists
            if (!EventDonasiKontribusi::where('invoice', $orderId)->first()) {
                EventDonasiKontribusi::create([
                    'event_donasi_id' => $donationPayment->event_donasi_id,
                    'warga_id' => $donationPayment->warga_id,
                    'is_anonymous' => (bool) $donationPayment->is_anonymous,
                    'tanggal_donasi' => $donationPayment->tanggal_bayar?->toDateString() ?? now()->toDateString(),
                    'nominal' => $donationPayment->jumlah,
                    'metode' => 'online',
                    'status' => 'paid',
                    'catatan' => $donationPayment->catatan,
                    'invoice' => $orderId,
                ]);
            }

            return redirect()->route('user.event-donasi.show', $donationPayment->eventDonasi)
                ->with('success', 'Donasi berhasil diproses. Terima kasih atas partisipasi Anda!');
        }

        return redirect()->route('user.event-donasi.show', $donationPayment->eventDonasi)
            ->with('error', 'Pembayaran donasi belum berhasil. Silakan coba lagi.');
    }

    /**
     * Cancel donation payment
     */
    public function cancel($id): RedirectResponse
    {
        $donationPayment = DonationPayment::findOrFail($id);

        $this->paymentGatewayManager->driver()->cancelDonation($donationPayment);

        $donationPayment->update([
            'invoice' => null,
            'status' => 'pending',
        ]);

        return redirect()->route('user.event-donasi.show', $donationPayment->eventDonasi)
            ->with('success', 'Transaksi dibatalkan. Silakan coba lagi dengan metode pembayaran lain.');
    }

    /**
     * Resolve warga from authenticated user
     */
    private function resolveWarga(): ?Warga
    {
        $user = Auth::user();

        if (($user?->verification_status ?? 'pending') === 'approved' && $user?->warga_id) {
            return Warga::query()->whereKey($user->warga_id)->first();
        }

        if ($user?->keluarga_id) {
            return Warga::query()->where('keluarga_id', $user->keluarga_id)->orderBy('nama')->first();
        }

        return null;
    }
}
