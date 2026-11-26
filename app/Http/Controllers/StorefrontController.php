<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Site;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StorefrontController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected CheckoutService $checkout,
    ) {}

    public function products()
    {
        $site = Site::first();

        $products = Product::with('category')
            ->where('site_id', $site?->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.products', compact('products', 'site'));
    }

    public function product(string $slug)
    {
        $site = Site::first();

        $product = Product::with('category')
            ->where('site_id', $site?->id)
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        return view('storefront.product', compact('product', 'site'));
    }

    public function cart()
    {
        $site = Site::first();
        $items = $this->cart->items();

        return view('storefront.cart', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'site' => $site,
        ]);
    }

    public function addToCart(Product $product, Request $request)
    {
        abort_unless($product->isActive() && $product->isInStock(), 404);

        $quantity = max(1, (int) $request->input('quantity', 1));

        $this->cart->add($product, $quantity);

        return redirect()->route('storefront.cart')
            ->with('success', $product->name . ' added to your cart.');
    }

    public function updateCart(Product $product, Request $request)
    {
        $quantity = (int) $request->input('quantity', 0);

        $this->cart->update($product->id, $quantity);

        return redirect()->route('storefront.cart');
    }

    public function removeCart(Product $product)
    {
        $this->cart->remove($product->id);

        return redirect()->route('storefront.cart');
    }

    public function checkout()
    {
        $site = Site::first();
        $items = $this->cart->items();

        $couponCode = Session::get('checkout_coupon');
        $totals = $this->checkout->totals($couponCode);

        return view('storefront.checkout', [
            'items' => $items,
            'totals' => $totals,
            'couponCode' => $couponCode,
            'site' => $site,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'coupon_code' => 'nullable|string|max:100',
        ]);

        $couponCode = $validated['coupon_code'] ?? null;

        if ($couponCode) {
            Session::put('checkout_coupon', $couponCode);
        }

        $result = $this->checkout->placeOrder($validated, $couponCode);

        if (!$result['order']) {
            return redirect()->route('storefront.checkout')
                ->withErrors($result['errors'])
                ->withInput();
        }

        Session::forget('checkout_coupon');

        return redirect()->route('storefront.success', $result['order'])
            ->with('success', 'Order ' . $result['order']->order_number . ' placed.');
    }

    public function success(Order $order)
    {
        $site = Site::first();

        $order->load('items');

        return view('storefront.success', compact('order', 'site'));
    }
}
