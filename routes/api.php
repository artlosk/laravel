<?php

use App\Http\Controllers\api\PostController;
use Illuminate\Support\Facades\Route;

// Применяем к группе маршрутов
Route::middleware(['auth.credentials', 'throttle:posts'])->group(function() {

    // Создание поста (POST /api/posts)
    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('check.permission:create-posts');

    // Просмотр всех постов (GET /api/posts)
    Route::get('/posts', [PostController::class, 'index'])
        ->middleware('check.permission:read-posts');

    // Просмотр конкретного поста (GET /api/posts/{id})
    Route::get('/posts/{id}', [PostController::class, 'show'])
        ->middleware('check.permission:read-posts');

    // Обновление поста (PUT /api/posts/{id})
    Route::put('/posts/{id}', [PostController::class, 'update'])
        ->middleware('check.permission:edit-posts');

    // Удаление поста (DELETE /api/posts/{id})
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])
        ->middleware('check.permission:delete-posts');
});
