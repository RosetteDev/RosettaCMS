<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        protected CartService $cart,
        protected PaymentService $payments,
    ) {}

    /**
     * Compute the totals for the current cart.
     *
     * @return array{subtotal: float, discount: float, tax: float, total: float, coupon: ?Coupon, errors: array}
     */
    public function totals(?string $couponCode = null): array
    {
        $subtotal = $this->cart->subtotal();
        $errors = [];
        $discount = 0.0;
        $coupon = null;

        if ($couponCode !== null && $couponCode !== '') {
            $siteId = Site::first()?->id;
            $coupon = Coupon::where('site_id', $siteId)
                ->where('code', Str::upper(trim($couponCode)))
                ->first();

            if (!$coupon) {
                $errors['coupon'] = 'Unknown coupon code.';
            } elseif (!$coupon->isUsable()) {
                $errors['coupon'] = 'This coupon is no longer valid.';
            } elseif (!$coupon->meetsMinimum($subtotal)) {
                $errors['coupon'] = 'Minimum subtotal of ' . number_format((float) $coupon->min_subtotal, 2) . ' required.';
            } else {
                $discount = $coupon->discountFor($subtotal);
            }
        }

        $settings = SiteSetting::forSite();
        $taxRate = (float) ($settings->tax_rate ?? 0);
        $taxable = max(0, $subtotal - $discount);
        $tax = round($taxable * ($taxRate / 100), 2);
        $total = round($taxable + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'coupon' => $coupon,
            'errors' => $errors,
        ];
    }

    /**
     * Place the order from the current cart. Decrements stock, records
     * the coupon, charges the gateway (best-effort) and clears the cart.
     *
     * @return array{order: ?Order, errors: array}
     */
    public function placeOrder(array $data, ?string $couponCode = null): array
    {
        $items = $this->cart->items();

        if (empty($items)) {
            return ['order' => null, 'errors' => ['cart' => 'Your cart is empty.']];
        }

        $totals = $this->totals($couponCode);

        if (!empty($totals['errors'])) {
            return ['order' => null, 'errors' => $totals['errors']];
        }

        $site = Site::first();
        $user = auth()->user();

        // Re-check stock before charging.
        foreach ($items as $line) {
            $product = $line['product'];

            if ($line['quantity'] > (int) $product->stock) {
                return [
                    'order' => null,
                    'errors' => ['cart' => "Not enough stock for {$product->name}."],
                ];
            }
        }

        $order = null;

        DB::transaction(function () use ($items, $totals, $site, $user, $data, $couponCode, &$order) {
            $order = Order::create([
                'site_id' => $site?->id,
                'user_id' => $user?->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'coupon_code' => $totals['coupon']?->code,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_address' => $data['customer_address'] ?? null,
                'payment_method' => 'manual',
                'payment_status' => Order::PAYMENT_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            if ($totals['coupon']) {
                $totals['coupon']->increment('used_count');
            }
        });

        // Payment is best-effort: a failing gateway never cancels an order.
        // The manual foundation gateway always returns pending.
        try {
            $result = $this->payments->charge($order);
            $order->payment_status = $result['status'] ?? $order->payment_status;
            $order->save();

            if ($result['status'] === Order::PAYMENT_PAID) {
                $order->status = Order::STATUS_PAID;
                $order->save();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $this->cart->clear();

        return ['order' => $order, 'errors' => []];
    }

    /**
     * Generate a unique, human-friendly order number.
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
