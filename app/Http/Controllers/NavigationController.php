<?php

namespace App\Http\Controllers;

use App\Models\RouteSession;
use App\Models\RouteCheckpoint;
use App\Models\TravelRoute;
use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NavigationController extends Controller
{
    /**
     * Начать навигацию по маршруту
     */
     public function start(Request $request, $routeId)
    {
        $user = Auth::user();
        $route = TravelRoute::findOrFail($routeId);
        
        // Проверяем, есть ли уже активная сессия
        $existingSession = RouteSession::where('user_id', $user->id)
            ->where('route_id', $routeId)
            ->whereIn('status', ['active', 'paused'])
            ->first();
        
        if ($existingSession) {
            return redirect()->route('routes.navigate', $routeId)
                ->with('info', 'У вас уже есть активная сессия для этого маршрута');
        }
        
        // Создаем новую сессию
        $session = RouteSession::create([
            'user_id' => $user->id,
            'route_id' => $routeId,
            'quest_id' => $request->input('quest_id'),
            'status' => 'active',
            'started_at' => now(),
            'current_checkpoint_id' => $route->checkpoints()->orderBy('order')->first()->id ?? null
        ]);
        
        return redirect()->route('routes.navigate', $routeId)
            ->with('success', 'Навигация начата!');
    }
    
    /**
     * Показать страницу навигации
     */
     public function navigate($routeId)
{
    $user = Auth::user();
    $route = TravelRoute::with(['checkpoints', 'points'])->findOrFail($routeId);
    
    // Находим активную сессию
    $session = RouteSession::where('user_id', $user->id)
        ->where('route_id', $routeId)
        ->whereIn('status', ['active', 'paused'])
        ->first();
    
    if (!$session) {
        // Создаем новую сессию
        $firstCheckpoint = $route->checkpoints()->orderBy('order')->first();
        
        $session = RouteSession::create([
            'user_id' => $user->id,
            'route_id' => $routeId,
            'status' => 'active',
            'started_at' => now(),
            'current_checkpoint_id' => $firstCheckpoint->id ?? null
        ]);
    }
    
    // Получаем все чекпоинты
    $checkpoints = $route->checkpoints()->orderBy('order')->get();
    
    // Текущий чекпоинт
    $currentCheckpoint = $session->current_checkpoint_id 
        ? RouteCheckpoint::find($session->current_checkpoint_id)
        : $checkpoints->first();
    
    // Вычисляем прогресс
    $completedCheckpoints = $session->checkpoints_visited 
        ? count($session->checkpoints_visited) 
        : 0;
    $totalCheckpoints = $checkpoints->count();
    $progressPercentage = $totalCheckpoints > 0 
        ? round(($completedCheckpoints / $totalCheckpoints) * 100) 
        : 0;
    
    // Инициализируем переменную activeQuests
    $activeQuests = collect(); // По умолчанию пустая коллекция
    
    // Получаем активные квесты
    try {
        // Способ 1: через userQuests
        if (method_exists($user, 'userQuests')) {
            $activeQuests = $user->userQuests()
                ->where('status', 'in_progress')
                ->whereHas('quest.routes', function($q) use ($route) {
                    $q->where('travel_routes.id', $route->id);
                })
                ->with(['quest' => function($q) {
                    $q->with(['tasks' => function($q) {
                        $q->orderBy('order');
                    }, 'badge']);
                }])
                ->get()
                ->pluck('quest');
        }
        // Способ 2: через связь quests
        elseif (method_exists($user, 'quests')) {
            $activeQuests = $user->quests()
                ->where('user_quests.status', 'in_progress')
                ->whereHas('routes', function($q) use ($route) {
                    $q->where('travel_routes.id', $route->id);
                })
                ->with(['tasks' => function($q) {
                    $q->orderBy('order');
                }, 'badge'])
                ->get();
        }
        
        // Добавляем вычисленные поля к квестам
        $activeQuests = $activeQuests->map(function($quest) use ($user) {
            // Вычисляем прогресс квеста
            $completedTasks = 0;
            $totalTasks = $quest->tasks->count();
            
            if ($totalTasks > 0) {
                // Получаем прогресс по заданиям
                $completedTasks = \App\Models\QuestTaskProgress::where('user_id', $user->id)
                    ->where('quest_id', $quest->id)
                    ->where('status', 'completed')
                    ->count();
                    
                $quest->progress_percentage = round(($completedTasks / $totalTasks) * 100);
            } else {
                $quest->progress_percentage = 0;
            }
            
            // Добавляем userProgress
            $quest->userProgress = (object)[
                'progress_percentage' => $quest->progress_percentage
            ];
            
            // Добавляем метки для сложности
            $quest->difficulty_label = $this->getDifficultyLabel($quest->difficulty);
            
            // Добавляем иконки для типов заданий
            $quest->tasks = $quest->tasks->map(function($task) use ($user) {
                $task->type_icon = $this->getTaskTypeIcon($task->type);
                $task->type_label = $this->getTaskTypeLabel($task->type);
                
                // Проверяем, выполнено ли задание
                $taskProgress = \App\Models\QuestTaskProgress::where('user_id', $user->id)
                    ->where('task_id', $task->id)
                    ->first();
                    
                $task->userProgress = $taskProgress;
                $task->is_completed = $taskProgress && $taskProgress->status === 'completed';
                
                // Метод для проверки возможности выполнения
                $task->canBeCompleted = function($checkpoint) use ($task) {
                    return $task->location_id === null || 
                           ($checkpoint && $task->location_id == $checkpoint->id);
                };
                
                return $task;
            });
            
            return $quest;
        });
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при получении квестов: ' . $e->getMessage());
        $activeQuests = collect(); // Возвращаем пустую коллекцию в случае ошибки
    }
    
    // Вычисляем заработанный XP
    $earnedXp = $session->earned_xp ?? 0;
    
    return view('navigation.navigate', compact(
        'route', 
        'session', 
        'checkpoints',
        'currentCheckpoint',
        'completedCheckpoints',
        'totalCheckpoints',
        'progressPercentage',
        'activeQuests',
        'earnedXp'
    ));
}

// Вспомогательные методы для меток и иконок
private function getDifficultyLabel($difficulty)
{
    $labels = [
        'easy' => 'Легкий',
        'medium' => 'Средний',
        'hard' => 'Сложный',
        'expert' => 'Эксперт'
    ];
    
    return $labels[$difficulty] ?? 'Средний';
}

private function getTaskTypeIcon($type)
{
    $icons = [
        'text' => 'fas fa-font',
        'image' => 'fas fa-image',
        'code' => 'fas fa-code',
        'cipher' => 'fas fa-key',
        'location' => 'fas fa-map-marker-alt',
        'puzzle' => 'fas fa-puzzle-piece',
        'quiz' => 'fas fa-question-circle'
    ];
    
    return $icons[$type] ?? 'fas fa-tasks';
}

private function getTaskTypeLabel($type)
{
    $labels = [
        'text' => 'Текстовое',
        'image' => 'Фотография',
        'code' => 'Код',
        'cipher' => 'Шифр',
        'location' => 'Локация',
        'puzzle' => 'Головоломка',
        'quiz' => 'Викторина'
    ];
    
    return $labels[$type] ?? 'Задание';
}
    /**
     * Показать навигацию по сессии
     */
    public function show(RouteSession $session)
    {
        // Проверка прав доступа
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }
        
        $route = $session->route;
        $checkpoints = $session->checkpoints()->with('point')->orderBy('order')->get();
        
        // Текущая контрольная точка
        $currentCheckpoint = $checkpoints->firstWhere('status', 'active') 
            ?? $checkpoints->firstWhere('status', 'pending');
            
        // Вычисляем прогресс
        $totalCheckpoints = $checkpoints->count();
        $completedCheckpoints = $checkpoints->where('status', 'completed')->count();
        $progressPercentage = $totalCheckpoints > 0 
            ? round(($completedCheckpoints / $totalCheckpoints) * 100) 
            : 0;
            
        // Получаем активные квесты для этой сессии
        $activeQuests = collect();
        if ($session->quest_id) {
            $quest = Quest::find($session->quest_id);
            if ($quest) {
                $activeQuests = collect([$quest]);
            }
        } else {
            // Получаем все квесты пользователя, связанные с этим маршрутом
            $activeQuests = Auth::user()->quests()
                ->where('status', 'active')
                ->whereHas('routes', function($q) use ($route) {
                    $q->where('travel_routes.id', $route->id);
                })
                ->get();
        }
        
        return view('routes.navigate', compact(
            'route',
            'session',
            'checkpoints',
            'currentCheckpoint',
            'totalCheckpoints',
            'completedCheckpoints',
            'progressPercentage',
            'activeQuests'
        ));
    }
    
    /**
     * Приостановить навигацию
     */
    public function pause(RouteSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }
        
        $session->update([
            'status' => 'paused',
            'paused_at' => now()
        ]);
        
        return redirect()->route('routes.show', $session->route_id)
            ->with('success', 'Навигация приостановлена');
    }
    
    /**
     * Возобновить навигацию
     */
    public function resume(RouteSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }
        
        $session->update([
            'status' => 'active',
            'paused_at' => null
        ]);
        
        return redirect()->route('routes.navigate', $session->route_id)
            ->with('success', 'Навигация возобновлена');
    }
    
    /**
     * Завершить навигацию
     */
    public function complete(RouteSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }
        
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'ended_at' => now()
        ]);
        
        // Проверяем, нужно ли отметить маршрут как пройденный
        $route = $session->route;
        $user = Auth::user();
        
        if (!$route->completions()->where('user_id', $user->id)->exists()) {
            $route->completions()->create([
                'user_id' => $user->id,
                'completed_at' => now(),
                'session_id' => $session->id
            ]);
            
            // Обновляем статистику
            $route->increment('completions_count');
            
            // Награждаем пользователя опытом
            $user->increment('experience_points', $route->experience_reward ?? 10);
            
            // Проверяем выполнение квестов
            $this->checkQuestCompletion($session);
        }
        
        return redirect()->route('routes.show', $route)
            ->with('success', 'Маршрут успешно завершен!');
    }
    
    /**
     * Прибытие на контрольную точку
     */
    public function arrive(Request $request, RouteCheckpoint $checkpoint)
    {
        $session = $checkpoint->session;
        
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        // Помечаем точку как пройденную
        $checkpoint->update([
            'status' => 'completed',
            'arrived_at' => now(),
            'comment' => $request->input('comment')
        ]);
        
        // Сохраняем фото если есть
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('checkpoint-photos', 'public');
            $checkpoint->photos()->create([
                'path' => $path,
                'uploaded_by' => Auth::id()
            ]);
        }
        
        // Находим следующую точку
        $nextCheckpoint = RouteCheckpoint::where('session_id', $session->id)
            ->where('order', '>', $checkpoint->order)
            ->where('status', 'pending')
            ->orderBy('order')
            ->first();
            
        if ($nextCheckpoint) {
            // Активируем следующую точку
            $nextCheckpoint->update(['status' => 'active']);
            $session->update(['current_checkpoint_id' => $nextCheckpoint->id]);
        } else {
            // Это последняя точка, можно завершить маршрут
            $session->update(['current_checkpoint_id' => null]);
        }
        
        // Проверяем выполнение заданий квеста
        $this->checkQuestProgress($session);
        
        return response()->json([
            'success' => true,
            'message' => 'Точка успешно отмечена',
            'next_checkpoint' => $nextCheckpoint,
            'completed' => !$nextCheckpoint
        ]);
    }
    
    /**
     * Пропустить контрольную точку
     */
    public function skip(RouteCheckpoint $checkpoint)
    {
        $session = $checkpoint->session;
        
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        $checkpoint->update([
            'status' => 'skipped',
            'skipped_at' => now()
        ]);
        
        // Находим следующую точку
        $nextCheckpoint = RouteCheckpoint::where('session_id', $session->id)
            ->where('order', '>', $checkpoint->order)
            ->where('status', 'pending')
            ->orderBy('order')
            ->first();
            
        if ($nextCheckpoint) {
            $nextCheckpoint->update(['status' => 'active']);
            $session->update(['current_checkpoint_id' => $nextCheckpoint->id]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Точка пропущена'
        ]);
    }
    
    /**
     * Информация о прибытии на точку
     */
    public function arrivalInfo(RouteCheckpoint $checkpoint)
    {
        $session = $checkpoint->session;
        
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }
        
        $point = $checkpoint->point;
        $quests = $point->quests()->where('status', 'active')->get();
        
        return response()->json([
            'point' => [
                'title' => $point->title,
                'description' => $point->description,
                'type' => $point->type
            ],
            'quests' => $quests->map(function($quest) {
                return [
                    'id' => $quest->id,
                    'name' => $quest->name,
                    'description' => $quest->description,
                    'reward_xp' => $quest->reward_xp,
                    'reward_badge' => $quest->reward_badge
                ];
            })
        ]);
    }
    
    /**
     * Проверка прогресса квеста
     */
    private function checkQuestProgress(RouteSession $session)
    {
        if ($session->quest_id) {
            $quest = Quest::find($session->quest_id);
            if ($quest) {
                $completedPoints = $session->checkpoints()
                    ->where('status', 'completed')
                    ->count();
                    
                // Обновляем прогресс квеста
                $userQuest = $session->user->userQuests()
                    ->where('quest_id', $quest->id)
                    ->where('status', 'started')
                    ->first();
                    
                if ($userQuest) {
                    $userQuest->update([
                        'progress' => $completedPoints,
                        'progress_percentage' => min(100, ($completedPoints / max($quest->required_points, 1)) * 100)
                    ]);
                    
                    // Проверяем, выполнен ли квест
                    if ($completedPoints >= $quest->required_points) {
                        $this->completeQuest($userQuest);
                    }
                }
            }
        }
    }
    
    /**
     * Проверка завершения квеста
     */
    private function checkQuestCompletion(RouteSession $session)
    {
        $user = $session->user;
        
        // Проверяем все активные квесты пользователя
        $userQuests = $user->userQuests()
            ->where('status', 'started')
            ->with('quest')
            ->get();
            
        foreach ($userQuests as $userQuest) {
            $quest = $userQuest->quest;
            
            // Проверяем, входит ли маршрут в квест
            if ($quest->routes()->where('travel_routes.id', $session->route_id)->exists()) {
                $completedRoutes = $quest->routes()
                    ->whereHas('completions', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->count();
                    
                $userQuest->update([
                    'progress' => $completedRoutes,
                    'progress_percentage' => min(100, ($completedRoutes / max($quest->required_routes, 1)) * 100)
                ]);
                
                // Проверяем, выполнен ли квест
                if ($completedRoutes >= $quest->required_routes) {
                    $this->completeQuest($userQuest);
                }
            }
        }
    }
    
    /**
     * Завершить квест
     */
    private function completeQuest($userQuest)
    {
        $userQuest->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        // Награждаем пользователя
        $user = $userQuest->user;
        $quest = $userQuest->quest;
        
        if ($quest->reward_xp > 0) {
            $user->increment('experience_points', $quest->reward_xp);
        }
        
        if ($quest->reward_badge) {
            // Выдаем значок пользователю
            $user->badges()->create([
                'name' => $quest->reward_badge,
                'description' => 'Награда за выполнение квеста: ' . $quest->name,
                'icon' => $quest->badge_icon ?? 'fa-medal',
                'color' => $quest->badge_color ?? '#F59E0B'
            ]);
        }
    }



    public function createTestRoute(Request $request)
{
    $user = Auth::user();
    
    // Создаем тестовый маршрут
    $route = TravelRoute::create([
        'user_id' => $user->id,
        'title' => 'Тестовый маршрут по Москве',
        'slug' => 'test-route-moscow-' . time(),
        'description' => 'Тестовый маршрут для проверки навигации и квестов.',
        'length_km' => 25.5,
        'duration_minutes' => 180,
        'difficulty' => 'medium',
        'road_type' => 'asphalt',
        'start_coordinates' => json_encode(['lat' => 55.7558, 'lng' => 37.6173]),
        'is_published' => true
    ]);
    
    // Создаем точки маршрута
    $points = [
        [
            'title' => 'Красная площадь',
            'description' => 'Главная площадь Москвы',
            'type' => 'attraction',
            'lat' => 55.7540,
            'lng' => 37.6201,
            'order' => 1
        ],
        [
            'title' => 'Парк Горького',
            'description' => 'Центральный парк культуры и отдыха',
            'type' => 'park',
            'lat' => 55.7296,
            'lng' => 37.6031,
            'order' => 2
        ],
        [
            'title' => 'Воробьевы горы',
            'description' => 'Смотровая площадка с видом на Москву',
            'type' => 'viewpoint',
            'lat' => 55.7102,
            'lng' => 37.5493,
            'order' => 3
        ],
        [
            'title' => 'Кафе "У самовара"',
            'description' => 'Традиционная русская кухня',
            'type' => 'cafe',
            'lat' => 55.7480,
            'lng' => 37.6345,
            'order' => 4
        ],
        [
            'title' => 'Отель "Метрополь"',
            'description' => 'Исторический отель в центре Москвы',
            'type' => 'hotel',
            'lat' => 55.7600,
            'lng' => 37.6197,
            'order' => 5
        ]
    ];
    
    foreach ($points as $pointData) {
        $route->points()->create($pointData);
    }
    
    // Создаем тестовый квест
    $quest = Quest::create([
        'title' => 'Знакомство с Москвой',
        'slug' => 'moscow-introduction-' . time(),
        'description' => 'Посетите основные достопримечательности Москвы',
        'type' => 'collection',
        'difficulty' => 'easy',
        'required_points' => 3,
        'reward_exp' => 100,
        'reward_coins' => 50,
        'is_active' => true
    ]);
    
    // Связываем квест с маршрутом
    $quest->routes()->attach($route->id, [
        'order' => 1,
        'is_required' => true
    ]);
    
    // Связываем квест с точками
    foreach ($route->points as $point) {
        $quest->points()->attach($point->id, [
            'order' => $point->order,
            'is_required' => true
        ]);
    }
    
    // Создаем сессию навигации
    $session = RouteSession::create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'quest_id' => $quest->id,
        'status' => 'active',
        'started_at' => now()
    ]);
    
    // Создаем контрольные точки
    foreach ($route->points as $point) {
        RouteCheckpoint::create([
            'session_id' => $session->id,
            'point_id' => $point->id,
            'order' => $point->order,
            'status' => $point->order == 1 ? 'active' : 'pending'
        ]);
    }
    
    return redirect()->route('routes.navigate', $route)
        ->with('success', 'Тестовый маршрут создан! Начинаем навигацию.');
}
}