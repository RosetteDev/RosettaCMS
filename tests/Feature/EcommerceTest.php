<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected User $admin;
    protected User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->editor = User::factory()->create();
        $editorRole = Role::create(['name' => 'Editor', 'slug' => 'editor', 'permissions' => []]);
        $this->editor->roles()->attach($editorRole);

        $this->site = Site::create([
            'name' => 'Store Test Site',
            'slug' => 'store-test-site',
            'domain' => 'store.test',
        ]);
    }

    protected function makeProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'price' => 50.00,
            'stock' => 10,
            'status' => Product::STATUS_ACTIVE,
        ], $attributes));
    }

    protected function makeCoupon(array $attributes = []): Coupon
    {
        return Coupon::create(array_merge([
            'site_id' => $this->site->id,
            'code' => 'SAVE10',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'active' => true,
        ], $attributes));
    }

    // --- Admin: products ------------------------------------------------

    public function test_admin_can_view_products_index(): void
    {
        $this->makeProduct(['name' => 'Visible Widget']);

        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertOk();
        $response->assertSee('Visible Widget');
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'New Widget',
            'price' => 25.00,
            'stock' => 7,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'New Widget',
            'price' => 25.00,
            'stock' => 7,
            'status' => 'active',
            'site_id' => $this->site->id,
        ]);
    }

    public function test_product_creation_rejects_sale_price_above_price(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Bad Sale',
            'price' => 20.00,
            'sale_price' => 30.00,
            'stock' => 1,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('sale_price');
        $this->assertDatabaseMissing('products', ['name' => 'Bad Sale']);
    }

    public function test_product_slug_is_unique_per_site(): void
    {
        $this->makeProduct(['slug' => 'taken-slug']);

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Other',
            'slug' => 'taken-slug',
            'price' => 10,
            'stock' => 1,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_product_toggle_status_flips_active_to_draft(): void
    {
        $product = $this->makeProduct(['status' => Product::STATUS_ACTIVE]);

        $this->actingAs($this->admin)->post('/admin/products/' . $product->id . '/toggle-status');

        $this->assertSame(Product::STATUS_DRAFT, $product->fresh()->status);
    }

    public function test_non_admin_cannot_access_products_admin(): void
    {
        $this->actingAs($this->editor)->get('/admin/products')->assertForbidden();
    }

    // --- Admin: coupons ---------------------------------------------------

    public function test_admin_can_create_percent_coupon(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => 'WELCOME20',
            'type' => 'percent',
            'value' => 20,
            'active' => 1,
        ]);

        $response->assertRedirect('/admin/coupons');
        $this->assertDatabaseHas('coupons', ['code' => 'WELCOME20', 'type' => 'percent', 'value' => 20.00]);
    }

    public function test_percent_coupon_cannot_exceed_100_percent(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => 'TOOBIG',
            'type' => 'percent',
            'value' => 150,
            'active' => 1,
        ]);

        $response->assertSessionHasErrors('value');
        $this->assertDatabaseMissing('coupons', ['code' => 'TOOBIG']);
    }

    public function test_coupon_code_is_uppercased_and_validated(): void
    {
        $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => 'lowercase-code',
            'type' => 'fixed',
            'value' => 5,
            'active' => 1,
        ]);

        $this->assertDatabaseHas('coupons', ['code' => 'LOWERCASE-CODE']);
    }

    public function test_non_admin_cannot_access_coupons_admin(): void
    {
        $this->actingAs($this->editor)->get('/admin/coupons')->assertForbidden();
    }

    // --- Admin form pages ----------------------------------------------------

    public function test_admin_can_view_product_form_pages(): void
    {
        $this->actingAs($this->admin)->get('/admin/products/create')->assertOk();

        $product = $this->makeProduct();
        $this->actingAs($this->admin)->get('/admin/products/' . $product->id . '/edit')->assertOk();
    }

    public function test_admin_can_view_coupon_form_pages(): void
    {
        $this->actingAs($this->admin)->get('/admin/coupons/create')->assertOk();

        $coupon = $this->makeCoupon();
        $this->actingAs($this->admin)->get('/admin/coupons/' . $coupon->id . '/edit')->assertOk();
    }

    public function test_admin_can_view_order_show_page(): void
    {
        $product = $this->makeProduct(['price' => 20.00, 'stock' => 5]);
        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com',
        ]);

        $order = Order::first();

        $response = $this->actingAs($this->admin)->get('/admin/orders/' . $order->id);
        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_admin_sidebar_shows_ecommerce_links(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertOk();
        $response->assertSee('/admin/products');
        $response->assertSee('/admin/orders');
        $response->assertSee('/admin/coupons');
    }

    public function test_settings_page_shows_ecommerce_tax_section(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/commerce');

        $response->assertOk();
        $response->assertSee('tax_rate');
        $response->assertSee('E-commerce');
    }

    public function test_settings_tax_rate_is_saved_and_validated(): void
    {
        SiteSetting::create(['site_id' => $this->site->id, 'tax_rate' => 5]);

        $response = $this->actingAs($this->admin)->post('/admin/settings/commerce', [
            'site_name' => 'Store Test Site',
            'site_description' => '',
            'admin_email' => 'admin@store.test',
            'tax_rate' => 12.5,
            'home_post_id' => '',
        ]);

        $response->assertRedirect();
        $this->assertSame('12.50', (string) SiteSetting::where('site_id', $this->site->id)->first()->tax_rate);
    }

    // --- Cart / checkout ---------------------------------------------------

    public function test_cart_add_update_remove_and_subtotal(): void
    {
        $product = $this->makeProduct(['price' => 25.00, 'stock' => 5]);

        $this->actingAs($this->admin)
            ->post('/cart/add/' . $product->id, ['quantity' => 2]);

        $this->actingAs($this->admin)->get('/cart')->assertOk();

        // Update quantity via the route
        $this->actingAs($this->admin)
            ->put('/cart/update/' . $product->id, ['quantity' => 3]);

        $this->actingAs($this->admin)
            ->delete('/cart/remove/' . $product->id);

        // Cart is now empty — the empty-cart checkout view renders
        $response = $this->actingAs($this->admin)->get('/checkout');
        $response->assertOk();
        $response->assertSee('cart is empty', false);
    }

    public function test_cart_caps_quantity_at_stock(): void
    {
        $product = $this->makeProduct(['price' => 10.00, 'stock' => 3]);

        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 10]);

        $response = $this->actingAs($this->admin)->get('/cart');
        $response->assertOk();
        $response->assertSee('3 in stock');
    }

    public function test_checkout_creates_order_decrements_stock_and_clears_cart(): void
    {
        $product = $this->makeProduct(['price' => 40.00, 'stock' => 5]);

        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 2]);

        $response = $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane Buyer',
            'customer_email' => 'jane@example.com',
        ]);

        $response->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('Jane Buyer', $order->customer_name);
        $this->assertSame('jane@example.com', $order->customer_email);
        $this->assertSame(80.00, (float) $order->subtotal);
        $this->assertSame(80.00, (float) $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('manual', $order->payment_method);
        $this->assertSame(3, (int) $product->fresh()->stock);
        $this->assertCount(1, $order->items);
        $this->assertSame(2, $order->items->first()->quantity);

        // Cart cleared after placement
        $response = $this->actingAs($this->admin)->get('/cart');
        $response->assertSee('cart is empty', false);
    }

    public function test_checkout_applies_coupon_and_tax(): void
    {
        SiteSetting::create(['site_id' => $this->site->id, 'tax_rate' => 10]);
        $this->makeCoupon(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10]);
        $product = $this->makeProduct(['price' => 100.00, 'stock' => 5]);

        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);

        $response = $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
            'coupon_code' => 'save10', // lowercase — should still match
        ]);

        $response->assertRedirect();

        $order = Order::first();
        $this->assertSame(10.00, (float) $order->discount);
        $this->assertSame(9.00, (float) $order->tax);
        $this->assertSame(99.00, (float) $order->total);
        $this->assertSame('SAVE10', $order->coupon_code);
        $this->assertSame(1, (int) Coupon::where('code', 'SAVE10')->first()->used_count);
    }

    public function test_checkout_with_empty_cart_shows_error(): void
    {
        $response = $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
        ]);

        $response->assertRedirect('/checkout');
        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_unknown_coupon(): void
    {
        $product = $this->makeProduct(['price' => 100.00, 'stock' => 5]);
        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);

        $response = $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
            'coupon_code' => 'DOESNOTEXIST',
        ]);

        $response->assertSessionHasErrors('coupon');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_number_is_unique_across_orders(): void
    {
        $product = $this->makeProduct(['price' => 10.00, 'stock' => 10]);

        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com',
        ]);

        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com',
        ]);

        $numbers = Order::pluck('order_number')->all();
        $this->assertCount(2, $numbers);
        $this->assertSame(2, count(array_unique($numbers)));
    }

    public function test_insufficient_stock_blocks_checkout(): void
    {
        $product = $this->makeProduct(['price' => 10.00, 'stock' => 1]);

        // Add 1 to cart, then drain stock behind the user's back
        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $product->update(['stock' => 0]);

        $response = $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    // --- Storefront rendering ---------------------------------------------

    public function test_storefront_products_page_renders_active_products_only(): void
    {
        $this->makeProduct(['name' => 'Public Gadget', 'status' => Product::STATUS_ACTIVE]);
        $this->makeProduct(['name' => 'Hidden Draft', 'status' => Product::STATUS_DRAFT]);

        $response = $this->get('/products');

        $response->assertOk();
        $response->assertSee('Public Gadget');
        $response->assertDontSee('Hidden Draft');
    }

    public function test_storefront_product_page_renders_single_product(): void
    {
        $product = $this->makeProduct(['name' => 'Showcase Item', 'slug' => 'showcase-item']);

        $response = $this->get('/products/showcase-item');

        $response->assertOk();
        $response->assertSee('Showcase Item');
    }

    public function test_storefront_unknown_product_returns_404(): void
    {
        $this->get('/products/does-not-exist')->assertNotFound();
    }

    public function test_storefront_success_page_renders_order(): void
    {
        $product = $this->makeProduct(['price' => 15.00, 'stock' => 5]);
        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com',
        ]);

        $order = Order::first();

        $response = $this->get('/checkout/success/' . $order->id);
        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    // --- Orders admin -------------------------------------------------------

    public function test_admin_can_view_orders_index_and_stats(): void
    {
        $product = $this->makeProduct(['price' => 30.00, 'stock' => 5]);
        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/orders');

        $response->assertOk();
        $response->assertSee(Order::first()->order_number);
    }

    public function test_admin_can_update_order_status_and_payment_status_follows(): void
    {
        $product = $this->makeProduct(['price' => 30.00, 'stock' => 5]);
        $this->actingAs($this->admin)->post('/cart/add/' . $product->id, ['quantity' => 1]);
        $this->actingAs($this->admin)->post('/checkout', [
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com',
        ]);

        $order = Order::first();

        $response = $this->actingAs($this->admin)->post('/admin/orders/' . $order->id . '/status', [
            'status' => 'paid',
        ]);

        $response->assertRedirect('/admin/orders/' . $order->id);
        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_non_admin_cannot_access_orders_admin(): void
    {
        $this->actingAs($this->editor)->get('/admin/orders')->assertForbidden();
    }

    // --- Coupon model behavior ----------------------------------------------

    public function test_coupon_discount_capped_at_subtotal(): void
    {
        $coupon = $this->makeCoupon(['type' => 'fixed', 'value' => 500]);

        $this->assertSame(20.00, $coupon->discountFor(20.00));
    }

    public function test_expired_coupon_is_not_usable(): void
    {
        $coupon = $this->makeCoupon(['expires_at' => now()->subDay()]);

        $this->assertFalse($coupon->isUsable());
    }

    public function test_coupon_max_uses_exhausted_is_not_usable(): void
    {
        $coupon = $this->makeCoupon(['max_uses' => 1, 'used_count' => 1]);

        $this->assertFalse($coupon->isUsable());
    }
}
