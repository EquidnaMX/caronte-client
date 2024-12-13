<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.5
 *
 */

use Illuminate\Support\Facades\Route;
use Equidna\Caronte\Http\Controllers\CaronteController;

//* Caronte
Route::get('/login', [CaronteController::class, 'loginForm'])->name('caronte.login');
Route::post('/login', [CaronteController::class, 'login']);

Route::post('/2fa', [CaronteController::class, 'twoFactorTokenRequest']);
Route::get('/2fa/{token}', [CaronteController::class, 'twoFactorTokenLogin']);

Route::match(['get', 'post'], 'logout', [CaronteController::class, 'logout'])->name('caronte.logout');

Route::prefix('password/recover')->group(
    function () {
        Route::get('', [CaronteController::class, 'passwordRecoverRequestForm'])->name('caronte.password.recover');
        Route::post('', [CaronteController::class, 'passwordRecoverRequest']);
        Route::get('{token}', [CaronteController::class, 'passwordRecoverTokenValidation']);
        Route::post('{token}', [CaronteController::class, 'passwordRecover']);
    }
);

Route::get('get-token', [CaronteController::class, 'getToken'])->name('caronte.token.get')
    ->middleware(
        [
            \Equidna\Toolkit\Http\Middleware\ExcludeFromHistory::class,
        ]
    );
