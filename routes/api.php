<?php

use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DocsController;
use App\Http\Controllers\Api\WriteController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function () {
    // Versioned API — the canonical public namespace.
    Route::prefix('v1')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('api.index');
        Route::get('/openapi.json', [DocsController::class, 'spec'])->name('api.openapi');
        Route::get('/docs', [DocsController::class, 'docs'])->name('api.docs');
        Route::get('/swagger', [DocsController::class, 'swagger'])->name('api.swagger');

        Route::get('/posts', [ContentController::class, 'posts'])->name('api.posts.index');
        Route::get('/posts/{slug}', [ContentController::class, 'post'])->name('api.posts.show');
        Route::get('/pages', [ContentController::class, 'pages'])->name('api.pages.index');
        Route::get('/pages/{slug}', [ContentController::class, 'page'])->name('api.pages.show');

        Route::get('/categories', [ContentController::class, 'categories'])->name('api.categories.index');
        Route::get('/tags', [ContentController::class, 'tags'])->name('api.tags.index');
        Route::get('/search', [ContentController::class, 'search'])->name('api.search');

        Route::get('/media', [ContentController::class, 'media'])->name('api.media.index');
        Route::get('/media/{id}', [ContentController::class, 'mediaShow'])->whereNumber('id')->name('api.media.show');

        Route::get('/menus', [ContentController::class, 'menus'])->name('api.menus.index');
        Route::get('/menus/{slug}', [ContentController::class, 'menu'])->name('api.menus.show');

        // Authenticated write endpoints.
        Route::post('/posts', [WriteController::class, 'storePost'])
            ->middleware('api.key:posts:write')->name('api.posts.store');
        Route::put('/posts/{slug}', [WriteController::class, 'updatePost'])
            ->middleware('api.key:posts:write')->name('api.posts.update');
        Route::delete('/posts/{slug}', [WriteController::class, 'destroyPost'])
            ->middleware('api.key:posts:write')->name('api.posts.destroy');

        Route::post('/pages', [WriteController::class, 'storePage'])
            ->middleware('api.key:pages:write')->name('api.pages.store');
        Route::put('/pages/{slug}', [WriteController::class, 'updatePage'])
            ->middleware('api.key:pages:write')->name('api.pages.update');
        Route::delete('/pages/{slug}', [WriteController::class, 'destroyPage'])
            ->middleware('api.key:pages:write')->name('api.pages.destroy');
    });

    // Legacy unversioned namespace kept for backwards compatibility.
    Route::get('/', [ContentController::class, 'index'])->name('api.index.legacy');
    Route::get('/posts', [ContentController::class, 'posts'])->name('api.posts.index.legacy');
    Route::get('/posts/{slug}', [ContentController::class, 'post'])->name('api.posts.show.legacy');
    Route::get('/pages', [ContentController::class, 'pages'])->name('api.pages.index.legacy');
    Route::get('/pages/{slug}', [ContentController::class, 'page'])->name('api.pages.show.legacy');
    Route::get('/categories', [ContentController::class, 'categories'])->name('api.categories.index.legacy');
    Route::get('/tags', [ContentController::class, 'tags'])->name('api.tags.index.legacy');
    Route::get('/search', [ContentController::class, 'search'])->name('api.search.legacy');
    Route::get('/media', [ContentController::class, 'media'])->name('api.media.index.legacy');
    Route::get('/media/{id}', [ContentController::class, 'mediaShow'])->whereNumber('id')->name('api.media.show.legacy');
    Route::get('/menus', [ContentController::class, 'menus'])->name('api.menus.index.legacy');
    Route::get('/menus/{slug}', [ContentController::class, 'menu'])->name('api.menus.show.legacy');
});
