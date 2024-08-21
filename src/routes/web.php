<?php

use Illuminate\Support\Facades\Route;
use Gruelas\Caronte\Http\Controllers\CaronteController;

//* Caronte
Route::get('/login', [CaronteController::class, 'loginForm'])->name('caronte.login');
Route::post('/login', [CaronteController::class, 'login']);
Route::post('/2fa', [CaronteController::class, 'twoFactorTokenRequest']);
Route::get('/2fa/{token}', [CaronteController::class, 'twoFactorTokenLogin']);
Route::match(['get', 'post'], 'logout', [CaronteController::class, 'logout'])->name('caronte.logout');
