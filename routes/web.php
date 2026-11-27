<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\IpRulesController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Site;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\InlineEditController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\Admin\WordPressController;
use App\Http\Controllers\Admin\WidgetController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\TranslationStringController;
use App\Http\Controllers\Admin\ReusableBlockController;
use App\Http\Controllers\Admin\RevisionController;
use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\View;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontendController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');
Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
Route::get('/posts/{slug}', [FrontendController::class, 'post'])->name('post.show');
Route::post('/posts/{slug}/comments', [FrontendController::class, 'storeComment'])
    ->middleware('throttle:5,1')
    ->name('comments.store');
Route::get('/pages/{slug}', [FrontendController::class, 'page'])->name('page.show');
Route::get('/categories/{slug}', [FrontendController::class, 'category'])->name('category.show');
Route::get('/tags/{slug}', [FrontendController::class, 'tag'])->name('tag.show');
Route::get('/search', [FrontendController::class, 'search'])->name('search');
Route::get('/sitemap.xml', [FrontendController::class, 'sitemap'])->name('sitemap');
Route::get('/feed', [FrontendController::class, 'feed'])->name('feed');
Route::get('/robots.txt', [FrontendController::class, 'robots'])->name('robots');
Route::get('/language/{code}', function (string $code) {
    $language = \App\Models\Language::where('code', $code)->where('is_active', true)->first();
    if (!$language) {
        return redirect()->back();
    }
    session(['active_language' => $language->code]);
    return redirect()->back();
})->name('language.switch');

// Public storefront (e-commerce foundation)
Route::get('/products', [StorefrontController::class, 'products'])->name('storefront.products');
Route::get('/products/{slug}', [StorefrontController::class, 'product'])->name('storefront.product');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('storefront.cart');
Route::post('/cart/add/{product}', [StorefrontController::class, 'addToCart'])->name('storefront.cart.add');
Route::put('/cart/update/{product}', [StorefrontController::class, 'updateCart'])->name('storefront.cart.update');
Route::delete('/cart/remove/{product}', [StorefrontController::class, 'removeCart'])->name('storefront.cart.remove');
Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('storefront.checkout');
Route::post('/checkout', [StorefrontController::class, 'placeOrder'])->name('storefront.checkout.place');
Route::get('/checkout/success/{order}', [StorefrontController::class, 'success'])->name('storefront.success');

// Installation routes
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/install/check', [InstallController::class, 'check'])->name('install.check');
    Route::get('/install', [InstallController::class, 'form'])->name('install');
    Route::post('/install', [InstallController::class, 'process'])->name('install.process');
});

// WordPress-compatible read-only REST API.
Route::prefix('wp-json/wp/v2')->name('wp.')
    ->middleware('throttle:120,1')
    ->group(function () {
        Route::get('/posts', [ContentController::class, 'wordpressPosts'])->name('posts');
        Route::get('/posts/{slug}', [ContentController::class, 'wordpressPost'])->name('posts.show');
        Route::get('/pages', [ContentController::class, 'wordpressPages'])->name('pages');
        Route::get('/pages/{slug}', [ContentController::class, 'wordpressPage'])->name('pages.show');
        Route::get('/categories', [ContentController::class, 'wordpressCategories'])->name('categories');
        Route::get('/tags', [ContentController::class, 'wordpressTags'])->name('tags');
    });

Route::get('/wp-json', [ContentController::class, 'wordpressIndex'])
    ->middleware('throttle:120,1')
    ->name('wp.index');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Account profile (available to every signed-in user, including subscribers)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Two-factor authentication (self-service)
    Route::post('/profile/2fa/enable', [TwoFactorController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [TwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [TwoFactorController::class, 'disable'])->name('profile.2fa.disable');
});

// Inline editing routes
Route::middleware('can:inline_edit')->group(function () {
    Route::get('/inline-edit/{post}', [InlineEditController::class, 'show'])->name('inline-edit.show');
    Route::put('/inline-edit/{post}/blocks/{block}', [InlineEditController::class, 'updateBlock'])->name('inline-edit.blocks.update');
    Route::delete('/inline-edit/{post}/blocks/{block}', [InlineEditController::class, 'destroyBlock'])->name('inline-edit.blocks.destroy');
});

