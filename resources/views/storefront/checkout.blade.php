@extends('storefront.layout')

@section('content')
    <h1>Checkout</h1>

    @if(empty($items))
        <div class="card center" style="padding: 3rem 1rem; margin-top: 1.5rem;">
            <h2>Your cart is empty</h2>
            <p class="muted">Add some products before checking out.</p>
            <a href="{{ route('storefront.products') }}" class="btn btn-primary" style="margin-top: 1rem;">View products</a>
        </div>
    @else
        <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start; margin-top: 1.5rem;">
            <div>
                <h2 class="section-title">Order summary</h2>
                <table class="line-items">
                    <thead>
                        <tr><th>Product</th><th>Qty</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($items as $line)
                            <tr>
                                <td>{{ $line['product']->name }}</td>
                                <td>{{ $line['quantity'] }}</td>
                                <td style="font-weight: 600;">${{ number_format($line['line_total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="totals">
                    <div class="row"><span>Subtotal</span><strong>${{ number_format($totals['subtotal'], 2) }}</strong></div>
                    @if($totals['discount'] > 0)
                        <div class="row" style="color: #166534;"><span>Discount{{ $totals['coupon'] ? ' (' . $totals['coupon']->code . ')' : '' }}</span><strong>-${{ number_format($totals['discount'], 2) }}</strong></div>
                    @endif
                    <div class="row"><span>Tax</span><strong>${{ number_format($totals['tax'], 2) }}</strong></div>
                    <div class="row total"><span>Total</span><span>${{ number_format($totals['total'], 2) }}</span></div>
                </div>
            </div>

            <div>
                <h2 class="section-title">Details</h2>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin-left: 1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('storefront.checkout.place') }}" class="stack">
                    @csrf
                    <label class="field">Full name
                        <input type="text" name="customer_name" class="text" value="{{ old('customer_name') }}" required maxlength="255">
                    </label>
                    <label class="field">Email
                        <input type="email" name="customer_email" class="text" value="{{ old('customer_email') }}" required maxlength="255">
                    </label>
                    <label class="field">Address
                        <textarea name="customer_address" class="text" rows="3" maxlength="1000">{{ old('customer_address') }}</textarea>
                    </label>
                    <label class="field">Coupon code
                        <input type="text" name="coupon_code" class="text" value="{{ old('coupon_code', $couponCode) }}" placeholder="Optional" maxlength="100">
                    </label>
                    <label class="field">Notes
                        <textarea name="notes" class="text" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                    </label>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Place order — ${{ number_format($totals['total'], 2) }}</button>
                    <p class="muted" style="font-size: 0.8rem;">Payment is manual/invoice-based in this foundation — orders are recorded and confirmed offline.</p>
                </form>
            </div>
        </div>
    @endif
@endsection
