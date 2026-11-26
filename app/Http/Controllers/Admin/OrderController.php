<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Site;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $siteId = Site::first()?->id;

        $orders = Order::with('items')
            ->where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Order::where('site_id', $siteId)->count(),
            'pending' => Order::where('site_id', $siteId)->where('status', Order::STATUS_PENDING)->count(),
            'paid' => Order::where('site_id', $siteId)->whereIn('status', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_COMPLETED])->count(),
            'revenue' => Order::where('site_id', $siteId)
                ->whereIn('status', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_COMPLETED])
                ->sum('total'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $this->authorizeSite($order);

        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeSite($order);

        $validated = $request->validate([
            'status' => 'required|in:pending,paid,processing,completed,cancelled,refunded',
        ]);

        $status = $validated['status'];

        $order->status = $status;

        // Keep payment status consistent with the order lifecycle.
        $order->payment_status = match ($status) {
            Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_COMPLETED => Order::PAYMENT_PAID,
            Order::STATUS_CANCELLED => Order::PAYMENT_FAILED,
            Order::STATUS_REFUNDED => Order::PAYMENT_REFUNDED,
            default => Order::PAYMENT_PENDING,
        };

        $order->save();

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated to ' . $status . '.');
    }

    protected function authorizeSite(Order $order): void
    {
        abort_unless($order->site_id === Site::first()?->id, 404);
    }
}
