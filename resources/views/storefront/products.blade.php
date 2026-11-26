@extends('storefront.layout')

@section('content')
    <h1>Products</h1>
    <p class="muted">Browse the catalog — everything listed is available to order now.</p>

    @if($products->isEmpty())
        <div class="card center" style="padding: 3rem 1rem;">
            <h2>No products yet</h2>
            <p class="muted">Products added in the admin will appear here.</p>
        </div>
    @else
        <div class="grid">
            @foreach($products as $product)
                <div class="card">
                    @if($product->isOnSale())
                        <span class="badge badge-sale">Sale</span>
                    @endif
                    <h2><a href="{{ route('storefront.product', $product->slug) }}" style="color: inherit; text-decoration: none;">{{ $product->name }}</a></h2>
                    @if($product->category)
                        <span class="muted" style="font-size: 0.85rem;">{{ $product->category->name }}</span>
                    @endif
                    @if($product->description)
                        <p class="muted" style="font-size: 0.9rem;">{{ Str::limit($product->description, 110) }}</p>
                    @endif
                    <div class="price">
                        ${{ number_format($product->effectivePrice(), 2) }}
                        @if($product->isOnSale())
                            <span class="was">${{ number_format((float) $product->price, 2) }}</span>
                        @endif
                    </div>
                    @if(!$product->isInStock())
                        <span class="badge badge-out">Out of stock</span>
                    @else
                        <span class="badge badge-ok">In stock ({{ $product->stock }})</span>
                    @endif
                    <a href="{{ route('storefront.product', $product->slug) }}" class="btn btn-primary btn-sm" style="align-self: flex-start;">View</a>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 1.5rem;">{{ $products->links() }}</div>
    @endif
@endsection
