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
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
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

        // Attendance Management (Permission handled in IsSupervisor middleware via can())
        Route::post('absensi/record/bulk', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'bulkStoreRecord'])->name('absensi.record.bulk');
        Route::resource('absensi', \App\Http\Controllers\Manager\SesiAbsensiController::class);
        Route::post('absensi/approve/{id}', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'approve'])->name('absensi.approve');
        Route::post('absensi/reject/{id}', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'reject'])->name('absensi.reject');
        Route::put('absensi/record/{id}', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'updateRecord'])->name('absensi.record.update');
        Route::post('absensi/{session}/approve-all', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'approveAll'])->name('absensi.approve-all');
        Route::post('absensi/session/{sessionId}/record', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'storeRecord'])->name('absensi.record.store');
        Route::delete('absensi/record/{id}', [\App\Http\Controllers\Manager\SesiAbsensiController::class, 'deleteRecord'])->name('absensi.record.destroy');

        // Task Management (Permission checked in IsSupervisor or via policy/controller)
        Route::resource('tugas', \App\Http\Controllers\Manager\TugasController::class);
        Route::post('tugas/session/{sessionId}/record', [\App\Http\Controllers\Manager\TugasController::class, 'storeTask'])->name('tugas.record.store');
        Route::put('tugas/record/{id}', [\App\Http\Controllers\Manager\TugasController::class, 'updateTask'])->name('tugas.record.update');
        Route::delete('tugas/record/{id}', [\App\Http\Controllers\Manager\TugasController::class, 'deleteTask'])->name('tugas.record.destroy');
        Route::post('tugas/approve/{id}', [\App\Http\Controllers\Manager\TugasController::class, 'approveTask'])->name('tugas.approve');
        Route::post('tugas/reject/{id}', [\App\Http\Controllers\Manager\TugasController::class, 'rejectTask'])->name('tugas.reject');
        Route::get('tugas/item/{id}', [\App\Http\Controllers\Manager\TugasController::class, 'taskDetail'])->name('tugas.detail');
        Route::post('tugas/item/{id}/propose-sub', [\App\Http\Controllers\Manager\TugasController::class, 'proposeSubTugas'])->name('tugas.propose-sub');
        Route::post('tugas/item/{id}/approve-plan', [\App\Http\Controllers\Manager\TugasController::class, 'approveSubTugasPlan'])->name('tugas.approve-plan');
        Route::post('tugas/sub/{id}/complete', [\App\Http\Controllers\Manager\TugasController::class, 'completeSubTugas'])->name('tugas.sub.complete');

        // Report & Export
        Route::get('reports', [\App\Http\Controllers\Manager\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [\App\Http\Controllers\Manager\ReportController::class, 'export'])->name('reports.export');
    });

    // Employee / Regular User Workspace
    Route::middleware('role:employee|supervisor|manager|superadmin')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/absensi', [\App\Http\Controllers\Employee\AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi/submit', [\App\Http\Controllers\Employee\AbsensiController::class, 'submit'])->name('absensi.submit');
        Route::post('/absensi/submit-request', [\App\Http\Controllers\Employee\AbsensiController::class, 'submitRequest'])->name('absensi.submit-request');

        // Task routes for employees
        Route::get('/tugas', [\App\Http\Controllers\Employee\TugasController::class, 'index'])->name('tugas.index');
        Route::post('/tugas/submit-progress/{id}', [\App\Http\Controllers\Employee\TugasController::class, 'submitProgress'])->name('tugas.submit-progress');

        // Report routes for employees
        Route::get('/reports', [\App\Http\Controllers\Employee\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Employee\ReportController::class, 'export'])->name('reports.export');
    });

    // Leader Workspace (Limited to coordination and breakdown)
    Route::middleware('role:leader')->prefix('leader')->name('leader.')->group(function () {
        Route::get('/tugas', [\App\Http\Controllers\Leader\TugasController::class, 'index'])->name('tugas.index');
    });
});
