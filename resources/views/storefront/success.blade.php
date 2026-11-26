@extends('storefront.layout')

@section('content')
    <div class="card center" style="padding: 3rem 2rem; max-width: 560px; margin: 2rem auto;">
        <div style="font-size: 2.5rem;">&#10003;</div>
        <h1 style="margin-top: 0.75rem;">Order placed</h1>
        <p class="muted">Thanks — your order has been received.</p>

        <div class="totals" style="text-align: left; margin-top: 1.5rem;">
            <div class="row"><span>Order number</span><strong>{{ $order->order_number }}</strong></div>
            <div class="row"><span>Status</span><strong>{{ ucfirst($order->status) }}</strong></div>
            <div class="row"><span>Payment</span><strong>{{ $order->paymentMethodLabel() }}</strong></div>
            <div class="row"><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></div>
        </div>

        <a href="{{ route('storefront.products') }}" class="btn btn-primary" style="margin-top: 1.5rem;">Continue shopping</a>
    </div>
@endsection
