@extends('storefront.layout')

@section('content')
    <a href="{{ route('storefront.products') }}" class="muted" style="text-decoration: none;">&larr; Back to products</a>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start; margin-top: 1.5rem;">
        <div>
            <h1>{{ $product->name }}</h1>
            @if($product->category)
                <p class="muted">{{ $product->category->name }}</p>
            @endif
            <div class="price" style="font-size: 2rem; margin: 1rem 0;">
                ${{ number_format($product->effectivePrice(), 2) }}
                @if($product->isOnSale())
                    <span class="was" style="font-size: 1.1rem;">${{ number_format((float) $product->price, 2) }}</span>
                @endif
            </div>
            @if($product->description)
                <p style="white-space: pre-line;">{{ $product->description }}</p>
            @endif
            <div style="margin-top: 1.25rem;">
                @if($product->sku)
                    <span class="muted" style="font-size: 0.85rem;">SKU: {{ $product->sku }}</span>
                @endif
            </div>
        </div>

        <div class="card">
            @if($product->isInStock())
                <span class="badge badge-ok">In stock ({{ $product->stock }})</span>
                <form method="POST" action="{{ route('storefront.cart.add', $product) }}" style="margin-top: 0.75rem;">
                    @csrf
                    <label class="field">Quantity
                        <input type="number" name="quantity" class="text" min="1" max="{{ $product->stock }}" value="1">
                    </label>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Add to cart</button>
                </form>
            @else
                <span class="badge badge-out">Out of stock</span>
                <p class="muted" style="margin-top: 0.75rem;">This product is currently unavailable.</p>
            @endif
        </div>
    </div>
@endsection
