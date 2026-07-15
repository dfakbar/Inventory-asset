<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════
//  REGISTRASI DINONAKTIFKAN
//  Akun baru hanya dapat dibuat oleh Super Admin
//  melalui panel admin (/admin/users/create).
// ═══════════════════════════════════════════════════════════════
Route::get('register', fn () =>
    redirect()->route('login')
        ->with('info', 'Pendaftaran mandiri dinonaktifkan. Hubungi Administrator untuk mendapatkan akun.')
)->name('register');

Route::post('register', fn () => redirect()->route('login'));

// ═══════════════════════════════════════════════════════════════
//  GUEST ROUTES (hanya untuk tamu / belum login)
// ═══════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// ═══════════════════════════════════════════════════════════════
//  AUTH ROUTES (hanya untuk user yang sudah login)
// ═══════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
