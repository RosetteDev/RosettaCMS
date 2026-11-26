<?php

namespace App\Services\Payments;

use App\Models\Order;

class PaymentService
{
    /**
     * The gateway is resolved from the `payments.default` config key so
     * a paid module can bind its own implementation without touching
     * checkout or order code.
     */
    public function gateway(): PaymentGateway
    {
        $default = config('payments.default', ManualPaymentGateway::class);

        return is_a($default, PaymentGateway::class, true)
            ? $default
            : app($default);
    }

    public function charge(Order $order): array
    {
        return $this->gateway()->charge($order);
    }

    public function refund(Order $order): array
    {
        return $this->gateway()->refund($order);
    }
}
