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

    // SuperAdmin / RBAC Management
    Route::middleware('super-admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::resource('roles', \App\Http\Controllers\SuperAdmin\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\SuperAdmin\PermissionController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('users', \App\Http\Controllers\SuperAdmin\UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('give-access', \App\Http\Controllers\SuperAdmin\GiveAccesController::class)->only(['index', 'update']);
        Route::resource('divisi', \App\Http\Controllers\SuperAdmin\DivisiController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('jabatan', \App\Http\Controllers\SuperAdmin\JabatanController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Manager / Supervisor Workspace
    Route::middleware('supervisor')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');
    });

    // Employee / Regular User Workspace
    Route::middleware('role:employee|supervisor|manager|superadmin')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
    });
});