// Preview routes
Route::middleware('auth')->group(function () {
    Route::get('/preview/{post}', [PreviewController::class, 'show'])->name('preview.show');
    Route::post('/preview/{post}', [PreviewController::class, 'preview'])->name('preview.update');
    Route::post('/preview/theme', [PreviewController::class, 'themePreview'])->name('preview.theme');
    Route::post('/preview/exit', [PreviewController::class, 'exitThemePreview'])->name('preview.exit');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Two-factor challenge (session carries the pending user id)
    Route::get('/login/2fa', [LoginController::class, 'showTwoFactorForm'])->name('login.2fa');
    Route::post('/login/2fa', [LoginController::class, 'verifyTwoFactor'])->name('login.2fa.verify')
        ->middleware('throttle:10,1');
    
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email')
        ->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store')
        ->middleware('throttle:3,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// API keys management (any signed-in user manages their own keys)
Route::middleware('auth')->prefix('admin/api-keys')->name('admin.api-keys.')->group(function () {
    Route::get('/', [ApiKeyController::class, 'index'])->name('index');
    Route::get('/create', [ApiKeyController::class, 'create'])->name('create');
    Route::post('/', [ApiKeyController::class, 'store'])->name('store');
    Route::get('/{apiKey}', [ApiKeyController::class, 'show'])->name('show');
    Route::delete('/{apiKey}', [ApiKeyController::class, 'destroy'])->name('destroy');
});

// Admin routes
Route::middleware(['auth', 'role:admin|editor', 'log.activity'])->group(function () {
    // Admin dashboard
    Route::get('/admin', function () {
        return redirect()->route('dashboard');
    })->name('admin');

    // Posts management
    Route::prefix('admin/posts')->name('admin.posts.')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('index');
        Route::get('/create', [PostController::class, 'create'])->name('create');
        Route::post('/', [PostController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
        Route::get('/{post}', [PostController::class, 'show'])->name('show');
        Route::put('/{post}', [PostController::class, 'update'])->name('update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
        
        // Bulk actions
        Route::post('/bulk', [PostController::class, 'bulk'])->name('bulk');
    });

    // Media management
    Route::prefix('admin/media')->name('admin.media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('/', [MediaController::class, 'store'])->name('store');

        // Static routes must come before /{media}.
        Route::get('/picker', [MediaController::class, 'picker'])->name('picker');
        Route::post('/bulk', [MediaController::class, 'bulk'])->name('bulk');
        Route::get('/folders', [MediaController::class, 'folders'])->name('folders');
        Route::post('/folders', [MediaController::class, 'createFolder'])->name('createFolder');
        Route::delete('/folders', [MediaController::class, 'deleteFolder'])->name('deleteFolder');

        // Metadata update
        Route::put('/{media}/metadata', [MediaController::class, 'updateMetadata'])->name('updateMetadata');

        Route::get('/{media}', [MediaController::class, 'show'])->whereNumber('media')->name('show');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->whereNumber('media')->name('destroy');
    });

    // Sites management - Admin only
    Route::prefix('admin/sites')->middleware('role:admin')->name('admin.sites.')->group(function () {
        Route::get('/', [SiteController::class, 'index'])->name('index');
        Route::get('/create', [SiteController::class, 'create'])->name('create');
        Route::post('/', [SiteController::class, 'store'])->name('store');
        Route::get('/{site}', [SiteController::class, 'show'])->name('show');
        Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('/{site}', [SiteController::class, 'update'])->name('update');
        Route::delete('/{site}', [SiteController::class, 'destroy'])->name('destroy');
    });

    // Pages management
    Route::prefix('admin/pages')->name('admin.pages.')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index');
        Route::get('/create', [PageController::class, 'create'])->name('create');
        Route::post('/', [PageController::class, 'store'])->name('store');
        Route::get('/{page}/edit', [PageController::class, 'edit'])->name('edit');
        Route::get('/{page}', [PageController::class, 'show'])->name('show');
        Route::put('/{page}', [PageController::class, 'update'])->name('update');
        Route::delete('/{page}', [PageController::class, 'destroy'])->name('destroy');
    });

    // Blocks management
    Route::prefix('admin/pages/{post}/blocks')->name('admin.pages.blocks.')->group(function () {
        Route::get('/', [BlockController::class, 'index'])->name('index');
        Route::post('/', [BlockController::class, 'store'])->name('store');
        Route::put('/{block}', [BlockController::class, 'update'])->name('update');
        Route::delete('/{block}', [BlockController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [BlockController::class, 'reorder'])->name('reorder');
        Route::get('/media-picker', [BlockController::class, 'mediaPicker'])->name('media-picker');
        Route::get('/block-types', [BlockController::class, 'blockTypes'])->name('block-types');
    });

    // Categories management
    Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Tags management
    Route::prefix('admin/tags')->name('admin.tags.')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::get('/create', [TagController::class, 'create'])->name('create');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('/{tag}/edit', [TagController::class, 'edit'])->name('edit');
        Route::put('/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
    });

    // Comments management
    Route::prefix('admin/comments')->name('admin.comments.')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('index');
        Route::get('/pending', [CommentController::class, 'pending'])->name('pending');
        Route::get('/{comment}/edit', [CommentController::class, 'edit'])->name('edit');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
        
        // Action routes
        Route::post('/{comment}/approve', [CommentController::class, 'approve'])->name('approve');
        Route::post('/{comment}/reject', [CommentController::class, 'reject'])->name('reject');
        Route::post('/{comment}/spam', [CommentController::class, 'spam'])->name('spam');
        Route::post('/{comment}/reply', [CommentController::class, 'reply'])->name('reply');
        
        // Bulk actions
        Route::post('/bulk/approve', [CommentController::class, 'bulkApprove'])->name('bulk.approve');
        Route::post('/bulk/reject', [CommentController::class, 'bulkReject'])->name('bulk.reject');
        Route::post('/bulk/destroy', [CommentController::class, 'bulkDestroy'])->name('bulk.destroy');
    });

    // Backups management - Admin only
    Route::prefix('admin/backups')->middleware('role:admin')->name('admin.backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'store'])->name('store');
        Route::get('/export/{format}', [BackupController::class, 'export'])->name('export');
        Route::post('/import', [BackupController::class, 'import'])->name('import');
        Route::post('/import-backup', [BackupController::class, 'importBackup'])->name('import-backup');
        Route::get('/{backup}/download', [BackupController::class, 'download'])->whereNumber('backup')->name('download');
        Route::post('/{backup}/restore', [BackupController::class, 'restore'])->whereNumber('backup')->name('restore');
        Route::delete('/{backup}', [BackupController::class, 'destroy'])->whereNumber('backup')->name('destroy');
    });

    // Webhooks management - Admin only
    Route::prefix('admin/webhooks')->middleware('role:admin')->name('admin.webhooks.')->group(function () {
        Route::get('/', [WebhookController::class, 'index'])->name('index');
        Route::get('/create', [WebhookController::class, 'create'])->name('create');
        Route::post('/', [WebhookController::class, 'store'])->name('store');
        Route::post('/{webhook}/test', [WebhookController::class, 'test'])->whereNumber('webhook')->name('test');
        Route::delete('/{webhook}', [WebhookController::class, 'destroy'])->whereNumber('webhook')->name('destroy');
    });

    Route::prefix('admin/products')->middleware('role:admin')->name('admin.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/coupons')->middleware('role:admin')->name('admin.coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::get('/create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
        Route::put('/{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/orders')->middleware('role:admin')->name('admin.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('show');
        Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('status');
    });

    // Users management - Admin only
    Route::prefix('admin/users')->middleware('role:admin')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');

        // Static routes must come before /{user}.
        Route::get('/pending', [UserController::class, 'pending'])->name('pending');
        Route::post('/bulk', [UserController::class, 'bulk'])->name('bulk');

        Route::get('/{user}', [UserController::class, 'show'])->whereNumber('user')->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->whereNumber('user')->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->whereNumber('user')->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->whereNumber('user')->name('destroy');

        // Additional actions
        Route::post('/{user}/verify', [UserController::class, 'verify'])->whereNumber('user')->name('verify');
        Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->whereNumber('user')->name('deactivate');
    });

    // Reusable blocks - Admin only
    Route::prefix('admin/reusable-blocks')->middleware('role:admin')->name('admin.reusable-blocks.')->group(function () {
        Route::get('/', [ReusableBlockController::class, 'index'])->name('index');
        Route::post('/', [ReusableBlockController::class, 'store'])->name('store');
        Route::delete('/{reusableBlock}', [ReusableBlockController::class, 'destroy'])->name('destroy');
    });

    // Settings - Admin only
    Route::prefix('admin/settings')->middleware('role:admin')->name('admin.settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
    });
    
    // Security & trust - Admin only
    Route::prefix('admin/activity')->middleware('role:admin')->name('admin.activity.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    });

    Route::prefix('admin/health')->middleware('role:admin')->name('admin.health.')->group(function () {
        Route::get('/', [HealthController::class, 'index'])->name('index');
    });

    Route::prefix('admin/ip-rules')->middleware('role:admin')->name('admin.ip-rules.')->group(function () {
        Route::get('/', [IpRulesController::class, 'index'])->name('index');
        Route::post('/', [IpRulesController::class, 'store'])->name('store');
        Route::delete('/{rule}', [IpRulesController::class, 'destroy'])->name('destroy');
    });

    // Languages - Admin only
    Route::prefix('admin/languages')->middleware('role:admin')->name('admin.languages.')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('/create', [LanguageController::class, 'create'])->name('create');
        Route::post('/', [LanguageController::class, 'store'])->name('store');
        Route::get('/{language}/edit', [LanguageController::class, 'edit'])->name('edit');
        Route::put('/{language}', [LanguageController::class, 'update'])->name('update');
        Route::post('/{language}/toggle', [LanguageController::class, 'toggle'])->name('toggle');
        Route::delete('/{language}', [LanguageController::class, 'destroy'])->name('destroy');
    });

    // Translation memory - Admin only
    Route::prefix('admin/translations')->middleware('role:admin')->name('admin.translations.')->group(function () {
        Route::get('/', [TranslationStringController::class, 'index'])->name('index');
        Route::post('/', [TranslationStringController::class, 'store'])->name('store');
        Route::put('/{string}', [TranslationStringController::class, 'update'])->name('update');
        Route::delete('/{string}', [TranslationStringController::class, 'destroy'])->name('destroy');
    });

    // Theme management - Admin only
    Route::prefix('admin/themes')->middleware('role:admin')->name('admin.themes.')->group(function () {
        Route::get('/', [ThemeController::class, 'index'])->name('index');
        Route::get('/{slug}', [ThemeController::class, 'edit'])->name('edit');
        Route::post('/{slug}', [ThemeController::class, 'update'])->name('update');
        Route::delete('/{slug}', [ThemeController::class, 'destroy'])->name('destroy');
    });

    // Plugin management
    Route::prefix('admin/plugins')->middleware('role:admin')->name('admin.plugins.')->group(function () {
        Route::get('/', [PluginController::class, 'index'])->name('index');
        Route::post('/{id}/activate', [PluginController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [PluginController::class, 'deactivate'])->name('deactivate');
    });

    // Menu management
    Route::prefix('admin/menus')->name('admin.menus.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');

        Route::post('/{menu}/items', [MenuController::class, 'storeItem'])->name('items.store');
        Route::put('/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('items.update');
        Route::delete('/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('items.destroy');
        Route::post('/{menu}/reorder', [MenuController::class, 'reorder'])->name('reorder');
    });

    // Custom widgets management
    Route::prefix('admin/widgets')->name('admin.widgets.')->group(function () {
        Route::get('/', [WidgetController::class, 'index'])->name('index');
        Route::get('/create', [WidgetController::class, 'create'])->name('create');
        Route::post('/', [WidgetController::class, 'store'])->name('store');
        Route::get('/{widget}/edit', [WidgetController::class, 'edit'])->name('edit');
        Route::put('/{widget}', [WidgetController::class, 'update'])->name('update');
        Route::delete('/{widget}', [WidgetController::class, 'destroy'])->name('destroy');
        Route::post('/toggle', [WidgetController::class, 'toggle'])->name('toggle');
    });

    // Post revisions management
    Route::prefix('admin/posts/{post}/revisions')->name('admin.posts.revisions.')->group(function () {
        Route::get('/', [RevisionController::class, 'index'])->name('index');
        Route::get('/{revision}', [RevisionController::class, 'show'])->name('show');
        Route::get('/diff', [RevisionController::class, 'diff'])->name('diff');
        Route::post('/restore/{revision}', [RevisionController::class, 'restore'])->name('restore');
    });

    // WordPress compatibility routes
    Route::prefix('admin/wordpress')->name('admin.wordpress.')->middleware('role:admin')->group(function () {
        Route::get('/', [WordPressController::class, 'index'])->name('index');
        Route::get('/export', [WordPressController::class, 'export'])->name('export');
        Route::get('/export-all', [WordPressController::class, 'exportAll'])->name('export-all');
        Route::delete('/export', [WordPressController::class, 'deleteExport'])->name('export.delete');
        Route::post('/import', [WordPressController::class, 'uploadImport'])->name('import');
        Route::post('/migrate', [WordPressController::class, 'runMigration'])->name('migrate.run');
        Route::get('/check-connection', [WordPressController::class, 'checkConnection'])->name('check-connection');
    });
});

// View composers for theme partials
View::composer('storefront.*', function ($view) {
    $site = \App\Models\Site::first();
    $cart = app(\App\Services\CartService::class);

    $view->with([
        'site' => $site,
        'cartCount' => $cart->count(),
    ]);
});

View::composer('themes.*.partials.sidebar', function ($view) {
    $categories = Category::withCount('posts')->get();
    $recentPosts = App\Models\Post::where('post_type', 'post')
        ->where('status', 'published')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    $tags = Tag::take(10)->get();
    
    $view->with(compact('categories', 'recentPosts', 'tags'));
});
