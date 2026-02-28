<?php

use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');


    Route::middleware('supervisor')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
    });

    // SuperAdmin / RBAC Management
    Route::middleware('super-admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::resource('roles', \App\Http\Controllers\SuperAdmin\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\SuperAdmin\PermissionController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('users', \App\Http\Controllers\SuperAdmin\UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('give-access', \App\Http\Controllers\Superadmin\GiveAccesController::class)->only(['index', 'update']);
    });
});
