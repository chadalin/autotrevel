<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteSession;
use App\Models\RouteCheckpoint;
use App\Models\Quest;
use App\Models\UserQuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\QuestVerificationService;

class RouteNavigationController extends Controller
{
    // Запуск маршрута
    public function start(Request $request, Route $route)
    {
        $user = Auth::user();
        $questId = $request->input('quest_id');
        $userQuest = null;

        // Проверяем, есть ли уже активная сессия
        $activeSession = RouteSession::where('user_id', $user->id)
            ->where('route_id', $route->id)
            ->whereIn('status', [RouteSession::STATUS_ACTIVE, RouteSession::STATUS_PAUSED])
            ->first();

        if ($activeSession) {
            return redirect()->route('routes.navigate', $route)
                ->with('info', 'У вас уже есть активная сессия для этого маршрута');
        }

        // Если запускаем в рамках квеста
        if ($questId) {
            $quest = Quest::findOrFail($questId);
            $userQuest = $user->userQuests()
                ->where('quest_id', $quest->id)
                ->where('status', UserQuest::STATUS_STARTED)
                ->first();

            if (!$userQuest) {
                return back()->with('error', 'У вас нет активного квеста');
            }
        }

        // Создаем сессию
        $session = RouteSession::create([
            'user_id' => $user->id,
            'route_id' => $route->id,
            'quest_id' => $questId,
            'user_quest_id' => $userQuest?->id,
            'status' => RouteSession::STATUS_ACTIVE,
            'started_at' => now(),
            'current_checkpoint_index' => 0,
            'checkpoints_data' => $this->prepareCheckpoints($route),
        ]);

        // Создаем записи для точек маршрута
        $this->createCheckpoints($session, $route);

        return redirect()->route('routes.navigate', $route)
            ->with('success', 'Маршрут запущен! Удачи в пути!');
    }

    // Страница навигации
    public function navigate(Route $route)
    {
        $user = Auth::user();
        
        // Получаем активную сессию
        $session = RouteSession::where('user_id', $user->id)
            ->where('route_id', $route->id)
            ->whereIn('status', [RouteSession::STATUS_ACTIVE, RouteSession::STATUS_PAUSED])
            ->with(['checkpoints.point'])
            ->first();

        if (!$session) {
            // Если нет активной сессии, перенаправляем на стартовую страницу
            return redirect()->route('routes.show', $route)
                ->with('info', 'Для начала навигации запустите маршрут');
        }

        // Получаем текущую точку
        $currentCheckpoint = $session->checkpoints
            ->where('order', $session->current_checkpoint_index)
            ->first();

        $nextCheckpoint = $session->checkpoints
            ->where('order', $session->current_checkpoint_index + 1)
            ->first();

        // Загружаем маршрут с точками
        $route->load(['points' => function($query) {
            $query->orderBy('order');
        }]);

        // Статистика
        $completedCheckpoints = $session->checkpoints->where('status', RouteCheckpoint::STATUS_COMPLETED)->count();
        $totalCheckpoints = $session->checkpoints->count();
        $progressPercentage = $totalCheckpoints > 0 ? round(($completedCheckpoints / $totalCheckpoints) * 100) : 0;

        return view('routes.navigate', compact(
            'route',
            'session',
            'currentCheckpoint',
            'nextCheckpoint',
            'progressPercentage',
            'completedCheckpoints',
            'totalCheckpoints'
        ));
    }

    // Отметка прибытия на точку
    public function arriveAtCheckpoint(Request $request, RouteSession $session, $checkpointId)
    {
        $checkpoint = RouteCheckpoint::where('route_session_id', $session->id)
            ->where('id', $checkpointId)
            ->firstOrFail();

        // Проверяем координаты пользователя
        $userLat = $request->input('lat');
        $userLng = $request->input('lng');
        
        $coordinates = $userLat && $userLng ? [
            'lat' => $userLat,
            'lng' => $userLng,
            'accuracy' => $request->input('accuracy', 50),
            'timestamp' => now(),
        ] : null;

        $checkpoint->markAsArrived($coordinates);

        // Обновляем текущую точку в сессии
        $session->update([
            'current_checkpoint_index' => $checkpoint->order,
            'current_coordinates' => $coordinates,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Вы прибыли на точку!',
            'checkpoint' => $checkpoint,
        ]);
    }

