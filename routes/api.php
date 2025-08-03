<?php

use App\Http\Controllers\api\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.credentials', 'throttle:posts'])->group(function() {

    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('check.permission:create-posts');

    Route::get('/posts', [PostController::class, 'index'])
        ->middleware('check.permission:read-posts');

    Route::get('/posts/{id}', [PostController::class, 'show'])
        ->middleware('check.permission:read-posts');

    Route::put('/posts/{id}', [PostController::class, 'update'])
        ->middleware('check.permission:edit-posts');

    Route::delete('/posts/{id}', [PostController::class, 'destroy'])
        ->middleware('check.permission:delete-posts');
});
