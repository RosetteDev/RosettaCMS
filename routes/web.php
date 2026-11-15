<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\Site;

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
    return view('themes.rosetta.about', [
        'title' => 'About Us',
        'description' => 'Learn more about Rosetta CMS',
    ]);
});

// Contact page
Route::get('/contact', function () {
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
        Route::get('/{post}', [PostController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [PostController::class, 'update'])->name('update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
    });

    // Media management
    Route::prefix('admin/media')->name('admin.media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/create', [MediaController::class, 'create'])->name('create');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::get('/{media}', [MediaController::class, 'show'])->name('show');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
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
    $page = App\Models\Post::where('slug', $slug)->where('type', 'page')->firstOrFail();
    return view('themes.rosetta.page', compact('page'));
})->name('page.show');

// Category routes
Route::get('/categories/{slug}', function ($slug) {
    $category = App\Models\Term::where('slug', $slug)->where('taxonomy', 'category')->firstOrFail();
    $posts = $category->posts()->paginate(10);
    
    return view('themes.rosetta.category', compact('category', 'posts'));
})->name('category.show');

// Tag routes
Route::get('/tags/{slug}', function ($slug) {
    $tag = App\Models\Term::where('slug', $slug)->where('taxonomy', 'post_tag')->firstOrFail();
    $posts = $tag->posts()->paginate(10);
    
    return view('themes.rosetta.tag', compact('tag', 'posts'));
})->name('tag.show');
