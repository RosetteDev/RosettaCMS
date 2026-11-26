<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const KEY = 'cart';

    /**
     * All cart lines with product details and line totals.
     *
     * @return array<int, array{product: Product, quantity: int, unit_price: float, line_total: float}>
     */
    public function items(): array
    {
        $lines = [];

        foreach ($this->raw() as $productId => $quantity) {
            $product = Product::find($productId);

            if (!$product || !$product->isActive()) {
                continue;
            }

            $quantity = max(1, (int) $quantity);
            $unitPrice = $product->effectivePrice();

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $quantity, 2),
            ];
        }

        return $lines;
    }

    public function raw(): array
    {
        return Session::get(self::KEY, []);
    }

    public function add(Product $product, int $quantity = 1): void
    {
        if (!$product->isActive() || !$product->isInStock()) {
            return;
        }

        $raw = $this->raw();
        $current = (int) ($raw[$product->id] ?? 0);
        $raw[$product->id] = min($current + $quantity, $product->stock);
        Session::put(self::KEY, $raw);
    }

    public function update(int $productId, int $quantity): void
    {
        $raw = $this->raw();

        if ($quantity <= 0) {
            unset($raw[$productId]);
        } else {
            $product = Product::find($productId);

            if ($product) {
                $raw[$productId] = min($quantity, max(1, (int) $product->stock));
            }
        }

        Session::put(self::KEY, $raw);
    }

    public function remove(int $productId): void
    {
        $raw = $this->raw();
        unset($raw[$productId]);
        Session::put(self::KEY, $raw);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }

    public function subtotal(): float
    {
        return round(array_sum(array_column($this->items(), 'line_total')), 2);
    }
}
