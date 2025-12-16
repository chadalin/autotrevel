<?php
// Маршруты для чатов
Route::middleware(['auth'])->prefix('chats')->name('chats.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/create', [ChatController::class, 'create'])->name('create');
    Route::post('/', [ChatController::class, 'store'])->name('store');
    Route::get('/{chat}', [ChatController::class, 'show'])->name('show');
    Route::post('/{chat}/add-users', [ChatController::class, 'addUsers'])->name('add-users');
    Route::delete('/{chat}/leave', [ChatController::class, 'leave'])->name('leave');
});