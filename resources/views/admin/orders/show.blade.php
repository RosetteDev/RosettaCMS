@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>{{ $order->order_number }}</h1>
            <p>{{ $order->customer_name }} — {{ $order->customer_email }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="row" style="margin-top: 1.5rem; gap: 1rem;">
        <div class="col-lg-7">
            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.25rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Items</h2>
                <div class="table-responsive"><table class="table mb-0">
                    <thead>
                        <tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit</th><th>Line</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="text-muted small">{{ $item->sku ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                                <td style="font-weight: 600;">${{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
                <div style="border-top: 1px solid var(--border-color); margin-top: 1rem; padding-top: 1rem;">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format((float) $order->subtotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span><strong>-${{ number_format((float) $order->discount, 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Tax</span><strong>${{ number_format((float) $order->tax, 2) }}</strong></div>
                    <div class="d-flex justify-content-between" style="font-size: 1.2rem; border-top: 1px solid var(--border-color); margin-top: 0.5rem; padding-top: 0.75rem;"><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem;">
                <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Customer</h2>
                <div><strong>{{ $order->customer_name }}</strong></div>
                <div class="text-muted">{{ $order->customer_email }}</div>
                @if($order->customer_address)
                    <div class="text-muted small" style="white-space: pre-line; margin-top: 0.5rem;">{{ $order->customer_address }}</div>
                @endif
                @if($order->notes)
                    <div class="text-muted small" style="margin-top: 0.5rem;"><strong>Notes:</strong> {{ $order->notes }}</div>
                @endif
            </div>

            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.25rem;">
                <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Order status</h2>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                    @csrf
                    <div class="mb-3">
                        <select name="status" class="form-select" required>
                            @foreach(['pending', 'paid', 'processing', 'completed', 'cancelled', 'refunded'] as $status)
                                <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Update status</button>
                </form>
                <div class="text-muted small" style="margin-top: 1rem;">
                    Payment: <strong>{{ $order->paymentMethodLabel() }}</strong> ({{ ucfirst($order->payment_status) }})<br>
                    Placed {{ $order->created_at->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 1.25rem;">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">&larr; Back to orders</a>
    </div>
@endsection
