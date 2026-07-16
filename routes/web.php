<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

// Auth routes (login, logout, reset password — register dinonaktifkan)
require __DIR__.'/auth.php';

Route::get('/', fn () => redirect()->route('assets.index'));

// ╔══════════════════════════════════════════════════════════════╗
// ║  AUTHENTICATED ROUTES                                        ║
// ╚══════════════════════════════════════════════════════════════╝
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ── Aset (akses dikontrol per-permission di controller) ──────
    Route::resource('assets', AssetController::class)->middleware('throttle:60,1');
    Route::get('assets/export/csv', [AssetController::class, 'exportCsv'])->name('assets.export.csv');
    Route::post('assets/import/csv', [AssetController::class, 'importCsv'])->name('assets.import.csv')->middleware('throttle:10,1');
    Route::get('assets/{asset}/qr-code', [AssetController::class, 'qrCode'])->name('assets.qr-code');
    Route::get('assets/{asset}/barcode', [AssetController::class, 'barcode'])->name('assets.barcode');
    Route::get('assets/{asset}/print-code', [AssetController::class, 'printCode'])->name('assets.print-code');

    // ── Kategori, Merek, Vendor & Lokasi (akses dikontrol per-permission di controller) ────
    Route::prefix('admin')->name('admin.')->middleware('throttle:60,1')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('vendors', VendorController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('employees', EmployeeController::class);
    });

    // ── User Management (Super Admin only) ──────────────────────
    Route::middleware(['admin', 'throttle:30,1'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', UserController::class);
        });

    // ── Check-Out / Check-In Aset (Peminjaman) ────────────────
    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show', 'destroy'])->middleware('throttle:60,1');
    Route::patch('loans/{loan}/checkin', [LoanController::class, 'checkin'])->name('loans.checkin')->middleware('throttle:60,1');

    // ── Laporan PDF ───────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('assets-pdf', [ReportController::class, 'assetsPdf'])->name('assets-pdf');
        Route::get('categories-pdf', [ReportController::class, 'categories'])->name('categories-pdf');
    });

    // ── Log Aktivitas & Mutasi Aset ──────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('logs/asset', [LogController::class, 'assetLog'])->name('logs.asset');
        Route::get('logs/mutation', [LogController::class, 'mutationLog'])->name('logs.mutation');
    });
});
