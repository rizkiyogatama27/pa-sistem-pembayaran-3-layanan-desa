<?php

namespace App\Services\PaymentGateways;

use App\Models\DonationPayment;
use App\Models\Pembayaran;
use App\Models\WhatsAppReminderLog;
use App\Services\PaymentGateways\Contracts\PaymentGatewayDriver;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirPaymentGateway implements PaymentGatewayDriver
{
    public function createCheckout(Pembayaran $pembayaran): array
    {
        if (!$pembayaran->invoice || $pembayaran->status !== 'paid') {
            $pembayaran->invoice = 'INV-' . now()->format('YmdHis') . '-' . $pembayaran->id . '-' . random_int(100, 999);
            $pembayaran->save();
        }

        $slug = (string) config('services.pakasir.slug');
        $amount = (int) $pembayaran->jumlah;
        $orderId = (string) $pembayaran->invoice;

        $redirectUrl = (string) (config('services.pakasir.redirect_url') ?: route('dashboard'));
        $qrisOnly = (bool) config('services.pakasir.qris_only');

        $query = [
            'order_id' => $orderId,
            'redirect' => $redirectUrl,
        ];

        if ($qrisOnly) {
            $query['qris_only'] = 1;
        }

        $url = 'https://app.pakasir.com/pay/' . $slug . '/' . $amount . '?' . http_build_query($query);

        return [
            'type' => 'redirect',
            'url' => $url,
        ];
    }

    public function callback(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id');
        $amount = (int) $request->input('amount');
        $status = (string) $request->input('status');

        if ($orderId === '' || $amount <= 0) {
            return response()->json(['message' => 'invalid payload'], 422);
        }

        $pembayaran = $this->resolvePembayaranByOrderId($orderId);

        if (!$pembayaran) {
            Log::warning('Pakasir callback: pembayaran not found', [
                'order_id' => $orderId,
            ]);

            return response()->json(['message' => 'ok']);
        }

        if ((int) $pembayaran->jumlah !== $amount) {
            Log::warning('Pakasir callback: amount mismatch', [
                'order_id' => $orderId,
                'expected' => (int) $pembayaran->jumlah,
                'received' => $amount,
            ]);

            return response()->json(['message' => 'amount mismatch'], 400);
        }

        $verified = $this->verifyTransaction($orderId, $amount);

        if (! $verified) {
            return response()->json(['message' => 'verification failed'], 400);
        }

        if ($status === 'completed' || $verified) {
            $updateData = ['status' => 'paid'];

            if (! $pembayaran->tanggal_bayar) {
                $updateData['tanggal_bayar'] = now();
            }

            $pembayaran->fill($updateData);
            $pembayaran->save();
            $this->sendPaidNotification($pembayaran);
        }

        return response()->json(['message' => 'ok']);
    }

    public function finish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $orderId = (string) $validated['order_id'];
        $pembayaran = $this->resolvePembayaranByOrderId($orderId);

        if (! $pembayaran) {
            return response()->json(['message' => 'not found'], 404);
        }

        $verified = $this->verifyTransaction($orderId, (int) $pembayaran->jumlah);

        if ($verified) {
            $updateData = ['status' => 'paid'];

            if (! $pembayaran->tanggal_bayar) {
                $updateData['tanggal_bayar'] = now();
            }

            $pembayaran->fill($updateData);
            $pembayaran->save();
            $this->sendPaidNotification($pembayaran);
        }

        return response()->json([
            'message' => 'ok',
            'status' => $pembayaran->status,
        ]);
    }

    public function cancel(Pembayaran $pembayaran): void
    {
        $payload = [
            'project' => (string) config('services.pakasir.slug'),
            'order_id' => (string) $pembayaran->invoice,
            'amount' => (int) $pembayaran->jumlah,
            'api_key' => (string) config('services.pakasir.api_key'),
        ];

        try {
            Http::asJson()
                ->post('https://app.pakasir.com/api/transactioncancel', $payload)
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Pakasir cancel request failed', [
                'pembayaran_id' => $pembayaran->id,
                'invoice' => $pembayaran->invoice,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // Donation Payment Methods

    public function createDonationCheckout(DonationPayment $donationPayment): array
    {
        if (!$donationPayment->invoice) {
            $donationPayment->invoice = 'DON-' . now()->format('YmdHis') . '-' . $donationPayment->id . '-' . random_int(100, 999);
            $donationPayment->save();
        }

        $slug = (string) config('services.pakasir.slug');
        $amount = (int) $donationPayment->jumlah;
        $orderId = (string) $donationPayment->invoice;

        $redirectUrl = (string) (config('services.pakasir.redirect_url') ?: route('dashboard'));
        $qrisOnly = (bool) config('services.pakasir.qris_only');

        $query = [
            'order_id' => $orderId,
            'redirect' => $redirectUrl,
        ];

        if ($qrisOnly) {
            $query['qris_only'] = 1;
        }

        $url = 'https://app.pakasir.com/pay/' . $slug . '/' . $amount . '?' . http_build_query($query);

        return [
            'type' => 'redirect',
            'url' => $url,
        ];
    }

    public function finishDonation(Request $request, DonationPayment $donationPayment): void
    {
        $orderId = (string) $request->input('order_id', '');

        if ($orderId === '' || !$donationPayment->invoice) {
            return;
        }

        $verified = $this->verifyTransaction($orderId, (int) $donationPayment->jumlah);

        if ($verified) {
            $updateData = ['status' => 'paid'];

            if (!$donationPayment->tanggal_bayar) {
                $updateData['tanggal_bayar'] = now();
            }

            $donationPayment->fill($updateData);
            $donationPayment->save();
        }
    }

    public function cancelDonation(DonationPayment $donationPayment): void
    {
        $payload = [
            'project' => (string) config('services.pakasir.slug'),
            'order_id' => (string) $donationPayment->invoice,
            'amount' => (int) $donationPayment->jumlah,
            'api_key' => (string) config('services.pakasir.api_key'),
        ];

        try {
            Http::asJson()
                ->post('https://app.pakasir.com/api/transactioncancel', $payload)
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Pakasir cancel request failed for donation', [
                'donation_payment_id' => $donationPayment->id,
                'invoice' => $donationPayment->invoice,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function verifyTransaction(string $orderId, int $amount): bool
    {
        $slug = (string) config('services.pakasir.slug');
        $apiKey = (string) config('services.pakasir.api_key');

        if ($slug === '' || $apiKey === '') {
            Log::warning('Pakasir verification skipped: missing credentials');
            return false;
        }

        try {
            $response = Http::get('https://app.pakasir.com/api/transactiondetail', [
                'project' => $slug,
                'amount' => $amount,
                'order_id' => $orderId,
                'api_key' => $apiKey,
            ]);

            if (! $response->ok()) {
                return false;
            }

            $status = (string) data_get($response->json(), 'transaction.status', '');

            return $status === 'completed';
        } catch (\Throwable $e) {
            Log::warning('Pakasir verification failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function sendPaidNotification(Pembayaran $pembayaran): void
    {
        $whatsAppService = app(WhatsAppService::class);

        if (! $whatsAppService->enabled()) {
            return;
        }

        $pembayaran->loadMissing('warga');
        $warga = $pembayaran->warga;

        if (! $warga || trim((string) $warga->no_hp) === '') {
            return;
        }

        $recipient = $whatsAppService->normalizeNumber((string) $warga->no_hp);

        $message = implode("\n", [
            'Halo ' . ($warga->nama ?? 'Warga') . ',',
            'Pembayaran Anda sudah LUNAS.',
            'Periode: ' . ($pembayaran->periode ?? now()->format('Y-m')),
            'Nominal: Rp ' . number_format((int) $pembayaran->jumlah, 0, ',', '.'),
            'Invoice: ' . ($pembayaran->invoice ?? '-'),
            'Terima kasih telah melakukan pembayaran.',
        ]);

        $sent = $whatsAppService->send($recipient, $message);
        $last = $whatsAppService->getLastResponse();

        WhatsAppReminderLog::create([
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $warga->id,
            'recipient' => $recipient,
            'status' => $sent ? 'sent' : 'failed',
            'message' => $message,
            'error_message' => $sent ? null : ($last ? json_encode($last) : 'Provider failed'),
            'sent_at' => $sent ? now() : null,
        ]);
    }

    private function resolvePembayaranByOrderId(string $orderId): ?Pembayaran
    {
        $byInvoice = Pembayaran::query()->where('invoice', $orderId)->first();
        if ($byInvoice) {
            return $byInvoice;
        }

        $rawId = (int) str_replace('ORDER-', '', $orderId);
        if ($rawId > 0) {
            $byRawId = Pembayaran::query()->find($rawId);
            if ($byRawId) {
                return $byRawId;
            }
        }

        if (preg_match('/-(\d+)(?:-\d+)?$/', $orderId, $matches)) {
            $tailId = (int) ($matches[1] ?? 0);
            if ($tailId > 0) {
                return Pembayaran::query()->find($tailId);
            }
        }

        return null;
    }
}
