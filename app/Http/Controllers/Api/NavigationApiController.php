<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RouteSession;
use App\Models\RouteCheckpoint;
use App\Models\QuestTask;
use App\Models\Quest;
use App\Models\QuestTaskProgress;
use App\Models\CheckpointPhoto;
use App\Models\CheckpointComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NavigationApiController extends Controller
{
    /**
     * Отметить прибытие на чекпоинт
     */
    public function arrive(Request $request, $checkpointId)
    {
        $user = Auth::user();
        $checkpoint = RouteCheckpoint::findOrFail($checkpointId);
        
        // Проверяем, что чекпоинт принадлежит активной сессии пользователя
        $session = RouteSession::where('user_id', $user->id)
            ->where('id', $checkpoint->session_id ?? null)
            ->whereIn('status', ['active', 'paused'])
            ->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Активная сессия не найдена'
            ], 404);
        }
        
        // Валидация
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:5120', // 5MB
            'rating' => 'nullable|integer|min:1|max:5'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Обновляем чекпоинт
            $checkpoint->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge(
                    $checkpoint->metadata ?? [],
                    ['rating' => $request->rating]
                )
            ]);
            
            // Обновляем список посещенных чекпоинтов в сессии
            $visited = $session->checkpoints_visited ?? [];
            if (!in_array($checkpoint->id, $visited)) {
                $visited[] = $checkpoint->id;
                $session->update(['checkpoints_visited' => $visited]);
            }
            
            // Сохраняем комментарий
            if ($request->has('comment') && $request->comment) {
                CheckpointComment::create([
                    'checkpoint_id' => $checkpoint->id,
                    'user_id' => $user->id,
                    'content' => $request->comment,
                    'is_system' => false
                ]);
            }
            
            // Сохраняем фото
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('checkpoint-photos/' . date('Y/m'), 'public');
                CheckpointPhoto::create([
                    'checkpoint_id' => $checkpoint->id,
                    'path' => $path,
                    'caption' => $request->comment,
                    'uploaded_by' => $user->id
                ]);
                $photoPath = Storage::url($path);
            }
            
            // Находим следующий чекпоинт
            $nextCheckpoint = RouteCheckpoint::where('route_id', $checkpoint->route_id)
                ->where('order', '>', $checkpoint->order)
                ->orderBy('order')
                ->first();
            
            if ($nextCheckpoint) {
                $session->update(['current_checkpoint_id' => $nextCheckpoint->id]);
            } else {
                // Это последний чекпоинт
                $session->update(['current_checkpoint_id' => null]);
            }
            
            // Обновляем статистику сессии
            $this->updateSessionStats($session);
            
            // Начисляем XP
            $xpEarned = 10; // Базовые XP за точку
            $user->increment('exp', $xpEarned);
            $session->increment('earned_xp', $xpEarned);
            
            // Проверяем квесты
            $completedQuests = $this->checkQuests($session, $checkpoint);
            
            return response()->json([
                'success' => true,
                'message' => 'Точка успешно отмечена',
                'data' => [
                    'next_checkpoint' => $nextCheckpoint,
                    'xp_earned' => $xpEarned,
                    'photo_url' => $photoPath,
                    'completed_quests' => $completedQuests,
                    'progress' => [
                        'completed' => count($visited),
                        'total' => $checkpoint->route->checkpoints()->count(),
                        'percentage' => round((count($visited) / max(1, $checkpoint->route->checkpoints()->count())) * 100)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Пропустить чекпоинт
     */
    public function skip(Request $request, $checkpointId)
    {
        $user = Auth::user();
        $checkpoint = RouteCheckpoint::findOrFail($checkpointId);
        
        $session = RouteSession::where('user_id', $user->id)
            ->where('id', $checkpoint->session_id ?? null)
            ->whereIn('status', ['active', 'paused'])
            ->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Активная сессия не найдена'
            ], 404);
        }
        
        try {
            $checkpoint->update([
                'status' => 'skipped',
                'skipped_at' => now()
            ]);
            
            // Находим следующий чекпоинт
            $nextCheckpoint = RouteCheckpoint::where('route_id', $checkpoint->route_id)
                ->where('order', '>', $checkpoint->order)
                ->orderBy('order')
                ->first();
            
            if ($nextCheckpoint) {
                $session->update(['current_checkpoint_id' => $nextCheckpoint->id]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Точка пропущена',
                'data' => [
                    'next_checkpoint' => $nextCheckpoint
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Информация о чекпоинте
     */
    public function info($checkpointId)
    {
        $checkpoint = RouteCheckpoint::with(['route', 'photos', 'comments.user'])->findOrFail($checkpointId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'checkpoint' => [
                    'id' => $checkpoint->id,
                    'title' => $checkpoint->title,
                    'description' => $checkpoint->description,
                    'type' => $checkpoint->type,
                    'type_label' => $this->getTypeLabel($checkpoint->type),
                    'latitude' => $checkpoint->latitude,
                    'longitude' => $checkpoint->longitude,
                    'order' => $checkpoint->order,
                    'distance_to_next' => $checkpoint->distance_to_next,
                    'photos' => $checkpoint->photos->map(function($photo) {
                        return [
                            'id' => $photo->id,
                            'url' => Storage::url($photo->path),
                            'caption' => $photo->caption,
                            'uploaded_at' => $photo->created_at->format('d.m.Y H:i')
                        ];
                    }),
                    'comments' => $checkpoint->comments->map(function($comment) {
                        return [
                            'id' => $comment->id,
                            'content' => $comment->content,
                            'user' => $comment->user->name,
                            'avatar' => $comment->user->avatar ? Storage::url($comment->user->avatar) : null,
                            'created_at' => $comment->created_at->format('d.m.Y H:i'),
                            'is_system' => $comment->is_system
                        ];
                    })
                ],
                'route' => [
                    'id' => $checkpoint->route->id,
                    'title' => $checkpoint->route->title,
                    'length_km' => $checkpoint->route->length_km
                ]
            ]
        ]);
    }
    
    /**
     * Выполнить задание
     */
    public function completeTask(Request $request, $taskId)
    {
        $user = Auth::user();
        $task = QuestTask::with(['quest'])->findOrFail($taskId);
        
        // Проверяем, доступно ли задание
        $userQuest = $user->userQuests()
            ->where('quest_id', $task->quest_id)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$userQuest) {
            return response()->json([
                'success' => false,
                'message' => 'Задание не доступно или квест не активен'
            ], 403);
        }
        
        // Проверяем, не выполнено ли уже задание
        $existingProgress = QuestTaskProgress::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('status', 'completed')
            ->first();
        
        if ($existingProgress) {
            return response()->json([
                'success' => false,
                'message' => 'Задание уже выполнено'
            ], 400);
        }
        
        try {
            // Создаем запись о прогрессе
            $taskProgress = QuestTaskProgress::create([
                'user_id' => $user->id,
                'quest_id' => $task->quest_id,
                'task_id' => $task->id,
                'status' => 'completed',
                'completed_at' => now(),
                'points_earned' => $task->points,
                'user_answer' => $request->input('answer'),
                'is_correct' => true, // Пока всегда true, можно добавить проверку
                'metadata' => [
                    'completed_via' => 'mobile',
                    'location' => $request->input('location')
                ]
            ]);
            
            // Обновляем прогресс квеста
            $completedTasks = QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $task->quest_id)
                ->where('status', 'completed')
                ->count();
            
            $totalTasks = $task->quest->tasks()->count();
            $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            
            $userQuest->update([
                'progress_current' => $completedTasks,
                'progress_target' => $totalTasks
            ]);
            
            // Начисляем XP за задание
            $xpEarned = $task->points * 2; // Например, 2 XP за каждый балл задания
            $user->increment('exp', $xpEarned);
            
            // Проверяем, выполнен ли весь квест
            if ($completedTasks >= $totalTasks) {
                $this->completeQuest($userQuest);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Задание выполнено!',
                'data' => [
                    'task' => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'type' => $task->type,
                        'points' => $task->points,
                        'xp_earned' => $xpEarned
                    ],
                    'progress' => [
                        'completed' => $completedTasks,
                        'total' => $totalTasks,
                        'percentage' => $progressPercentage
                    ],
                    'quest' => [
                        'id' => $task->quest->id,
                        'title' => $task->quest->title,
                        'reward_exp' => $task->quest->reward_exp
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выполнении задания: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Информация о задании
     */
    public function taskInfo($taskId)
    {
        $task = QuestTask::with(['quest', 'location'])->findOrFail($taskId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'type' => $task->type,
                    'type_label' => $this->getTaskTypeLabel($task->type),
                    'content' => $task->content,
                    'points' => $task->points,
                    'time_limit_minutes' => $task->time_limit_minutes,
                    'required_answer' => $task->required_answer,
                    'location' => $task->location ? [
                        'id' => $task->location->id,
                        'title' => $task->location->title,
                        'type' => $task->location->type
                    ] : null,
                    'quest' => [
                        'id' => $task->quest->id,
                        'title' => $task->quest->title,
                        'difficulty' => $task->quest->difficulty
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * Статистика сессии
     */
    public function stats($sessionId)
    {
        $user = Auth::user();
        $session = RouteSession::with(['route'])->findOrFail($sessionId);
        
        if ($session->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        // Вычисляем пройденное расстояние (упрощенно)
        $distance = $session->distance_traveled ?? 0;
        
        // Вычисляем время сессии
        $startTime = $session->started_at;
        $pauseTime = $session->paused_at;
        $duration = now()->diffInSeconds($startTime);
        
        if ($pauseTime) {
            $duration -= now()->diffInSeconds($pauseTime);
        }
        
        // Обновляем сессию
        $session->update([
            'duration_seconds' => $duration,
            'distance_traveled' => $distance + (rand(0, 100) / 1000) // Имитация движения
        ]);
        
        // Получаем прогресс
        $visitedCount = count($session->checkpoints_visited ?? []);
        $totalCheckpoints = $session->route->checkpoints()->count();
        $progressPercentage = $totalCheckpoints > 0 ? round(($visitedCount / $totalCheckpoints) * 100) : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'distance' => number_format($session->distance_traveled, 2),
                    'duration' => gmdate('H:i:s', $duration),
                    'checkpoints_completed' => $visitedCount,
                    'total_checkpoints' => $totalCheckpoints,
                    'progress_percentage' => $progressPercentage,
                    'average_speed' => $session->average_speed ?? 0,
                    'earned_xp' => $session->earned_xp ?? 0
                ],
                'session' => [
                    'id' => $session->id,
                    'status' => $session->status,
                    'started_at' => $session->started_at->format('d.m.Y H:i'),
                    'current_checkpoint' => $session->current_checkpoint_id
                ]
            ]
        ]);
    }
    
    /**
     * Обновить позицию
     */
    public function updatePosition(Request $request, $sessionId)
    {
        $user = Auth::user();
        $session = RouteSession::findOrFail($sessionId);
        
        if ($session->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $session->update([
                'current_position' => [
                    'lat' => $request->latitude,
                    'lng' => $request->longitude,
                    'accuracy' => $request->accuracy,
                    'speed' => $request->speed,
                    'heading' => $request->heading,
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
            // Проверяем близость к текущему чекпоинту
            $nearCheckpoint = false;
            $currentCheckpoint = $session->currentCheckpoint;
            
            if ($currentCheckpoint && $currentCheckpoint->status !== 'completed') {
                $distance = $this->calculateDistance(
                    $request->latitude,
                    $request->longitude,
                    $currentCheckpoint->latitude,
                    $currentCheckpoint->longitude
                );
                
                if ($distance < 0.05) { // 50 метров
                    $nearCheckpoint = true;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'position_updated' => true,
                    'near_checkpoint' => $nearCheckpoint,
                    'distance_to_checkpoint' => $distance ?? null,
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении позиции: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Сделать фото
     */
    public function takePhoto(Request $request, $sessionId)
    {
        $user = Auth::user();
        $session = RouteSession::findOrFail($sessionId);
        
        if ($session->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|max:10240', // 10MB
            'caption' => 'nullable|string|max:255',
            'checkpoint_id' => 'nullable|exists:route_checkpoints,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $path = $request->file('photo')->store('session-photos/' . date('Y/m'), 'public');
            
            // Сохраняем фото
            $photo = CheckpointPhoto::create([
                'checkpoint_id' => $request->checkpoint_id,
                'path' => $path,
                'caption' => $request->caption,
                'uploaded_by' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Фото сохранено',
                'data' => [
                    'photo' => [
                        'id' => $photo->id,
                        'url' => Storage::url($path),
                        'caption' => $photo->caption,
                        'created_at' => $photo->created_at->format('d.m.Y H:i')
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении фото: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Прогресс квеста
     */
    public function questProgress($questId)
    {
        $user = Auth::user();
        $quest = Quest::with(['tasks'])->findOrFail($questId);
        
        $userQuest = $user->userQuests()
            ->where('quest_id', $questId)
            ->first();
        
        if (!$userQuest) {
            return response()->json([
                'success' => false,
                'message' => 'Квест не найден'
            ], 404);
        }
        
        $completedTasks = QuestTaskProgress::where('user_id', $user->id)
            ->where('quest_id', $questId)
            ->where('status', 'completed')
            ->count();
        
        $totalTasks = $quest->tasks->count();
        $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'quest' => [
                    'id' => $quest->id,
                    'title' => $quest->title,
                    'description' => $quest->description,
                    'difficulty' => $quest->difficulty,
                    'reward_exp' => $quest->reward_exp,
                    'reward_coins' => $quest->reward_coins
                ],
                'progress' => [
                    'completed' => $completedTasks,
                    'total' => $totalTasks,
                    'percentage' => $progressPercentage,
                    'status' => $userQuest->status
                ],
                'tasks' => $quest->tasks->map(function($task) use ($user) {
                    $progress = QuestTaskProgress::where('user_id', $user->id)
                        ->where('task_id', $task->id)
                        ->first();
                    
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'type' => $task->type,
                        'points' => $task->points,
                        'is_completed' => $progress && $progress->status === 'completed',
                        'completed_at' => $progress ? $progress->completed_at : null
                    ];
                })
            ]
        ]);
    }
    
    /**
     * Начать квест
     */
    public function startQuest(Request $request, $questId)
    {
        $user = Auth::user();
        $quest = Quest::findOrFail($questId);
        
        // Проверяем, не начат ли уже квест
        $existingQuest = $user->userQuests()
            ->where('quest_id', $questId)
            ->whereIn('status', ['available', 'in_progress'])
            ->first();
        
        if ($existingQuest) {
            return response()->json([
                'success' => false,
                'message' => 'Квест уже начат'
            ], 400);
        }
        
        try {
            $userQuest = $user->userQuests()->create([
                'quest_id' => $questId,
                'status' => 'in_progress',
                'progress_current' => 0,
                'progress_target' => $quest->tasks()->count(),
                'started_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Квест начат!',
                'data' => [
                    'quest' => [
                        'id' => $quest->id,
                        'title' => $quest->title
                    ],
                    'user_quest' => [
                        'id' => $userQuest->id,
                        'status' => $userQuest->status,
                        'started_at' => $userQuest->started_at->format('d.m.Y H:i')
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при начале квеста: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Обновить статистику сессии
     */
    private function updateSessionStats($session)
    {
        $visitedCount = count($session->checkpoints_visited ?? []);
        $totalCheckpoints = $session->route->checkpoints()->count();
        
        if ($totalCheckpoints > 0) {
            $progress = ($visitedCount / $totalCheckpoints) * 100;
            $session->update(['progress_percentage' => $progress]);
        }
    }
    
    /**
     * Проверить квесты при прибытии на точку
     */
    private function checkQuests($session, $checkpoint)
    {
        $user = Auth::user();
        $completedQuests = [];
        
        // Получаем активные квесты пользователя
        $activeQuests = $user->userQuests()
            ->where('status', 'in_progress')
            ->with(['quest.tasks'])
            ->get();
        
        foreach ($activeQuests as $userQuest) {
            $quest = $userQuest->quest;
            
            // Проверяем задания, привязанные к этой точке
            foreach ($quest->tasks as $task) {
                if ($task->location_id == $checkpoint->id) {
                    // Автоматически выполняем задание
                    QuestTaskProgress::create([
                        'user_id' => $user->id,
                        'quest_id' => $quest->id,
                        'task_id' => $task->id,
                        'status' => 'completed',
                        'completed_at' => now(),
                        'points_earned' => $task->points
                    ]);
                    
                    // Проверяем, выполнен ли весь квест
                    $completedTasks = QuestTaskProgress::where('user_id', $user->id)
                        ->where('quest_id', $quest->id)
                        ->where('status', 'completed')
                        ->count();
                    
                    if ($completedTasks >= $quest->tasks()->count()) {
                        $this->completeQuest($userQuest);
                        $completedQuests[] = $quest->title;
                    }
                }
            }
        }
        
        return $completedQuests;
    }
    
    /**
     * Завершить квест
     */
    private function completeQuest($userQuest)
    {
        $userQuest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_current' => $userQuest->progress_target
        ]);
        
        // Награждаем пользователя
        $user = $userQuest->user;
        $quest = $userQuest->quest;
        
        if ($quest->reward_exp > 0) {
            $user->increment('exp', $quest->reward_exp);
            
            // Обновляем статистику
            $userStats = $user->stats ?? $user->stats()->create(['total_exp' => 0]);
            $userStats->increment('total_exp', $quest->reward_exp);
            $userStats->increment('quests_completed');
        }
        
        if ($quest->reward_coins > 0) {
            $user->increment('coins', $quest->reward_coins);
            
            if ($userStats) {
                $userStats->increment('total_coins', $quest->reward_coins);
            }
        }
        
        // Выдаем значок если есть
        if ($quest->badge_id) {
            $user->badges()->attach($quest->badge_id, [
                'earned_at' => now(),
                'metadata' => [
                    'quest_id' => $quest->id,
                    'quest_title' => $quest->title,
                    'completed_at' => now()
                ]
            ]);
        }
    }
    
    /**
     * Вычислить расстояние между двумя точками
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // км
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Метка для типа точки
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'checkpoint' => 'Контрольная точка',
            'viewpoint' => 'Смотровая площадка',
            'cafe' => 'Кафе',
            'hotel' => 'Отель',
            'attraction' => 'Достопримечательность',
            'gas_station' => 'Заправка',
            'camping' => 'Кемпинг',
            'photo_spot' => 'Фото-точка',
            'nature' => 'Природа',
            'historical' => 'Историческое место',
            'other' => 'Другое'
        ];
        
        return $labels[$type] ?? 'Точка';
    }
    
    /**
     * Метка для типа задания
     */
    private function getTaskTypeLabel($type)
    {
        $labels = [
            'text' => 'Текстовое задание',
            'image' => 'Фотография',
            'code' => 'Код',
            'cipher' => 'Шифр',
            'location' => 'Локация',
            'puzzle' => 'Головоломка',
            'quiz' => 'Викторина'
        ];
        
        return $labels[$type] ?? 'Задание';
    }
}