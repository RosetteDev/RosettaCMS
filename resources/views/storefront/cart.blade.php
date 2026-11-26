@extends('storefront.layout')

@section('content')
    <h1>Your cart</h1>

    @if(empty($items))
        <div class="card center" style="padding: 3rem 1rem; margin-top: 1.5rem;">
            <h2>Your cart is empty</h2>
            <p class="muted">Browse the catalog to add something.</p>
            <a href="{{ route('storefront.products') }}" class="btn btn-primary" style="margin-top: 1rem;">View products</a>
        </div>
    @else
        <table class="line-items" style="margin-top: 1.5rem;">
            <thead>
                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($items as $line)
                    <tr>
                        <td>
                            <a href="{{ route('storefront.product', $line['product']->slug) }}" style="color: inherit; font-weight: 600;">{{ $line['product']->name }}</a>
                            <div class="muted" style="font-size: 0.8rem;">{{ $line['product']->stock }} in stock</div>
                        </td>
                        <td>${{ number_format($line['unit_price'], 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('storefront.cart.update', $line['product']) }}" class="qty" style="display: flex; gap: 0.5rem; align-items: center;">
                                @csrf
                                @method('PUT')
                                <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="{{ $line['product']->stock }}">
                                <button type="submit" class="btn btn-outline btn-sm">Update</button>
                            </form>
                        </td>
                        <td style="font-weight: 700;">${{ number_format($line['line_total'], 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('storefront.cart.remove', $line['product']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></div>
            <div class="row"><span>Tax, discount and shipping are calculated at checkout.</span></div>
            <div class="row" style="margin-top: 0.75rem;">
                <a href="{{ route('storefront.products') }}" class="btn btn-outline">Continue shopping</a>
                <a href="{{ route('storefront.checkout') }}" class="btn btn-primary">Proceed to checkout</a>
            </div>
        </div>
    @endif
@endsection
