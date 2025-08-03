<?php

use App\Http\Controllers\backend\AdminController;
use App\Http\Controllers\backend\PostController as BackendPostController;
use App\Http\Controllers\backend\RolePermissionController;
use App\Http\Controllers\backend\UserController;
use App\Http\Controllers\frontend\DashboardController;
use App\Http\Controllers\frontend\PostController as FrontendPostController;
use App\Http\Controllers\frontend\ProfileController as FrontendProfileController;
use App\Http\Controllers\backend\ProfileController as BackendProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('frontend.welcome');
});

Auth::routes();

Route::get('/posts', [FrontendPostController::class, 'index'])->name('frontend.posts.index')->middleware('permission:read-posts');
Route::get('/posts/{post}', [FrontendPostController::class, 'show'])->name('frontend.posts.show')->middleware('permission:read-posts');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.dashboard');
    Route::match(['get', 'post'], '/profile', [FrontendProfileController::class, 'update'])->name('frontend.profile');
    Route::match(['get', 'post'], '/password', [FrontendProfileController::class, 'changePassword'])->name('frontend.password');
});

Route::middleware(['auth', 'permission:access-admin-panel'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('backend.dashboard');
    Route::match(['get', 'post'], '/profile', [BackendProfileController::class, 'update'])->name('backend.profile');
    Route::match(['get', 'post'], '/password', [BackendProfileController::class, 'changePassword'])->name('backend.password');

    Route::middleware(['auth', 'permission:read-posts'])->group(function () {
        Route::get('/posts', [BackendPostController::class, 'index'])->name('backend.posts.index');
        Route::get('/posts/create', [BackendPostController::class, 'create'])->name('backend.posts.create');
        Route::post('/posts', [BackendPostController::class, 'store'])->name('backend.posts.store');
        Route::get('/posts/{post}/edit', [BackendPostController::class, 'edit'])->name('backend.posts.edit');
        Route::put('/posts/{post}', [BackendPostController::class, 'update'])->name('backend.posts.update');
        Route::delete('/posts/{post}', [BackendPostController::class, 'delete'])->name('backend.posts.delete');
        Route::get('/posts/{post}', [BackendPostController::class, 'show'])->name('backend.posts.show');
    });
    Route::middleware(['auth', 'permission:manage-roles|manage-permissions'])->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'indexRoles'])->name('backend.roles.index');
        Route::get('/roles/create', [RolePermissionController::class, 'createRole'])->name('backend.roles.create');
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('backend.roles.store');
        Route::get('/roles/{role}/edit', [RolePermissionController::class, 'editRole'])->name('backend.roles.edit');
        Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole'])->name('backend.roles.update');
        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroyRole'])->name('backend.roles.destroy');

        Route::get('/permissions', [RolePermissionController::class, 'indexPermissions'])->name('backend.permissions.index');
        Route::get('/permissions/create', [RolePermissionController::class, 'createPermission'])->name('backend.permissions.create');
        Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('backend.permissions.store');
        Route::get('/permissions/{permission}/edit', [RolePermissionController::class, 'editPermission'])->name('backend.permissions.edit');
        Route::put('/permissions/{permission}', [RolePermissionController::class, 'updatePermission'])->name('backend.permissions.update');
        Route::delete('/permissions/{permission}', [RolePermissionController::class, 'destroyPermission'])->name('backend.permissions.destroy');

        Route::get('/users/roles-permissions', [RolePermissionController::class, 'manageUserRolesPermissions'])->name('backend.roles.manage');
        Route::post('/users/roles-permissions', [RolePermissionController::class, 'updateUserRolesPermissions'])->name('backend.roles.update-user');
        Route::get('/users/{user}/roles-permissions', [RolePermissionController::class, 'getUserRolesPermissions'])->name('backend.roles.get-user');
    });

    Route::middleware(['auth', 'permission:manage-users'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('backend.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('backend.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('backend.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('backend.users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('backend.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('backend.users.destroy');
    });

    Route::view('/upload', 'backend.upload');

    Route::post('/upload-media', [BackendPostController::class, 'uploadMedia'])->name('admin.upload-media');
    Route::delete('/posts/{post}/remove-media/{media}', [BackendPostController::class, 'removeMedia'])->name('backend.posts.removeMedia');

    Route::post('/upload-image', [BackendPostController::class, 'uploadImage'])->name('upload.image');
    Route::get('/get-gallery-images', [BackendPostController::class, 'getGalleryImages'])->name('gallery.images');
    Route::post('/posts/{post}/attach-media/{mediaId}', [BackendPostController::class, 'attachMediaToPost'])->name('post.attach.media');

    Route::get('media', [App\Http\Controllers\backend\MediaController::class, 'index'])->name('backend.media.index');
    Route::get('media/get-by-ids', [App\Http\Controllers\backend\MediaController::class, 'getByIds'])->name('backend.media.getByIds');
    Route::delete('media/{media}', [App\Http\Controllers\backend\MediaController::class, 'deleteMedia'])->name('backend.media.delete');

    Route::post('filepond/upload', [App\Http\Controllers\backend\MediaController::class, 'uploadFilepond'])->name('backend.filepond.upload');
    Route::delete('filepond/delete', [App\Http\Controllers\backend\MediaController::class, 'deleteFilepond'])->name('backend.filepond.delete');
});
