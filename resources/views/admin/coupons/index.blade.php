@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Coupons</h1>
            <p>Discount codes for the storefront checkout.</p>
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

    <div style="padding: 1.25rem 0; display: flex; justify-content: flex-end;">
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">+ New Coupon</a>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow-x: auto;">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Uses</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><code>{{ $coupon->code }}</code></td>
                        <td>{{ $coupon->type === 'percent' ? 'Percent' : 'Fixed' }}</td>
                        <td>{{ $coupon->type === 'percent' ? $coupon->value . '%' : '$' . number_format((float) $coupon->value, 2) }}</td>
                        <td>{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</td>
                        <td>{{ $coupon->expires_at?->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @if($coupon->isUsable())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" style="display: inline;" onsubmit="return confirm('Delete this coupon?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding: 2rem;">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $coupons->links() }}</div>
    </div>
@endsection
