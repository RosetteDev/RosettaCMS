@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Products</h1>
            <p>Catalog management — pricing, stock and availability.</p>
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

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-top: 1rem;">
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);">{{ $stats['total'] }}</div>
            <div class="text-muted small">Total products</div>
        </div>
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--success-color);">{{ $stats['active'] }}</div>
            <div class="text-muted small">Active</div>
        </div>
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--warning-color);">{{ $stats['low_stock'] }}</div>
            <div class="text-muted small">Low stock (≤5)</div>
        </div>
        <div class="card" style="background: var(--light-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--danger-color);">{{ $stats['out_of_stock'] }}</div>
            <div class="text-muted small">Out of stock</div>
        </div>
    </div>

    <div style="padding: 1.25rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div></div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ New Product</a>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow-x: auto;">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->sku)
                                <div class="text-muted small">SKU: {{ $product->sku }}</div>
                            @endif
                        </td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>
                            ${{ number_format($product->effectivePrice(), 2) }}
                            @if($product->isOnSale())
                                <div class="text-muted small text-decoration-line-through">${{ number_format((float) $product->price, 2) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($product->stock <= 0)
                                <span class="badge bg-danger">0</span>
                            @elseif($product->stock <= 5)
                                <span class="badge bg-warning text-dark">{{ $product->stock }}</span>
                            @else
                                <span>{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if($product->isActive())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('storefront.product', $product->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View storefront">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $product->isActive() ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display: inline;" onsubmit="return confirm('Delete this product? Order items keep a snapshot.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding: 2rem;">No products yet — create your first one.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $products->links() }}</div>
    </div>
@endsection
