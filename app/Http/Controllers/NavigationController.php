<?php

namespace App\Http\Controllers;

use App\Models\RouteSession;
use App\Models\RouteCheckpoint;
use App\Models\TravelRoute;
use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    $route = TravelRoute::with(['points' => function($q) {
        $q->orderBy('order');
    }])->findOrFail($routeId);
    
   debugLog('Начало навигации', ['route_id' => $routeId, 'user_id' => $user->id]);
    
    // Находим или создаем сессию
    $session = RouteSession::where('user_id', $user->id)
        ->where('route_id', $routeId)
        ->whereIn('status', ['active', 'paused'])
        ->first();
    
    if (!$session) {
        debugLog('Создание новой сессии');
        
        $session = RouteSession::create([
            'user_id' => $user->id,
            'route_id' => $routeId,
            'status' => 'active',
            'started_at' => now(),
            'checkpoints_visited' => [],
            'distance_traveled' => 0,
            'duration_seconds' => 0
        ]);
    }
    
   // debugLog('Сессия', $session->toArray());
    
    // Получаем точки маршрута
    $points = $route->points;
    
    // Определяем текущую точку
    $currentPoint = null;
    $visitedPoints = $session->checkpoints_visited ?? [];
    
    //debugLog('Посещенные точки', $visitedPoints);
    
    // Находим первую непосещенную точку
    foreach ($points as $point) {
        if (!in_array($point->id, $visitedPoints)) {
            $currentPoint = $point;
            break;
        }
    }
    
    // Если все точки посещены, берем последнюю
    if (!$currentPoint && count($points) > 0) {
        $currentPoint = $points->last();
    }
    
    //debugLog('Текущая точка', $currentPoint ? $currentPoint->toArray() : null);
    
    // Вычисляем прогресс
    $completedPoints = count($visitedPoints);
    $totalPoints = $points->count();
    $progressPercentage = $totalPoints > 0 
        ? round(($completedPoints / $totalPoints) * 100) 
        : 0;
    
    // Получаем данные для карты
    $mapData = [
        'route' => [
            'id' => $route->id,
            'title' => $route->title,
            'start_coordinates' => $route->start_coordinates ? json_decode($route->start_coordinates, true) : null,
            'end_coordinates' => $route->end_coordinates ? json_decode($route->end_coordinates, true) : null,
            'path_coordinates' => $route->path_coordinates ? json_decode($route->path_coordinates, true) : [],
        ],
        'points' => $points->map(function($point) use ($visitedPoints) {
            return [
                'id' => $point->id,
                'title' => $point->title,
                'description' => $point->description,
                'type' => $point->type,
                'lat' => $point->lat,
                'lng' => $point->lng,
                'order' => $point->order,
                'is_visited' => in_array($point->id, $visitedPoints),
                'type_icon' => $this->getPointTypeIcon($point->type),
                'type_label' => $this->getPointTypeLabel($point->type),
                'type_color' => $this->getPointTypeColor($point->type),
            ];
        })->toArray(),
        'current_point' => $currentPoint ? [
            'id' => $currentPoint->id,
            'title' => $currentPoint->title,
            'lat' => $currentPoint->lat,
            'lng' => $currentPoint->lng,
            'type' => $currentPoint->type,
        ] : null,
        'session' => [
            'id' => $session->id,
            'status' => $session->status,
            'visited_points' => $visitedPoints,
            'distance_traveled' => $session->distance_traveled,
            'duration_seconds' => $session->duration_seconds,
        ]
    ];
    
   // debugLog('Данные для карты подготовлены', [
    //    'points_count' => count($mapData['points']),
    //    'has_current_point' => !is_null($mapData['current_point']),
   //     'completed_points' => $completedPoints,
   //     'total_points' => $totalPoints
   // ]);
    
    // Получаем активные квесты
    $activeQuests = $this->getActiveQuests($user, $route);
    
    // Передаем данные в представление
    return view('navigation.navigate', [
        'route' => $route,
        'session' => $session,
        'points' => $points,
        'currentPoint' => $currentPoint,
        'completedPoints' => $completedPoints,
        'totalPoints' => $totalPoints,
        'progressPercentage' => $progressPercentage,
        'activeQuests' => $activeQuests,
        'mapData' => $mapData,
        'visitedPoints' => $visitedPoints,
    ]);
}

/**
 * Получить активные квесты пользователя для маршрута
 */
private function getActiveQuests($user, $route)
{
    try {
        $activeQuests = collect();
        
        // Проверяем, есть ли у пользователя активные квесты
        if (method_exists($user, 'quests')) {
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
        
        return $activeQuests->map(function($quest) use ($user) {
            // Добавляем метки
            $quest->difficulty_label = $this->getDifficultyLabel($quest->difficulty);
            $quest->type_label = $this->getQuestTypeLabel($quest->type);
            
            // Добавляем иконки для заданий
            if ($quest->tasks) {
                $quest->tasks = $quest->tasks->map(function($task) {
                    $task->type_icon = $this->getTaskTypeIcon($task->type);
                    $task->type_label = $this->getTaskTypeLabel($task->type);
                    return $task;
                });
            }
            
            return $quest;
        });
        
    } catch (\Exception $e) {
        debugLog('Ошибка получения квестов', $e->getMessage());
        return collect();
    }
}

/**
 * Вспомогательные методы для меток
 */
private function getPointTypeIcon($type)
{
    $icons = [
        'viewpoint' => 'fas fa-binoculars',
        'cafe' => 'fas fa-utensils',
        'hotel' => 'fas fa-bed',
        'attraction' => 'fas fa-landmark',
        'gas_station' => 'fas fa-gas-pump',
        'camping' => 'fas fa-campground',
        'photo_spot' => 'fas fa-camera',
        'nature' => 'fas fa-tree',
        'historical' => 'fas fa-monument',
        'other' => 'fas fa-map-marker-alt'
    ];
    
    return $icons[$type] ?? 'fas fa-map-marker-alt';
}

private function getPointTypeLabel($type)
{
    $labels = [
        'viewpoint' => 'Смотровая площадка',
        'cafe' => 'Кафе',
        'hotel' => 'Отель',
        'attraction' => 'Достопримечательность',
        'gas_station' => 'Заправка',
        'camping' => 'Кемпинг',
        'photo_spot' => 'Фото-спот',
        'nature' => 'Природа',
        'historical' => 'Историческое место',
        'other' => 'Точка интереса'
    ];
    
    return $labels[$type] ?? 'Точка интереса';
}

private function getPointTypeColor($type)
{
    $colors = [
        'viewpoint' => '#F59E0B',
        'cafe' => '#EF4444',
        'hotel' => '#3B82F6',
        'attraction' => '#6366F1',
        'gas_station' => '#6B7280',
        'camping' => '#10B981',
        'photo_spot' => '#8B5CF6',
        'nature' => '#059669',
        'historical' => '#DC2626',
        'other' => '#6B7280'
    ];
    
    return $colors[$type] ?? '#6B7280';
}

private function getQuestTypeLabel($type)
{
    $labels = [
        'collection' => 'Коллекционный',
        'challenge' => 'Испытание',
        'weekend' => 'Выходной',
        'story' => 'История',
        'user' => 'Пользовательский'
    ];
    
    return $labels[$type] ?? 'Квест';
}

// Вспомогательная функция для логирования
function debugLog($message, $data = null)
{
    \Log::info('[Navigation] ' . $message, $data ? (array) $data : []);
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