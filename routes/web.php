<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\QuestController;

use App\Http\Controllers\ReviewController;
// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/map-data', [HomeController::class, 'mapData'])->name('map.data');

// Авторизация по email
Route::get('/login', [EmailVerificationController::class, 'showLoginForm'])->name('login');
Route::post('/send-code', [EmailVerificationController::class, 'sendCode'])->name('send.code');
Route::post('/verify-code', [EmailVerificationController::class, 'verifyCode'])->name('verify.code');
Route::post('/logout', [EmailVerificationController::class, 'logout'])->name('logout');

// Маршруты
Route::resource('routes', RouteController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
Route::get('/routes/{route}/map-data', [RouteController::class, 'mapData'])->name('routes.map.data');
Route::get('/routes/{route}/export/gpx', [RouteController::class, 'exportGpx'])->name('routes.export.gpx');

// Отзывы
Route::post('/routes/{route}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

// Защищенные маршруты
Route::middleware(['auth'])->group(function () {
    // Создание и редактирование маршрутов
    Route::get('/routes/create', [RouteController::class, 'create'])->name('routes.create');
    Route::post('/routes', [RouteController::class, 'store'])->name('routes.store');
    Route::get('/routes/{route}/edit', [RouteController::class, 'edit'])->name('routes.edit');
    Route::put('/routes/{route}', [RouteController::class, 'update'])->name('routes.update');
    Route::delete('/routes/{route}', [RouteController::class, 'destroy'])->name('routes.destroy');
    
    // Сохранение маршрутов
    Route::post('/routes/{route}/save', [RouteController::class, 'save'])->name('routes.save');
    
    // Точки интереса
    Route::post('/routes/{route}/points', [RouteController::class, 'addPoint'])->name('routes.points.store');
    Route::delete('/points/{point}', [RouteController::class, 'deletePoint'])->name('routes.points.destroy');
});

// Система квестов
Route::prefix('quests')->name('quests.')->group(function () {
    // Публичные маршруты
    Route::get('/', [QuestController::class, 'index'])->name('index');
    Route::get('/map', [QuestController::class, 'mapQuests'])->name('map');
    Route::get('/leaderboard', [QuestController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/{slug}', [QuestController::class, 'show'])->name('show');
    
    // Требуют авторизации
    Route::middleware(['auth'])->group(function () {
        Route::get('/my/quests', [QuestController::class, 'myQuests'])->name('my');
        Route::get('/achievements', [QuestController::class, 'achievements'])->name('achievements');
        Route::get('/badges', [QuestController::class, 'badges'])->name('badges');
        
        Route::post('/{quest}/start', [QuestController::class, 'start'])->name('start');
        Route::post('/{quest}/progress', [QuestController::class, 'updateProgress'])->name('progress.update');
        Route::post('/{quest}/cancel', [QuestController::class, 'cancel'])->name('cancel');
    });
    
    // Админ маршруты
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin,App\Models\Quest'])->group(function () {
        Route::get('/', [QuestController::class, 'adminIndex'])->name('index');
        Route::get('/create', [QuestController::class, 'adminCreate'])->name('create');
        Route::post('/', [QuestController::class, 'adminStore'])->name('store');
        Route::get('/moderate', [QuestController::class, 'adminModerate'])->name('moderate');
        Route::post('/verify/{completion}', [QuestController::class, 'adminVerify'])->name('verify');
    });
});