<?php

use App\Http\Controllers\Api\Mentor\ProfileSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {

    Route::prefix('profile')->group(function () {
        Route::post('/about-me', [ProfileSettingController::class,  'updateAboutMe']);
        Route::post('/experience', [ProfileSettingController::class,  'addExperience']);
        Route::put('/experience/update/{index}', [ProfileSettingController::class,  'updateExperience']);
        Route::delete('/experience/{index}', [ProfileSettingController::class,  'removeExperience']);
        Route::post('/education', [ProfileSettingController::class,  'addEducation']);
        Route::put('/education/update/{index}', [ProfileSettingController::class,  'updateEducation']);
        Route::delete('/education/{index}', [ProfileSettingController::class,  'removeEducation']);
    });
  


});


require __DIR__.'/auth.php';
require __DIR__.'/wallet.php';
