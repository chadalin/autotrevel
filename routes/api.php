<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NavigationApiController;

Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->group(function () {
    // API для навигации
    Route::prefix('checkpoints')->group(function () {
        Route::post('/{checkpoint}/arrive', [NavigationApiController::class, 'arrive']);
        Route::post('/{checkpoint}/skip', [NavigationApiController::class, 'skip']);
        Route::get('/{checkpoint}/info', [NavigationApiController::class, 'info']);
    });
    
    Route::prefix('tasks')->group(function () {
        Route::post('/{task}/complete', [NavigationApiController::class, 'completeTask']);
        Route::get('/{task}/info', [NavigationApiController::class, 'taskInfo']);
    });
    
    Route::prefix('sessions')->group(function () {
        Route::get('/{session}/stats', [NavigationApiController::class, 'stats']);
        Route::post('/{session}/update-position', [NavigationApiController::class, 'updatePosition']);
        Route::post('/{session}/take-photo', [NavigationApiController::class, 'takePhoto']);
    });
    
    Route::prefix('quests')->group(function () {
        Route::get('/{quest}/progress', [NavigationApiController::class, 'questProgress']);
        Route::post('/{quest}/start', [NavigationApiController::class, 'startQuest']);
    });
});