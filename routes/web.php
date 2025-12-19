<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DatabaseSchemaController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\Admin\QuestTaskController;

use App\Http\Controllers\QuestInteractiveController;
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
// Маршруты для работы с маршрутами
Route::prefix('routes')->name('routes.')->group(function () {
    // Публичные маршруты
    Route::get('/', [RouteController::class, 'index'])->name('index');
    Route::get('/create', [RouteController::class, 'create'])->name('create');
    Route::get('/{route}', [RouteController::class, 'show'])->name('show');
    Route::get('/{route}/export/gpx', [RouteController::class, 'exportGpx'])->name('export.gpx');
    
    // Маршруты требующие аутентификации
    Route::middleware(['auth'])->group(function () {
        Route::post('/', [RouteController::class, 'store'])->name('store');
        Route::get('/{route}/edit', [RouteController::class, 'edit'])->name('edit');
        Route::put('/{route}', [RouteController::class, 'update'])->name('update');
        Route::delete('/{route}', [RouteController::class, 'destroy'])->name('destroy');
        Route::post('/{route}/save', [RouteController::class, 'save'])->name('save');
        
        // Маршрут для подтверждения прохождения
        Route::get('/{route}/complete', [RouteController::class, 'complete'])->name('complete');
    });
});
   

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
    Route::get('/my', [QuestController::class, 'myQuests'])->name('my');
     Route::get('/{quest:slug}', [QuestController::class, 'show'])->name('show');;
    Route::get('/{slug}', [QuestController::class, 'create'])->name('craete');
    // Требуют авторизации
    Route::middleware(['auth'])->group(function () {
        Route::get('/my/quests', [QuestController::class, 'myQuests'])->name('my');
        Route::get('/achievements', [QuestController::class, 'achievements'])->name('achievements');
        Route::get('/badges', [QuestController::class, 'badges'])->name('badges');
        
        Route::post('/{quest}/start', [QuestController::class, 'start'])->name('start');
        Route::post('/{quest}/progress', [QuestController::class, 'updateProgress'])->name('progress.update');
        Route::post('/{quest}/cancel', [QuestController::class, 'cancel'])->name('cancel');
        Route::post('/complete', [QuestController::class, 'completeRoute'])->name('complete-route');
        Route::delete('/{userQuest}/abandon', [QuestController::class, 'abandon'])->name('abandon');
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

// Админ маршруты
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/routes', [AdminController::class, 'routes'])->name('routes.index');
    Route::get('/routes/{route}', [AdminController::class, 'show'])->name('routes.show');
    Route::post('/routes/{route}/moderate', [AdminController::class, 'moderateRoute'])->name('routes.moderate');
    Route::get('/quests', [AdminController::class, 'quests'])->name('quests.index');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');
    Route::post('/reports/{report}/handle', [AdminController::class, 'handleReport'])->name('reports.handle');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
     
    // Квесты
    Route::get('/quests', [\App\Http\Controllers\Admin\QuestController::class, 'index'])->name('quests.index');
    Route::get('/quests/create', [\App\Http\Controllers\Admin\QuestController::class, 'create'])->name('quests.create');
    Route::post('/quests', [\App\Http\Controllers\Admin\QuestController::class, 'store'])->name('quests.store');
    Route::get('/quests/{quest}', [\App\Http\Controllers\Admin\QuestController::class, 'show'])->name('quests.show');
    Route::get('/quests/{quest}/edit', [\App\Http\Controllers\Admin\QuestController::class, 'edit'])->name('quests.edit');
    Route::put('/quests/{quest}', [\App\Http\Controllers\Admin\QuestController::class, 'update'])->name('quests.update');
    Route::delete('/quests/{quest}', [\App\Http\Controllers\Admin\QuestController::class, 'destroy'])->name('quests.destroy');
    Route::post('/quests/{quest}/toggle-status', [\App\Http\Controllers\Admin\QuestController::class, 'toggleStatus'])->name('quests.toggle-status');
    Route::get('/quests/{quest}/stats', [\App\Http\Controllers\Admin\QuestController::class, 'stats'])->name('quests.stats');
    Route::post('/quests/{quest}/progress', [QuestController::class, 'updateProgress'])->name('quests.progress.update');
});

