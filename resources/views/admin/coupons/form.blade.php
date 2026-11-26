@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>{{ $action === 'Create' ? 'New Coupon' : 'Edit Coupon' }}</h1>
            <p>Discount codes applied at checkout.</p>
        </div>
    </div>

    <div class="container" style="max-width: 720px; padding-top: 1.5rem;">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action === 'Create' ? route('admin.coupons.store') : route('admin.coupons.update', $coupon) }}">
            @csrf
            @if($action !== 'Create')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="code" class="form-label"><strong>Code</strong></label>
                <input type="text" name="code" id="code" class="form-control" required maxlength="100"
                       placeholder="SAVE10" value="{{ old('code', $coupon?->code ?? '') }}" style="text-transform: uppercase;">
                <div class="form-text">Letters, numbers, dashes and underscores. Stored uppercase.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label"><strong>Type</strong></label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="percent" {{ old('type', $coupon?->type ?? 'percent') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('type', $coupon?->type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed amount ($)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="value" class="form-label"><strong>Value</strong></label>
                    <input type="number" name="value" id="value" class="form-control" required step="0.01" min="0.01"
                           value="{{ old('value', $coupon?->value ?? '') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="min_subtotal" class="form-label">Min. subtotal <span class="text-muted">(optional)</span></label>
                    <input type="number" name="min_subtotal" id="min_subtotal" class="form-control" step="0.01" min="0"
                           value="{{ old('min_subtotal', $coupon?->min_subtotal ?? '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="max_uses" class="form-label">Max uses <span class="text-muted">(optional)</span></label>
                    <input type="number" name="max_uses" id="max_uses" class="form-control" min="1"
                           value="{{ old('max_uses', $coupon?->max_uses ?? '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="expires_at" class="form-label">Expires <span class="text-muted">(optional)</span></label>
                    <input type="datetime-local" name="expires_at" id="expires_at" class="form-control"
                           value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d\TH:i') ?? '') }}">
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="active" id="active" class="form-check-input" value="1"
                       {{ old('active', $coupon?->active ?? true) ? 'checked' : '' }}>
                <label for="active" class="form-check-label">Active</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $action === 'Create' ? 'Create Coupon' : 'Update Coupon' }}</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
