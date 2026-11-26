<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $siteId = Site::first()?->id;

        $products = Product::with('category')
            ->where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Product::where('site_id', $siteId)->count(),
            'active' => Product::where('site_id', $siteId)->where('status', Product::STATUS_ACTIVE)->count(),
            'low_stock' => Product::where('site_id', $siteId)->where('status', Product::STATUS_ACTIVE)->where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_of_stock' => Product::where('site_id', $siteId)->where('stock', '<=', 0)->count(),
        ];

        return view('admin.products.index', compact('products', 'stats'));
    }

    public function create()
    {
        $categories = Category::where('site_id', Site::first()?->id)->orderBy('name')->get();

        return view('admin.products.form', ['action' => 'Create', 'product' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $validated['site_id'] = Site::first()?->id;
        $validated['user_id'] = auth()->id();

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $this->authorizeSite($product);

        $categories = Category::where('site_id', $product->site_id)->orderBy('name')->get();

        return view('admin.products.form', ['action' => 'Edit', 'product' => $product, 'categories' => $categories]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeSite($product);

        $validated = $this->validateProduct($request, $product);

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $this->authorizeSite($product);

        $product->status = $product->status === Product::STATUS_ACTIVE
            ? Product::STATUS_DRAFT
            : Product::STATUS_ACTIVE;
        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product ' . ($product->status === Product::STATUS_ACTIVE ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeSite($product);

        // Order items keep a snapshot of name/price, so the product may be deleted safely.
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $siteId = Site::first()?->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'slug')->where('site_id', $siteId)->ignore($product?->id),
            ],
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'sale_price' => 'nullable|numeric|min:0|max:99999999.99|lte:price',
            'stock' => 'required|integer|min:0|max:999999',
            'status' => 'required|in:draft,active',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (empty($validated['sale_price'])) {
            $validated['sale_price'] = null;
        }

        if (empty($validated['category_id'])) {
            $validated['category_id'] = null;
        }

        if (empty($validated['sku'])) {
            $validated['sku'] = null;
        }

        return $validated;
    }

    protected function authorizeSite(Product $product): void
    {
        abort_unless($product->site_id === Site::first()?->id, 404);
    }
}
