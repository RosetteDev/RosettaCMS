<?php

use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/', [ContentController::class, 'index'])->name('api.index');

    Route::get('/posts', [ContentController::class, 'posts'])->name('api.posts.index');
    Route::get('/posts/{slug}', [ContentController::class, 'post'])->name('api.posts.show');
    Route::get('/pages', [ContentController::class, 'pages'])->name('api.pages.index');
    Route::get('/pages/{slug}', [ContentController::class, 'page'])->name('api.pages.show');

    Route::get('/categories', [ContentController::class, 'categories'])->name('api.categories.index');
    Route::get('/tags', [ContentController::class, 'tags'])->name('api.tags.index');
    Route::get('/search', [ContentController::class, 'search'])->name('api.search');
});
