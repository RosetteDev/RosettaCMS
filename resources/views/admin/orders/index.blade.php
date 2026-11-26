@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Orders</h1>
            <p>Orders placed through the storefront checkout.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-top: 1rem;">
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);">{{ $stats['total'] }}</div>
            <div class="text-muted small">Total orders</div>
        </div>
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--warning-color);">{{ $stats['pending'] }}</div>
            <div class="text-muted small">Pending</div>
        </div>
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--success-color);">{{ $stats['paid'] }}</div>
            <div class="text-muted small">Paid / in progress</div>
        </div>
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);">${{ number_format((float) $stats['revenue'], 2) }}</div>
            <div class="text-muted small">Revenue</div>
        </div>
    </div>

    <div style="padding: 1.25rem 0;"></div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow-x: auto;">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><code>{{ $order->order_number }}</code></td>
                        <td>
                            {{ $order->customer_name }}
                            <div class="text-muted small">{{ $order->customer_email }}</div>
                        </td>
                        <td>{{ $order->items->sum('quantity') }}</td>
                        <td style="font-weight: 700;">${{ number_format((float) $order->total, 2) }}</td>
                        <td><span class="badge bg-{{ match($order->status) { 'completed' => 'success', 'paid', 'processing' => 'primary', 'pending' => 'warning text-dark', 'cancelled' => 'secondary', 'refunded' => 'info', default => 'secondary' } }}">{{ ucfirst($order->status) }}</span></td>
                        <td><span class="badge bg-{{ match($order->payment_status) { 'paid' => 'success', 'pending' => 'warning text-dark', 'failed' => 'danger', 'refunded' => 'info', default => 'secondary' } }}">{{ ucfirst($order->payment_status) }}</span></td>
                        <td class="text-muted small">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding: 2rem;">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $orders->links() }}</div>
    </div>
@endsection
