<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 *
 */

use Illuminate\Support\Facades\Route;
use Equidna\Caronte\Http\Controllers\CaronteController;
use Equidna\Toolkit\Http\Middleware\ExcludeFromHistory;
use Equidna\Toolkit\Http\Middleware\DisableDebugbar;

//* Caronte
Route::middleware([DisableDebugbar::class])->group(function () {
    Route::get('/login', [CaronteController::class, 'loginForm'])->name('caronte.login.form');
    Route::post('/login', [CaronteController::class, 'login'])->name('caronte.login');
    Route::get('/logout', [CaronteController::class, 'logout'])->name('caronte.logout');

    Route::post('/2fa', [CaronteController::class, 'twoFactorTokenRequest'])->name('caronte.2fa.request');
    Route::get('/2fa/{token}', [CaronteController::class, 'twoFactorTokenLogin'])->name('caronte.2fa.login');

    // Ensure a single blank line after use statements
    Route::prefix('password/recover')->group(
        function () {
            Route::get('', [CaronteController::class, 'passwordRecoverRequestForm'])->name('caronte.password.recover.form');
            Route::post('', [CaronteController::class, 'passwordRecoverRequest'])->name('caronte.password.recover.request');
            Route::get('{token}', [CaronteController::class, 'passwordRecoverTokenValidation'])->name('caronte.password.recover.validate-token');
            Route::post('{token}', [CaronteController::class, 'passwordRecover'])->name('caronte.password.recover');
        }
    );

    Route::get('get-token', [CaronteController::class, 'getToken'])->name('caronte.token')
        ->middleware([ExcludeFromHistory::class,]);
});
