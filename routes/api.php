<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NavigationApiController;
use App\Http\Controllers\Api\TaskApiController;

Route::prefix('api')->name('api.')->group(function () {
    
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
    
    // API для заданий
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/{id}', [TaskApiController::class, 'show'])->name('show');
        Route::post('/{id}/complete', [TaskApiController::class, 'complete'])->name('complete');
        Route::post('/{id}/check-answer', [TaskApiController::class, 'checkAnswer'])->name('check-answer');
        Route::get('/{id}/hint', [TaskApiController::class, 'hint'])->name('hint');
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


// routes/api.php - добавьте перед основной группой
Route::middleware(['auth:sanctum'])->prefix('api')->name('api.')->group(function () {
    // Временно добавьте этот маршрут ПЕРВЫМ
    Route::get('/debug/tasks/{id}', function ($id) {
        $task = \App\Models\QuestTask::find($id);
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'QuestTask не найден в БД',
                'id' => $id,
                'time' => now()->toDateTimeString()
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $task,
            'content_decoded' => is_string($task->content) 
                ? json_decode($task->content, true) 
                : $task->content
        ]);
    });
    
    // ... остальные ваши маршруты
});


use App\Models\QuestTask;

Route::prefix('test-api')->group(function () {
    Route::get('/tasks/{id}', function ($id) {
        header('Content-Type: application/json');
        
        $task = QuestTask::find($id);
        
        if (!$task) {
            http_response_code(404);
            echo json_encode([
                'error' => 'Not Found',
                'message' => "QuestTask with ID {$id} not found",
                'available_ids' => QuestTask::pluck('id')->toArray()
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'type' => $task->type,
                'content' => $task->content
            ]
        ]);
        exit;
    });
    
    Route::get('/check', function () {
        return response()->json([
            'status' => 'test-api working',
            'time' => now()->toDateTimeString(),
            'quest_tasks_count' => QuestTask::count()
        ]);
    });
});