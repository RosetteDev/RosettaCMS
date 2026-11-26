@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>{{ $action === 'Create' ? 'New Product' : 'Edit Product' }}</h1>
            <p>Pricing, stock and availability.</p>
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

        <form method="POST" action="{{ $action === 'Create' ? route('admin.products.store') : route('admin.products.update', $product) }}">
            @csrf
            @if($action !== 'Create')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label"><strong>Name</strong></label>
                <input type="text" name="name" id="name" class="form-control" required maxlength="255"
                       value="{{ old('name', $product?->name ?? '') }}">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">Slug <span class="text-muted">(optional)</span></label>
                    <input type="text" name="slug" id="slug" class="form-control" maxlength="255"
                           placeholder="auto-generated" value="{{ old('slug', $product?->slug ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sku" class="form-label">SKU <span class="text-muted">(optional)</span></label>
                    <input type="text" name="sku" id="sku" class="form-control" maxlength="100"
                           value="{{ old('sku', $product?->sku ?? '') }}">
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="5">{{ old('description', $product?->description ?? '') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $product?->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="price" class="form-label"><strong>Price</strong></label>
                    <input type="number" name="price" id="price" class="form-control" required step="0.01" min="0"
                           value="{{ old('price', $product?->price ?? '0.00') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="sale_price" class="form-label">Sale price <span class="text-muted">(optional)</span></label>
                    <input type="number" name="sale_price" id="sale_price" class="form-control" step="0.01" min="0"
                           value="{{ old('sale_price', $product?->sale_price ?? '') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="stock" class="form-label"><strong>Stock</strong></label>
                    <input type="number" name="stock" id="stock" class="form-control" required min="0"
                           value="{{ old('stock', $product?->stock ?? 0) }}">
                </div>
                <div class="col-md-8 mb-3">
                    <label for="status" class="form-label"><strong>Status</strong></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="draft" {{ old('status', $product?->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $product?->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $action === 'Create' ? 'Create Product' : 'Update Product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
