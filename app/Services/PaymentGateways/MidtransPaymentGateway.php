<?php

namespace App\Services\PaymentGateways;

use App\Models\DonationPayment;
use App\Models\Pembayaran;
use App\Services\PaymentGateways\Contracts\PaymentGatewayDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransPaymentGateway implements PaymentGatewayDriver
{
    public function createCheckout(Pembayaran $pembayaran): array
    {
        $this->configure();

        if (!$pembayaran->invoice || $pembayaran->status !== 'paid') {
            $pembayaran->invoice = 'INV-' . now()->format('YmdHis') . '-' . $pembayaran->id . '-' . random_int(100, 999);
            $pembayaran->save();
        }

        $orderId = $pembayaran->invoice;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $pembayaran->jumlah,
            ],
            'item_details' => [[
                'id' => (string) $pembayaran->jenis_pembayaran_id,
                'price' => (int) $pembayaran->jumlah,
                'quantity' => 1,
                'name' => (string) optional($pembayaran->jenisPembayaran)->nama,
            ]],
            'customer_details' => [
                'first_name' => (string) optional($pembayaran->warga)->nama,
                'email' => optional($pembayaran->warga)->email ?? 'warga@example.com',
            ],
            'callbacks' => [
                'finish' => route('dashboard'),
                'unfinish' => route('dashboard'),
                'error' => route('dashboard'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans snap token failed', [
                'pembayaran_id' => $pembayaran->id,
                'invoice' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'type' => 'view',
            'view' => 'bayar',
            'data' => compact('snapToken', 'pembayaran'),
        ];
    }

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
            Log::warning('Midtrans callback rejected: invalid signature', [
                'order_id' => $orderId,
            ]);

            return response()->json(['message' => 'invalid signature'], 403);
        }

        $pembayaran = $this->resolvePembayaranByOrderId($orderId);

        if (!$pembayaran) {
            Log::warning('Midtrans callback: pembayaran not found', [
                'order_id' => $orderId,
            ]);

            return response()->json(['message' => 'ok']);
        }

        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status');

        $isPaid = $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if ($pembayaran->status === 'paid' && !$isPaid) {
            return response()->json(['message' => 'ok']);
        }

        if ($isPaid) {
            $updateData = ['status' => 'paid'];

            if (! $pembayaran->tanggal_bayar) {
                $updateData['tanggal_bayar'] = now();
            }

            $pembayaran->fill($updateData);
        } else {
            $pembayaran->status = 'pending';
        }

        $pembayaran->save();

        return response()->json(['message' => 'ok']);
    }

    public function finish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $orderId = (string) $validated['order_id'];

        $pembayaran = $this->resolvePembayaranByOrderId($orderId);

        if (!$pembayaran) {
            return response()->json(['message' => 'not found'], 404);
        }

        $this->configure();

        try {
            $status = Transaction::status($orderId);
            $transactionStatus = (string) data_get($status, 'transaction_status', '');
            $fraudStatus = (string) data_get($status, 'fraud_status', '');

            $isPaid = $transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

            if ($isPaid) {
                $updateData = ['status' => 'paid'];

                if (! $pembayaran->tanggal_bayar) {
                    $updateData['tanggal_bayar'] = now();
                }

                $pembayaran->fill($updateData);
                $pembayaran->save();
            }

            return response()->json([
                'message' => 'ok',
                'status' => $pembayaran->status,
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans finish verification failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'verification failed'], 500);
        }
    }

    public function cancel(Pembayaran $pembayaran): void
    {
        $this->configure();

        if ($pembayaran->invoice) {
            try {
                Transaction::cancel($pembayaran->invoice);
            } catch (\Throwable $e) {
                Log::warning('Midtrans cancel request failed', [
                    'pembayaran_id' => $pembayaran->id,
                    'invoice' => $pembayaran->invoice,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // Donation Payment Methods

    public function createDonationCheckout(DonationPayment $donationPayment): array
    {
        $this->configure();

        if (!$donationPayment->invoice) {
            $donationPayment->invoice = 'DON-' . now()->format('YmdHis') . '-' . $donationPayment->id . '-' . random_int(100, 999);
            $donationPayment->save();
        }

        $orderId = $donationPayment->invoice;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $donationPayment->jumlah,
            ],
            'item_details' => [[
                'id' => 'donation-' . $donationPayment->event_donasi_id,
                'price' => (int) $donationPayment->jumlah,
                'quantity' => 1,
                'name' => 'Donasi: ' . optional($donationPayment->eventDonasi)->nama_event,
            ]],
            'customer_details' => [
                'first_name' => (string) optional($donationPayment->warga)->nama,
                'email' => optional($donationPayment->warga)->email ?? 'warga@example.com',
            ],
            'callbacks' => [
                'finish' => route('donation-payment.finish'),
                'unfinish' => route('donation-payment.cancel', $donationPayment),
                'error' => route('donation-payment.cancel', $donationPayment),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans snap token failed for donation', [
                'donation_payment_id' => $donationPayment->id,
                'invoice' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'type' => 'view',
            'view' => 'donasi-bayar',
            'data' => compact('snapToken', 'donationPayment'),
        ];
    }

    public function finishDonation(Request $request, DonationPayment $donationPayment): void
    {
        $orderId = (string) $request->input('order_id', '');

        if ($orderId === '' || !$donationPayment->invoice) {
            return;
        }

        $this->configure();

        try {
            $status = Transaction::status($orderId);
            $transactionStatus = (string) data_get($status, 'transaction_status', '');
            $fraudStatus = (string) data_get($status, 'fraud_status', '');

            $isPaid = $transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

            if ($isPaid) {
                $updateData = ['status' => 'paid'];

                if (!$donationPayment->tanggal_bayar) {
                    $updateData['tanggal_bayar'] = now();
                }

                $donationPayment->fill($updateData);
                $donationPayment->save();
            }
        } catch (\Throwable $e) {
            Log::error('Midtrans finish verification failed for donation', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function cancelDonation(DonationPayment $donationPayment): void
    {
        $this->configure();

        if ($donationPayment->invoice) {
            try {
                Transaction::cancel($donationPayment->invoice);
            } catch (\Throwable $e) {
                Log::warning('Midtrans cancel request failed for donation', [
                    'donation_payment_id' => $donationPayment->id,
                    'invoice' => $donationPayment->invoice,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function configure(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
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