// Чат маршруты
Route::prefix('chats')->name('chats.')->middleware('auth')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/{chat}', [ChatController::class, 'show'])->name('show');
    Route::post('/private/{user}', [ChatController::class, 'createPrivate'])->name('private.create');
    Route::post('/route/{route}', [ChatController::class, 'createRouteChat'])->name('route.create');
    Route::delete('/{chat}', [ChatController::class, 'destroy'])->name('destroy');
});

// Сообщения
Route::prefix('messages')->name('messages.')->middleware('auth')->group(function () {
    Route::post('/{chat}', [MessageController::class, 'store'])->name('store');
    Route::put('/{message}', [MessageController::class, 'update'])->name('update');
    Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
    Route::post('/{message}/read', [MessageController::class, 'markAsRead'])->name('read');
});

// Комментарии
Route::prefix('comments')->name('comments.')->middleware('auth')->group(function () {
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
    Route::post('/{comment}/like', [CommentController::class, 'toggleLike'])->name('like');
    Route::post('/{comment}/pin', [CommentController::class, 'pin'])->name('pin');
    Route::post('/{comment}/unpin', [CommentController::class, 'unpin'])->name('unpin');
});


Route::get('/check-fk', function() {
    $tables = ['chats', 'chat_user', 'messages', 'comments', 'comment_likes'];
    
    $results = [];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $results[$table] = DB::select("
                SHOW CREATE TABLE $table
            ")[0]->{'Create Table'};
        }
    }
    
    return view('debug.fk', ['results' => $results]);
});


Route::get('/test-email', function() {
    try {
        // Проверяем настройки
        $config = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
        ];
        
        Log::info('Настройки почты:', $config);
        
        // Пробуем отправить тестовое письмо
        Mail::raw('Тестовое письмо от AutoRuta', function($message) {
            $message->to('test@example.com') // Замените на свой email
                    ->subject('Тест отправки почты');
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Письмо отправлено!',
            'config' => $config
        ]);
        
    } catch (\Exception $e) {
        Log::error('Тест почты провален', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage(),
            'config' => config('mail.mailers.smtp')
        ], 500);
    }
});


// Тестовые маршруты для авторизации
Route::prefix('test-auth')->name('test.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\TestLoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [\App\Http\Controllers\Auth\TestLoginController::class, 'login'])->name('login');
    Route::get('/direct-login/{email}', [\App\Http\Controllers\Auth\TestLoginController::class, 'directLogin'])->name('direct-login');
    Route::post('/verify-code', [\App\Http\Controllers\Auth\TestLoginController::class, 'verifyCode'])->name('verify-code');
});


// Тестовый маршрут для проверки почты
Route::get('/test-mail', function () {
    try {
        $code = rand(100000, 999999);
        
        \Mail::raw('Тестовое письмо. Код: ' . $code, function ($message) {
            $message->to('dgimmibos@yandex.ru')
                    ->subject('Тест отправки почты');
        });
        
        return 'Письмо отправлено! Код: ' . $code;
        
    } catch (\Exception $e) {
        return 'Ошибка: ' . $e->getMessage();
    }
});


// Тестовые маршруты
Route::get('/test-csrf-page', [TestController::class, 'showTestPage']);
Route::post('/test-csrf', [TestController::class, 'testCsrf']);




