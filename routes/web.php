<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProfileController;
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
use App\Http\Controllers\Api\ContentController;
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
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth', 'role:admin|editor'])->group(function () {
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

    // Settings - Admin only
    Route::prefix('admin/settings')->middleware('role:admin')->name('admin.settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
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
    });
});

// View composers for theme partials
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
