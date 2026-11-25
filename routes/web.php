<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegionDashboardController;
use App\Http\Controllers\BranchDashboardController;
use App\Http\Controllers\AgencyDashboardController;
use App\Http\Controllers\BranchCustomerController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegionAgencyController;
use App\Http\Controllers\RegionReportController;
use Illuminate\Http\Request;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn() => redirect()->route(auth()->user()->homeRoute()))->name('dashboard');

    Route::middleware('role:region')->prefix('region')->name('region.')->group(function () {
        Route::get('/dashboard', [RegionDashboardController::class, 'index'])->name('dashboard');
        Route::get('/agencies', [RegionAgencyController::class, 'index'])->name('agencies.index');
        Route::get('/reports', [RegionReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [RegionReportController::class, 'exportCsv'])->name('reports.export');
    });
    Route::middleware(['auth', 'active', 'role:admin_branch'])
        ->prefix('branch')
        ->name('branch.')
        ->group(function () {
            Route::get('/dashboard', [BranchDashboardController::class, 'index'])
                ->name('dashboard');

            /*
         * Route pelanggan, import, dan export ditambahkan di sini
         * setelah controller fiturnya dibuat.
         */
        });
    Route::middleware('role:agency')->prefix('agency')->name('agency.')->group(function () {
        Route::get('/dashboard', [AgencyDashboardController::class, 'index'])->name('dashboard');
        
    });
    Route::middleware(['auth'])->prefix('branch')->name('branch.')->group(function () {
        Route::get('/dashboard', [BranchDashboardController::class, 'index'])->name('dashboard');
        Route::post('/customers', [BranchCustomerController::class, 'store'])->name('customers.store');
    });
});