// Маршруты для навигации
Route::middleware(['auth'])->group(function () {
    // Навигация по маршруту
    Route::post('/routes/{route}/navigation/start', [NavigationController::class, 'start'])->name('routes.navigation.start');
    Route::get('/routes/{route}/navigate', [NavigationController::class, 'navigate'])->name('routes.navigate');
    Route::get('/routes/{session}/navigation', [NavigationController::class, 'show'])->name('routes.navigation.show');
    
    // Управление сессией
    Route::post('/navigation/sessions/{session}/pause', [NavigationController::class, 'pause'])->name('routes.navigation.pause');
    Route::post('/navigation/sessions/{session}/resume', [NavigationController::class, 'resume'])->name('routes.navigation.resume');
    Route::post('/navigation/sessions/{session}/complete', [NavigationController::class, 'complete'])->name('routes.navigation.complete');
    
    // Контрольные точки
    Route::post('/navigation/checkpoint/{checkpoint}/arrive', [NavigationController::class, 'arrive'])->name('routes.navigation.checkpoint.arrive');
    Route::post('/navigation/checkpoint/{checkpoint}/skip', [NavigationController::class, 'skip'])->name('routes.navigation.checkpoint.skip');
    Route::get('/navigation/checkpoint/{checkpoint}/arrival-info', [NavigationController::class, 'arrivalInfo'])->name('routes.navigation.checkpoint.arrival-info');
    
    // Фотографии и комментарии
    Route::post('/navigation/checkpoint/{checkpoint}/add-photo', [NavigationController::class, 'addPhoto'])->name('routes.navigation.checkpoint.add-photo');
    Route::post('/navigation/checkpoint/{checkpoint}/add-comment', [NavigationController::class, 'addComment'])->name('routes.navigation.checkpoint.add-comment');
});

Route::get('/database-schema', [DatabaseSchemaController::class, 'index']);

// Тестовый маршрут
Route::post('/test-route/create', [NavigationController::class, 'createTestRoute'])
    ->name('test.route.create')
    ->middleware('auth');

    // Интерактивные квесты
Route::middleware(['auth'])->prefix('quests')->name('quests.')->group(function () {
    // Основные маршруты
    Route::get('/', [QuestController::class, 'index'])->name('index');
    Route::get('/my', [QuestController::class, 'myQuests'])->name('my');
    Route::get('/{slug}', [QuestController::class, 'show'])->name('show');
    Route::post('/{quest}/start', [QuestController::class, 'start'])->name('start');
    
    // Интерактивные задания
    Route::prefix('interactive/{questSlug}')->name('interactive.')->group(function () {
        Route::get('/', [QuestInteractiveController::class, 'showCurrentTask'])->name('task');
        Route::post('/task/{taskId}/submit', [QuestInteractiveController::class, 'submitAnswer'])->name('submit');
        Route::post('/task/{taskId}/hint', [QuestInteractiveController::class, 'getHint'])->name('hint');
        Route::post('/task/{taskId}/location', [QuestInteractiveController::class, 'checkLocation'])->name('check.location');
        Route::post('/pause', [QuestInteractiveController::class, 'pauseQuest'])->name('pause');
        Route::post('/resume', [QuestInteractiveController::class, 'resumeQuest'])->name('resume');
        Route::post('/complete', [QuestInteractiveController::class, 'completeQuest'])->name('complete.early');
        Route::get('/chat', [QuestInteractiveController::class, 'questChat'])->name('chat');
    });
    
    // Лидерборд и достижения
    Route::get('/leaderboard', [QuestController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/achievements', [QuestController::class, 'achievements'])->name('achievements');
    Route::get('/badges', [QuestController::class, 'badges'])->name('badges');
});


   // Маршруты для профилей пользователей
Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/routes', [UserController::class, 'routes'])->name('routes');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::get('/{user}/achievements', [UserController::class, 'achievements'])->name('achievements');
    Route::get('/{user}/activity', [UserController::class, 'activity'])->name('activity');
});

// Чат маршруты
Route::prefix('chats')->name('chats.')->middleware('auth')->group(function () {
    Route::get('/create', [ChatController::class, 'create'])->name('create');
    Route::get('/{chat}', [ChatController::class, 'show'])->name('show');
     Route::post('/', [ChatController::class, 'store'])->name('store');
    Route::post('/private/{user}', [ChatController::class, 'createPrivate'])->name('private.create');
    Route::post('/route/{route}', [ChatController::class, 'createRouteChat'])->name('route.create');
    Route::post('/{chat}/add-users', [ChatController::class, 'addUsers'])->name('add-users');
    Route::delete('/{chat}/leave', [ChatController::class, 'leave'])->name('leave');
    Route::delete('/{chat}', [ChatController::class, 'destroy'])->name('destroy');
});

