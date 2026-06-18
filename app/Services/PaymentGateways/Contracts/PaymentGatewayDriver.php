<?php

namespace App\Services\PaymentGateways\Contracts;

use App\Models\DonationPayment;
use App\Models\Pembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface PaymentGatewayDriver
{
    public function createCheckout(Pembayaran $pembayaran): array;

    public function callback(Request $request): JsonResponse;

    public function finish(Request $request): JsonResponse;

    public function cancel(Pembayaran $pembayaran): void;

    public function createDonationCheckout(DonationPayment $donationPayment): array;

    public function finishDonation(Request $request, DonationPayment $donationPayment): void;

    public function cancelDonation(DonationPayment $donationPayment): void;
}
