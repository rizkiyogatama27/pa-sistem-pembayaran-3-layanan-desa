<?php

namespace App\Services\PaymentGateways;

use App\Services\PaymentGateways\Contracts\PaymentGatewayDriver;

class PaymentGatewayManager
{
    public function driver(): PaymentGatewayDriver
    {
        return match (config('services.payment_gateway.driver', 'midtrans')) {
            'pakasir' => new PakasirPaymentGateway(),
            'midtrans' => new MidtransPaymentGateway(),
            default => new MidtransPaymentGateway(),
        };
    }
}