// Админ маршруты
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Задания квестов
    Route::get('/quests/{quest}/tasks', [QuestTaskController::class, 'index'])->name('quests.tasks.index');
    Route::get('/quests/{quest}/tasks/create', [QuestTaskController::class, 'create'])->name('quests.tasks.create');
    Route::post('/quests/{quest}/tasks', [QuestTaskController::class, 'store'])->name('quests.tasks.store');
    Route::get('/quests/{quest}/tasks/{task}/edit', [QuestTaskController::class, 'edit'])->name('quests.tasks.edit');
    Route::put('/quests/{quest}/tasks/{task}', [QuestTaskController::class, 'update'])->name('quests.tasks.update');
    Route::delete('/quests/{quest}/tasks/{task}', [QuestTaskController::class, 'destroy'])->name('quests.tasks.destroy');
    Route::post('/quests/{quest}/tasks/reorder', [QuestTaskController::class, 'reorder'])->name('quests.tasks.reorder');
});

// Навигация
Route::middleware(['auth'])->prefix('routes/{route}/navigation')->group(function () {
    Route::get('/', [NavigationController::class, 'navigate'])->name('routes.navigate');
    Route::post('/start', [NavigationController::class, 'start'])->name('routes.navigation.start');
    Route::post('/sessions/{session}/pause', [NavigationController::class, 'pause'])->name('routes.navigation.pause');
    Route::post('/sessions/{session}/resume', [NavigationController::class, 'resume'])->name('routes.navigation.resume');
    Route::post('/sessions/{session}/complete', [NavigationController::class, 'complete'])->name('routes.navigation.complete');
});

// Для тестирования
Route::post('/create-test-route', [NavigationController::class, 'createTestRoute'])
    ->middleware('auth')
    ->name('test.route.create');

    // routes/web.php - добавьте в конец файла
Route::get('/test-db-connection', function () {
    return [
        'status' => 'ok',
        'database' => config('database.default'),
        'quest_tasks_table_exists' => \Schema::hasTable('quest_tasks'),
        'quest_tasks_count' => \App\Models\QuestTask::count(),
        'task_6_exists' => \App\Models\QuestTask::find(6) ? 'yes' : 'no'
    ];
});

// routes/web.php - добавьте в конец файла, перед последней скобкой

// Тестовый маршрут для проверки подключения к БД
Route::get('/test-db-connection', function () {
    return response()->json([
        'status' => 'ok',
        'database' => config('database.default'),
        'quest_tasks_table_exists' => \Schema::hasTable('quest_tasks'),
        'quest_tasks_count' => \App\Models\QuestTask::count(),
        'task_6_exists' => \App\Models\QuestTask::find(6) ? 'yes' : 'no',
        'all_task_ids' => \App\Models\QuestTask::pluck('id')->toArray()
    ]);
});

// Тестовый маршрут для проверки задачи
Route::get('/test-task/{id}', function ($id) {
    try {
        $task = \App\Models\QuestTask::find($id);
        
        if (!$task) {
            return response()->json([
                'error' => 'Not Found',
                'message' => "QuestTask с ID {$id} не найден",
                'available_ids' => \App\Models\QuestTask::pluck('id')->toArray(),
                'total_tasks' => \App\Models\QuestTask::count()
            ], 404);
        }
        
        // Пытаемся декодировать content если это JSON строка
        $content = $task->content;
        if (is_string($content)) {
            try {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $content = $decoded;
                }
            } catch (\Exception $e) {
                // Оставляем как есть
            }
        }
        
        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'type' => $task->type,
                'quest_id' => $task->quest_id,
                'description' => $task->description,
                'content' => $content,
                'content_type' => gettype($task->content),
                'points' => $task->points,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at
            ],
            'debug' => [
                'model' => 'QuestTask',
                'table' => 'quest_tasks',
                'exists_in_db' => 'yes'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Проверка что API маршруты существуют
Route::get('/test-api-routes', function () {
    $routes = [];
    
    foreach (\Route::getRoutes() as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'api') !== false || strpos($uri, 'task') !== false) {
            $routes[] = [
                'uri' => $uri,
                'name' => $route->getName(),
                'methods' => $route->methods(),
                'action' => $route->getActionName()
            ];
        }
    }
    
    return response()->json([
        'total_routes' => count($routes),
        'routes' => $routes
    ]);
});

require __DIR__.'/api.php';