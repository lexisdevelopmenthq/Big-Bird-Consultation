<?php

use App\Http\Controllers\Api\Wallet\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {
    Route::prefix('wallet')->group(function () {
        Route::get('/balance', [WalletController::class, 'balance']);
         Route::post('/deposit', [WalletController::class, 'deposit']);
         Route::post('/withdraw', [WalletController::class, 'withdraw']);
    });
});

