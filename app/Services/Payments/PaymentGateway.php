<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * Payment gateway seam.
 *
 * The foundation ships a manual gateway (orders are recorded with a
 * pending payment status and an invoice workflow). A paid module can
 * implement this contract (e.g. Stripe) and bind it via the
 * `payments.default` config key without touching checkout or order code.
 */
interface PaymentGateway
{
    /**
     * Attempt to charge an order for its total.
     *
     * @return array{status: string, reference: ?string, error: ?string}
     *         status is one of Order::PAYMENT_PAID, Order::PAYMENT_PENDING,
     *         or Order::PAYMENT_FAILED
     */
    public function charge(Order $order): array;

    /**
     * Refund a paid order.
     *
     * @return array{status: string, reference: ?string, error: ?string}
     */
    public function refund(Order $order): array;
}
