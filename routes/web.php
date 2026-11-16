<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\CommentController;
use App\Models\Site;
use App\Models\Category;
use App\Models\Tag;

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
Route::get('/', function () {
    // Try to get the default site
    $site = Site::first();
    
    if ($site) {
        // Check if the site has a homepage set
        if ($site->home_post_id) {
            $post = $site->posts()->find($site->home_post_id);
            if ($post) {
                return view('themes.rosetta.single', compact('post'));
            }
        }
        
        // If no homepage set, show a welcome page with site data
        return view('themes.rosetta.home', [
            'site' => $site,
            'title' => $site->name ?? 'Rosetta CMS',
            'description' => $site->description ?? 'A modern, flexible content management system',
        ]);
    }
    
    // Fallback to basic welcome page if no site exists
    return view('themes.rosetta.home');
});

// About page
Route::get('/about', function () {
    // Check if there's a CMS page for about (slug: about or about-our-cms)
    $page = App\Models\Post::where('post_type', 'page')
        ->where('status', 'published')
        ->where(function($query) {
            $query->where('slug', 'about')->orWhere('slug', 'about-our-cms');
        })
        ->first();
    
    if ($page) {
        return view('themes.rosetta.page', compact('page'));
    }
    
    return view('themes.rosetta.about', [
        'title' => 'About Us',
        'description' => 'Learn more about Rosetta CMS',
    ]);
});

// Contact page
Route::get('/contact', function () {
    // Check if there's a CMS page for contact
    $page = App\Models\Post::where('slug', 'contact')->where('post_type', 'page')->first();
    
    if ($page) {
        return view('themes.rosetta.page', compact('page'));
    }
    
    return view('themes.rosetta.contact', [
        'title' => 'Contact Us',
        'description' => 'Get in touch with Rosetta CMS',
    ]);
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth', 'verified'])->group(function () {
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
        Route::get('/{media}', [MediaController::class, 'show'])->name('show');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
        
        // Bulk operations
        Route::post('/bulk', [MediaController::class, 'bulk'])->name('bulk');
        
        // Folder management
        Route::get('/folders', [MediaController::class, 'folders'])->name('folders');
        Route::post('/folders', [MediaController::class, 'createFolder'])->name('createFolder');
        Route::delete('/folders', [MediaController::class, 'deleteFolder'])->name('deleteFolder');
        
        // Metadata update
        Route::put('/{media}/metadata', [MediaController::class, 'updateMetadata'])->name('updateMetadata');
    });

    // Sites management
    Route::prefix('admin/sites')->name('admin.sites.')->group(function () {
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

    // Settings
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
    });
});

// Post routes
Route::get('/posts/{slug}', function ($slug) {
    $post = App\Models\Post::where('slug', $slug)->firstOrFail();
    return view('themes.rosetta.single', compact('post'));
})->name('post.show');

// Page routes
Route::get('/pages/{slug}', function ($slug) {
    $page = App\Models\Post::where('slug', $slug)->where('post_type', 'page')->firstOrFail();
    return view('themes.rosetta.page', compact('page'));
})->name('page.show');

// Category routes
Route::get('/categories/{slug}', function ($slug) {
    $category = Category::where('slug', $slug)->firstOrFail();
    $posts = $category->posts()->where('post_type', 'post')->where('status', 'published')->paginate(10);
    
    return view('themes.rosetta.category', compact('category', 'posts'));
})->name('category.show');

// Tag routes
Route::get('/tags/{slug}', function ($slug) {
    $tag = Tag::where('slug', $slug)->firstOrFail();
    $posts = $tag->posts()->where('post_type', 'post')->where('status', 'published')->paginate(10);
    
    return view('themes.rosetta.tag', compact('tag', 'posts'));
})->name('tag.show');
