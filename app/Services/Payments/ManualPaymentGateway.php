<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * Foundation gateway: records the order and leaves payment pending for
 * an offline/invoice workflow. No external calls are made.
 */
class ManualPaymentGateway implements PaymentGateway
{
    public function charge(Order $order): array
    {
        return [
            'status' => Order::PAYMENT_PENDING,
            'reference' => null,
            'error' => null,
        ];
    }

    public function refund(Order $order): array
    {
        return [
            'status' => Order::PAYMENT_REFUNDED,
            'reference' => null,
            'error' => null,
        ];
    }
}
