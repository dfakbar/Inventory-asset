<?php

use App\Http\Controllers\Api\AssetController;
use Illuminate\Support\Facades\Route;

Route::get('/assets', [AssetController::class, 'index'])->name('api.assets.index');
Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('api.assets.show');
