<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Auth routes
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Root → dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Customer routes (manager: all, contractor: own) ---
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers/{customer}/call', [CustomerController::class, 'recordCall'])->name('customers.call');

    // Manager-only customer actions
    Route::middleware('manager')->group(function () {
        Route::post('/customers/{customer}/verify', [CustomerController::class, 'verify'])->name('customers.verify');
        Route::post('/customers/{customer}/reassign', [CustomerController::class, 'reassign'])->name('customers.reassign');

        // Import
        Route::get('/import', [ImportController::class, 'show'])->name('import.show');
        Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
        Route::post('/import', [ImportController::class, 'import'])->name('import.store');

        // Settings / user management
        Route::get('/settings', [UserController::class, 'index'])->name('settings.index');
        Route::post('/settings/users', [UserController::class, 'store'])->name('settings.users.store');
        Route::put('/settings/users/{user}', [UserController::class, 'update'])->name('settings.users.update');
        Route::delete('/settings/users/{user}', [UserController::class, 'destroy'])->name('settings.users.destroy');
    });

    // Contractor performance
    Route::get('/my-performance', [PerformanceController::class, 'index'])->name('contractor.performance');

    // Contractor = same customers route with tab filter
    Route::get('/my-customers', function () {
        return redirect()->route('customers.index');
    })->name('contractor.customers');
});
