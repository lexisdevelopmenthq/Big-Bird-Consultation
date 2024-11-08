<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class,  'register']);
    Route::post('/login', [AuthController::class,  'login']);
    Route::post('/verify-otp', [AuthController::class,  'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class,  'resendOtp']);
    Route::post('/forget-password', [AuthController::class,  'forgotPassword']);
    Route::post('/reset-password', [AuthController::class,  'resetPassword']);
});

Route::middleware('auth:sanctum')->group( function () {
    // Customer
Route::post('logout', [AuthController::class,  'logout']);
Route::post('/change-password', [AuthController::class,  'changePassword']);
// Route::prefix('profile')->group(function () {
//     Route::post('/update', [UpdateController::class,  'updateProfile']);
//     Route::post('/picture', [UpdateController::class,  'updateProfilePicture']);
// });

});
