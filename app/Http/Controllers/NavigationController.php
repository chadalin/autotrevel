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
    public function start(Request $request, TravelRoute $route)
    {
        // Проверяем, есть ли уже активная сессия
        $activeSession = RouteSession::where('user_id', Auth::id())
            ->where('route_id', $route->id)
            ->whereIn('status', ['active', 'paused'])
            ->first();
            
        if ($activeSession) {
            return redirect()->route('routes.navigate', $route)
                ->with('error', 'У вас уже есть активная сессия на этом маршруте');
        }
        
        // Проверяем, есть ли не завершенные сессии
        $existingSession = RouteSession::where('user_id', Auth::id())
            ->where('route_id', $route->id)
            ->where('status', 'started')
            ->first();
            
        if ($existingSession) {
            // Возобновляем существующую сессию
            $existingSession->update([
                'status' => 'active',
                'started_at' => now(),
                'paused_at' => null
            ]);
            
            return redirect()->route('routes.navigate', $route)
                ->with('success', 'Сессия навигации возобновлена');
        }
        
        // Создаем новую сессию
        $session = RouteSession::create([
            'user_id' => Auth::id(),
            'route_id' => $route->id,
            'status' => 'active',
            'started_at' => now(),
            'current_checkpoint_id' => $route->points->first()->id ?? null,
            'quest_id' => $request->input('quest_id')
        ]);
        
        // Создаем контрольные точки для сессии
        foreach ($route->points as $point) {
            RouteCheckpoint::create([
                'session_id' => $session->id,
                'point_id' => $point->id,
                'order' => $point->order,
                'status' => 'pending'
            ]);
        }
        
        return redirect()->route('routes.navigate', $route)
            ->with('success', 'Навигация по маршруту начата');
    }
    
    /**
     * Показать страницу навигации
     */
    public function navigate(RouteSession $session)
{
    // Проверка прав доступа
  //  if ($session->user_id !== Auth::id()) {
     //   abort(403);
    //}
    
    $route = $session->route;
    
    // ПРАВИЛЬНО: получаем чекпоинты маршрута, а не сессии
    $checkpoints = $route->checkpoints()->orderBy('order')->get();
    
    // Определяем текущий чекпоинт
    $currentCheckpoint = null;
    if ($session->current_checkpoint_id) {
        $currentCheckpoint = RouteCheckpoint::find($session->current_checkpoint_id);
    } else {
        // Если нет текущего чекпоинта, берем первый
        $currentCheckpoint = $checkpoints->first();
    }
    
    // Получаем посещенные чекпоинты
    $visitedCheckpoints = json_decode($session->checkpoints_visited ?? '[]', true);
    
    return view('navigation.index', [
        'session' => $session,
        'route' => $route,
        'checkpoints' => $checkpoints,
        'currentCheckpoint' => $currentCheckpoint,
        'visitedCheckpoints' => $visitedCheckpoints,
        'progress' => $this->calculateProgress($checkpoints->count(), count($visitedCheckpoints)),
    ]);
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