<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NavigationApiController;

// API маршруты с защитой Sanctum
Route::middleware(['auth:sanctum'])->prefix('api')->name('api.')->group(function () {
    
    // Проверка авторизации
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // API для навигации
    Route::prefix('checkpoints')->name('checkpoints.')->group(function () {
        Route::post('/{checkpoint}/arrive', [NavigationApiController::class, 'arrive'])->name('arrive');
        Route::post('/{checkpoint}/skip', [NavigationApiController::class, 'skip'])->name('skip');
        Route::get('/{checkpoint}/info', [NavigationApiController::class, 'info'])->name('info');
    });
    
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::post('/{task}/complete', [NavigationApiController::class, 'completeTask'])->name('complete');
        Route::get('/{task}/info', [NavigationApiController::class, 'taskInfo'])->name('info');
    });
    
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/{session}/stats', [NavigationApiController::class, 'stats'])->name('stats');
        Route::post('/{session}/update-position', [NavigationApiController::class, 'updatePosition'])->name('update-position');
        Route::post('/{session}/take-photo', [NavigationApiController::class, 'takePhoto'])->name('take-photo');
    });
    
    Route::prefix('quests')->name('quests.')->group(function () {
        Route::get('/{quest}/progress', [NavigationApiController::class, 'questProgress'])->name('progress');
        Route::post('/{quest}/start', [NavigationApiController::class, 'startQuest'])->name('start');
    });
});