    // Завершение точки (загрузка фото)
    public function completeCheckpoint(Request $request, RouteSession $session, $checkpointId)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'notes' => 'nullable|string|max:500',
        ]);

        $checkpoint = RouteCheckpoint::where('route_session_id', $session->id)
            ->where('id', $checkpointId)
            ->firstOrFail();

        // Сохраняем фото
        $photoPath = $this->saveCheckpointPhoto($request->file('photo'), $session, $checkpoint);

        // Отмечаем точку как завершенную
        $checkpoint->markAsCompleted($photoPath, $request->input('notes'));

        // Если есть квест, проверяем выполнение
        if ($session->quest_id) {
            $verificationService = new QuestVerificationService();
            $result = $verificationService->verifyRouteCompletion(
                $session->user,
                $session->route,
                $request->file('photo'),
                $session->quest_id
            );

            if (!$result['success']) {
                return back()->with('error', $result['message']);
            }
        }

        // Переходим к следующей точке
        $nextCheckpoint = $session->checkpoints()
            ->where('order', $checkpoint->order + 1)
            ->first();

        if ($nextCheckpoint) {
            $session->update(['current_checkpoint_index' => $nextCheckpoint->order]);
        } else {
            // Все точки пройдены - завершаем маршрут
            $this->completeRoute($session);
        }

        return back()->with('success', 'Точка успешно завершена! Фото сохранено.');
    }

    // Пауза маршрута
    public function pause(RouteSession $session)
    {
        $session->pauseSession();
        return back()->with('success', 'Маршрут приостановлен');
    }

    // Возобновление маршрута
    public function resume(RouteSession $session)
    {
        $session->resumeSession();
        return back()->with('success', 'Маршрут возобновлен');
    }

    // Завершение маршрута
    public function complete(RouteSession $session)
    {
        $this->completeRoute($session);
        return redirect()->route('routes.show', $session->route)
            ->with('success', 'Маршрут успешно завершен!');
    }

    // Пропуск точки
    public function skipCheckpoint(RouteSession $session, $checkpointId)
    {
        $checkpoint = RouteCheckpoint::where('route_session_id', $session->id)
            ->where('id', $checkpointId)
            ->firstOrFail();

        $checkpoint->update(['status' => RouteCheckpoint::STATUS_SKIPPED]);

        // Переходим к следующей точке
        $nextCheckpoint = $session->checkpoints()
            ->where('order', $checkpoint->order + 1)
            ->first();

        if ($nextCheckpoint) {
            $session->update(['current_checkpoint_index' => $nextCheckpoint->order]);
        }

        return back()->with('info', 'Точка пропущена');
    }

    // Вспомогательные методы
    private function prepareCheckpoints(Route $route): array
    {
        $checkpoints = [];
        $points = $route->points()->orderBy('order')->get();

        foreach ($points as $point) {
            $checkpoints[] = [
                'id' => $point->id,
                'title' => $point->title,
                'type' => $point->type,
                'description' => $point->description,
                'lat' => $point->lat,
                'lng' => $point->lng,
                'order' => $point->order,
                'photos' => $point->photos ?? [],
                'requirements' => $this->getPointRequirements($point),
            ];
        }

        return $checkpoints;
    }

    private function createCheckpoints(RouteSession $session, Route $route): void
    {
        $points = $route->points()->orderBy('order')->get();
        $checkpoints = [];

        foreach ($points as $index => $point) {
            RouteCheckpoint::create([
                'route_session_id' => $session->id,
                'point_id' => $point->id,
                'order' => $index,
                'status' => RouteCheckpoint::STATUS_PENDING,
            ]);
        }
    }

    private function saveCheckpointPhoto($photo, RouteSession $session, RouteCheckpoint $checkpoint): string
    {
        $path = "route_sessions/{$session->id}/checkpoints/{$checkpoint->id}/" 
                . uniqid() . '.' . $photo->getClientOriginalExtension();
        
        Storage::disk('public')->put($path, file_get_contents($photo->getRealPath()));
        
        return $path;
    }

    private function completeRoute(RouteSession $session): void
    {
        $session->completeSession();

        // Обновляем статистику маршрута
        $session->route->increment('completions_count');

        // Если есть квест, обновляем прогресс
        if ($session->user_quest_id) {
            $userQuest = UserQuest::find($session->user_quest_id);
            if ($userQuest) {
                $progress = $userQuest->progress ?? [];
                $completedRoutes = $progress['completed_routes'] ?? [];
                
                if (!in_array($session->route_id, $completedRoutes)) {
                    $completedRoutes[] = $session->route_id;
                }

                $userQuest->update([
                    'progress' => ['completed_routes' => $completedRoutes],
                    'current_route_index' => count($completedRoutes),
                ]);

                // Проверяем выполнение квеста
                $this->checkQuestCompletion($userQuest);
            }
        }
    }

    private function getPointRequirements($point): array
    {
        // Требования для разных типов точек
        $requirements = [
            'viewpoint' => ['photo' => true, 'time' => 5],
            'cafe' => ['photo' => true, 'checkin' => true],
            'hotel' => ['photo' => true, 'checkin' => true],
            'attraction' => ['photo' => true, 'description' => true],
            'gas_station' => ['photo' => false, 'checkin' => true],
            'camping' => ['photo' => true, 'checkin' => true],
            'photo_spot' => ['photo' => true, 'time' => 3],
            'nature' => ['photo' => true, 'description' => true],
            'historical' => ['photo' => true, 'description' => true],
            'other' => ['photo' => true],
        ];

        return $requirements[$point->type] ?? ['photo' => true];
    }

    private function checkQuestCompletion(UserQuest $userQuest): void
    {
        $quest = $userQuest->quest;
        $requiredRoutes = $quest->requirements['routes_required'] ?? $quest->routes->count();
        $completedRoutes = count($userQuest->progress['completed_routes'] ?? []);

        if ($completedRoutes >= $requiredRoutes) {
            $userQuest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Начисляем награду
            $user = $userQuest->user;
            $user->increment('experience', $quest->reward_xp);
            
            if ($quest->badge) {
                $user->badges()->attach($quest->badge_id);
            }
        }
    }


    public function show(RouteSession $session)
{
    // Проверка прав доступа
    if ($session->user_id !== Auth::id()) {
        abort(403);
    }
    
    $route = $session->route;
    $checkpoints = $session->checkpoints()
        ->with(['point' => function($query) {
            $query->select('id', 'title', 'description', 'type', 'lat', 'lng');
        }])
        ->orderBy('order')
        ->get();
        
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
        // Исправляем запрос - используем userQuests вместо quests
        $activeQuests = Auth::user()->userQuests()
            ->where('status', 'in_progress')
            ->whereHas('quest', function($q) use ($route) {
                $q->whereHas('routes', function($q2) use ($route) {
                    $q2->where('travel_routes.id', $route->id);
                });
            })
            ->with('quest')
            ->get()
            ->pluck('quest');
    }
    
    // Для совместимости со старым кодом
    $sessionQuests = $activeQuests;
    
    return view('routes.navigate', compact(
        'route',
        'session',
        'checkpoints',
        'currentCheckpoint',
        'totalCheckpoints',
        'completedCheckpoints',
        'progressPercentage',
        'activeQuests',
        'sessionQuests' // Добавляем переменную для представления
    ));
}
}