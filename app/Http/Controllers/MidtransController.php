<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\WhatsAppReminderLog;
use App\Services\PaymentGateways\PaymentGatewayManager;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MidtransController extends Controller
{
    public function __construct(private readonly PaymentGatewayManager $paymentGatewayManager)
    {
    }

    public function pay($id)
    {
        $pembayaran = Pembayaran::with(['warga', 'jenisPembayaran'])->findOrFail($id);
        $user = Auth::user();

        if ($user?->role === 'user' && (int) $user->warga_id !== (int) $pembayaran->warga_id) {
            abort(403);
        }

        if ($pembayaran->status === 'paid') {
            $fallbackRoute = $user?->role === 'admin'
                ? route('pembayaran.index')
                : route('user.tagihan');

            return redirect($fallbackRoute)
                ->with('error', 'Tagihan ini sudah lunas.');
        }

        if ((int) $pembayaran->jumlah <= 0) {
            $fallbackRoute = $user?->role === 'admin'
                ? route('pembayaran.index')
                : route('user.tagihan');

            return redirect($fallbackRoute)
                ->with('error', 'Tagihan ini belum lengkap. Silakan isi meter akhir dan simpan nominalnya dulu.');
        }

        if ($pembayaran->warga && empty($pembayaran->warga->no_hp)) {
            $fallbackRoute = $user?->role === 'admin'
                ? route('pembayaran.index')
                : route('user.tagihan');

            return redirect($fallbackRoute)
                ->with('error', 'Nomor HP warga belum diisi. Lengkapi dulu agar notifikasi WhatsApp bisa dikirim.');
        }

        try {
            $checkout = $this->paymentGatewayManager->driver()->createCheckout($pembayaran);
        } catch (\Throwable $e) {
            $fallbackRoute = $user?->role === 'admin'
                ? route('pembayaran.index')
                : route('user.tagihan');

            return redirect($fallbackRoute)
                ->with('error', 'Gagal terhubung ke metode pembayaran. Cek koneksi lalu coba lagi.');
        }

        if (($checkout['type'] ?? 'view') === 'redirect') {
            return redirect()->away((string) ($checkout['url'] ?? ''));
        }

        return view((string) ($checkout['view'] ?? 'bayar'), $checkout['data'] ?? []);
    }

    public function callback(Request $request)
    {
        return $this->paymentGatewayManager->driver()->callback($request);
    }

    public function finish(Request $request)
    {
        $this->paymentGatewayManager->driver()->finish($request);

        // Extract order_id dan update pembayaran status langsung ke PAID
        $orderId = (string) $request->input('order_id', '');
        
        if ($orderId !== '') {
            // Cari pembayaran berdasarkan invoice (order_id)
            $pembayaran = Pembayaran::where('invoice', $orderId)->first();
            
            if ($pembayaran && $pembayaran->status !== 'paid') {
                // Langsung update status ke paid
                $pembayaran->status = 'paid';
                if (!$pembayaran->tanggal_bayar) {
                    $pembayaran->tanggal_bayar = now();
                }
                $pembayaran->save();
                $this->sendPaidNotification($pembayaran);
            }
            
            if ($pembayaran) {
                $user = Auth::user();
                
                // Redirect ke halaman yang tepat berdasarkan user role
                if ($user && $user->role === 'admin') {
                    return redirect()->route('pembayaran.index')
                        ->with('success', 'Pembayaran berhasil diproses.');
                }
                
                if ($user && $user->role === 'user') {
                    return redirect()->route('user.tagihan')
                        ->with('success', 'Pembayaran berhasil diproses.');
                }
            }
        }
        
        return redirect()->route('dashboard')
            ->with('success', 'Pembayaran berhasil diproses.');
    }

    private function sendPaidNotification(Pembayaran $pembayaran): void
    {
        $whatsAppService = app(WhatsAppService::class);

        if (! $whatsAppService->enabled()) {
            return;
        }

        $warga = $pembayaran->warga()->first();
        $noHp = (string) ($warga?->no_hp ?? '');

        if (trim($noHp) === '') {
            return;
        }

        $recipient = $whatsAppService->normalizeNumber($noHp);

        $nama = trim((string) ($warga?->nama ?? 'Warga'));
        $periode = (string) ($pembayaran->periode ?? now()->format('Y-m'));
        $nominal = 'Rp ' . number_format((int) $pembayaran->jumlah, 0, ',', '.');

        $message = implode("\n", [
            'Halo ' . $nama . ',',
            'Pembayaran Anda sudah LUNAS.',
            'Periode: ' . $periode,
            'Nominal: ' . $nominal,
            'Invoice: ' . ($pembayaran->invoice ?? '-'),
            'Terima kasih telah melakukan pembayaran tepat waktu.',
        ]);

        $sent = $whatsAppService->send($recipient, $message);
        $last = $whatsAppService->getLastResponse();

        WhatsAppReminderLog::create([
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $warga?->id,
            'recipient' => $recipient,
            'status' => $sent ? 'sent' : 'failed',
            'message' => $message,
            'error_message' => $sent ? null : ($last ? json_encode($last) : 'Provider failed'),
            'sent_at' => $sent ? now() : null,
        ]);
    }

    public function cancel($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $user = Auth::user();

        if ($user?->role === 'user' && (int) $user->warga_id !== (int) $pembayaran->warga_id) {
            abort(403);
        }

        if ($pembayaran->status === 'paid') {
            return redirect()->route('user.tagihan')
                ->with('error', 'Tagihan ini sudah lunas, tidak bisa dibatalkan.');
        }

        $this->paymentGatewayManager->driver()->cancel($pembayaran);

        // Reset invoice so next click Bayar creates a fresh order id and allows another method.
        $pembayaran->invoice = null;
        $pembayaran->status = 'pending';
        $pembayaran->save();

        $fallbackRoute = $user?->role === 'admin'
            ? route('pembayaran.index')
            : route('user.tagihan');

        return redirect($fallbackRoute)
            ->with('success', 'Transaksi dibatalkan. Silakan pilih metode pembayaran lain.');
    }
}
