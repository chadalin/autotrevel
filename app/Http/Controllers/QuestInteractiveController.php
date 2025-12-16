<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\QuestTask;
use App\Models\QuestTaskProgress;
use App\Models\UserQuest;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuestInteractiveController extends Controller
{
    // Показать текущее задание квеста
    public function showCurrentTask($questSlug)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        
        // Проверяем, участвует ли пользователь в квесте
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->whereIn('status', ['in_progress', 'paused'])
            ->first();
        
        if (!$userQuest) {
            return redirect()->route('quests.show', $questSlug)
                ->with('error', 'Вы не участвуете в этом квесте');
        }
        
        // Получаем текущее задание
        $currentTaskProgress = QuestTaskProgress::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->where('status', 'in_progress')
            ->with('task')
            ->first();
        
        if (!$currentTaskProgress) {
            // Начинаем первое задание
            $firstTask = QuestTask::where('quest_id', $quest->id)
                ->orderBy('order')
                ->first();
                
            if (!$firstTask) {
                return redirect()->route('quests.show', $questSlug)
                    ->with('error', 'В квесте нет заданий');
            }
            
            $currentTaskProgress = QuestTaskProgress::create([
                'user_id' => $user->id,
                'quest_id' => $quest->id,
                'task_id' => $firstTask->id,
                'status' => 'in_progress',
                'started_at' => now(),
                'attempts' => 0
            ]);
        }
        
        $task = $currentTaskProgress->task;
        $formattedContent = $task->getFormattedContent();
        $hints = $task->getHints();
        $timeRemaining = $currentTaskProgress->getTimeRemaining();
        
        // Получаем сообщения из чата квеста
        $chatMessages = [];
        if ($quest->chat_id) {
            $chat = Chat::find($quest->chat_id);
            if ($chat && $chat->users()->where('user_id', $user->id)->exists()) {
                $chatMessages = $chat->messages()
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->reverse();
            }
        }
        
        // Статистика квеста
        $completedTasks = QuestTaskProgress::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->where('status', 'completed')
            ->where('is_correct', true)
            ->count();
            
        $totalTasks = QuestTask::where('quest_id', $quest->id)->count();
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        return view('quests.interactive.task', compact(
            'quest',
            'task',
            'currentTaskProgress',
            'formattedContent',
            'hints',
            'timeRemaining',
            'chatMessages',
            'completedTasks',
            'totalTasks',
            'progress'
        ));
    }
    
    // Отправить ответ на задание
    public function submitAnswer(Request $request, $questSlug, $taskId)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        $task = QuestTask::where('id', $taskId)->where('quest_id', $quest->id)->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            $taskProgress = QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->where('task_id', $task->id)
                ->where('status', 'in_progress')
                ->first();
            
            if (!$taskProgress) {
                throw new \Exception('Задание не активно');
            }
            
            // Проверяем, не истекло ли время
            if ($taskProgress->isTimeExpired()) {
                // Автопереход с потерей баллов
                $taskProgress->complete(false, 'Время истекло');
                return $this->handleTaskCompletion($user, $quest, $task, false, true);
            }
            
            $userAnswer = $request->input('answer');
            $isCorrect = $task->checkAnswer($userAnswer);
            
            // Обновляем прогресс
            $taskProgress->complete($isCorrect, $userAnswer);
            $taskProgress->increment('attempts');
            
            // Обрабатываем завершение задания
            $result = $this->handleTaskCompletion($user, $quest, $task, $isCorrect, false);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'is_correct' => $isCorrect,
                'next_task' => $result['next_task'] ?? null,
                'points' => $taskProgress->points_earned,
                'completed_tasks' => $result['completed_tasks'],
                'total_tasks' => $result['total_tasks'],
                'message' => $isCorrect ? 'Правильно! Задание выполнено!' : 'Неправильный ответ. Попробуйте ещё раз.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    // Получить подсказку
    public function getHint(Request $request, $questSlug, $taskId)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        $task = QuestTask::where('id', $taskId)->where('quest_id', $quest->id)->firstOrFail();
        
        $taskProgress = QuestTaskProgress::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->where('task_id', $task->id)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$taskProgress) {
            return response()->json([
                'success' => false,
                'message' => 'Задание не активно'
            ], 400);
        }
        
        $hintIndex = $request->input('hint_index', 0);
        $hints = $task->getHints();
        
        if (!isset($hints[$hintIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Подсказка не найдена'
            ], 404);
        }
        
        $hint = $hints[$hintIndex];
        
        // Проверяем, доступна ли подсказка по времени
        $minutesElapsed = $taskProgress->started_at ? 
            now()->diffInMinutes($taskProgress->started_at) : 0;
            
        if (isset($hint['available_after_minutes']) && 
            $minutesElapsed < $hint['available_after_minutes']) {
            $minutesLeft = $hint['available_after_minutes'] - $minutesElapsed;
            return response()->json([
                'success' => false,
                'message' => "Подсказка будет доступна через {$minutesLeft} минут"
            ], 400);
        }
        
        // Используем подсказку
        $taskProgress->useHint($hintIndex);
        
        return response()->json([
            'success' => true,
            'hint' => $hint,
            'penalty_points' => $taskProgress->penalty_points,
            'hints_used' => $taskProgress->hints_used
        ]);
    }
    
    // Проверить местоположение (для заданий типа location)
    public function checkLocation(Request $request, $questSlug, $taskId)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        $task = QuestTask::where('id', $taskId)->where('quest_id', $quest->id)->firstOrFail();
        
        if ($task->type !== 'location') {
            return response()->json([
                'success' => false,
                'message' => 'Это задание не требует проверки местоположения'
            ], 400);
        }
        
        $coordinates = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);
        
        $isCorrect = $task->checkAnswer($coordinates);
        
        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'distance' => $isCorrect ? 0 : $this->calculateDistance(
                $task->content['coordinates']['lat'],
                $task->content['coordinates']['lng'],
                $coordinates['lat'],
                $coordinates['lng']
            )
        ]);
    }
    
    // Пауза квеста
    public function pauseQuest($questSlug)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->where('status', 'in_progress')
            ->first();
        
        if ($userQuest) {
            $userQuest->update(['status' => 'paused']);
            
            // Также ставим на паузу текущее задание
            QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->where('status', 'in_progress')
                ->update(['status' => 'paused']);
        }
        
        return redirect()->route('quests.show', $questSlug)
            ->with('info', 'Квест приостановлен');
    }
    
    // Возобновить квест
    public function resumeQuest($questSlug)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->where('status', 'paused')
            ->first();
        
        if ($userQuest) {
            $userQuest->update(['status' => 'in_progress']);
            
            // Возобновляем задание
            QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->where('status', 'paused')
                ->update(['status' => 'in_progress']);
        }
        
        return redirect()->route('quests.interactive.task', $questSlug)
            ->with('success', 'Квест возобновлён');
    }
    
    // Закончить квест досрочно
    public function completeQuest($questSlug)
    {
        $user = Auth::user();
        $quest = Quest::where('slug', $questSlug)->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            $userQuest = UserQuest::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->whereIn('status', ['in_progress', 'paused'])
                ->firstOrFail();
            
            // Помечаем все активные задания как пропущенные
            QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->whereIn('status', ['in_progress', 'paused'])
                ->update([
                    'status' => 'skipped',
                    'completed_at' => now(),
                    'points_earned' => 0
                ]);
            
            // Завершаем квест
            $totalPoints = QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->sum('points_earned');
            
            $userQuest->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress_current' => QuestTask::where('quest_id', $quest->id)->count(),
                'progress_target' => QuestTask::where('quest_id', $quest->id)->count(),
                'completed_data' => [
                    'total_points' => $totalPoints,
                    'tasks_completed' => QuestTaskProgress::where('user_id', $user->id)
                        ->where('quest_id', $quest->id)
                        ->where('is_correct', true)
                        ->count(),
                    'early_completion' => true
                ]
            ]);
            
            // Начисляем опыт
            $user->increment('experience', $totalPoints);
            $user->stats()->increment('total_exp', $totalPoints);
            
            // Проверяем выдачу бейджа
            if ($quest->badge_id && $totalPoints > 0) {
                $user->badges()->syncWithoutDetaching([$quest->badge_id => [
                    'earned_at' => now(),
                    'metadata' => ['points_earned' => $totalPoints]
                ]]);
            }
            
            DB::commit();
            
            return redirect()->route('quests.show', $questSlug)
                ->with('success', 'Квест завершён досрочно. Вы заработали ' . $totalPoints . ' очков.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    // Чат квеста
    public function questChat($questSlug)
    {
        $user = Auth::user();
        $quest = Quest::with('chat.users')->where('slug', $questSlug)->firstOrFail();
        
        // Проверяем, участвует ли пользователь в квесте
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->exists();
        
        if (!$userQuest) {
            abort(403, 'Вы не участвуете в этом квесте');
        }
        
        if (!$quest->chat) {
            // Создаем чат для квеста, если его нет
            $chat = Chat::create([
                'name' => $quest->title,
                'type' => 'group'
            ]);
            
            // Добавляем всех участников квеста в чат
            $participants = UserQuest::where('quest_id', $quest->id)
                ->pluck('user_id')
                ->toArray();
                
            $chat->users()->attach($participants);
            
            $quest->update(['chat_id' => $chat->id]);
            $quest->refresh();
        }
        
        $messages = $quest->chat->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('quests.interactive.chat', compact('quest', 'messages'));
    }
    
    // Приватные методы
    private function handleTaskCompletion($user, $quest, $task, $isCorrect, $timeExpired)
    {
        $nextTask = null;
        
        if ($isCorrect || $timeExpired) {
            // Находим следующее задание
            $nextTask = QuestTask::where('quest_id', $quest->id)
                ->where('order', '>', $task->order)
                ->orderBy('order')
                ->first();
            
            if ($nextTask) {
                // Создаем прогресс для следующего задания
                QuestTaskProgress::create([
                    'user_id' => $user->id,
                    'quest_id' => $quest->id,
                    'task_id' => $nextTask->id,
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'attempts' => 0
                ]);
            } else {
                // Это было последнее задание - завершаем квест
                $this->completeQuestAutomatically($user, $quest);
            }
        }
        
        $completedTasks = QuestTaskProgress::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->where('status', 'completed')
            ->where('is_correct', true)
            ->count();
            
        $totalTasks = QuestTask::where('quest_id', $quest->id)->count();
        
        // Обновляем прогресс в UserQuest
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->first();
            
        if ($userQuest) {
            $userQuest->update([
                'progress_current' => $completedTasks,
                'progress_target' => $totalTasks
            ]);
        }
        
        return [
            'next_task' => $nextTask,
            'completed_tasks' => $completedTasks,
            'total_tasks' => $totalTasks
        ];
    }
    
    private function completeQuestAutomatically($user, $quest)
    {
        DB::beginTransaction();
        
        try {
            $userQuest = UserQuest::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->first();
            
            if ($userQuest) {
                $totalPoints = QuestTaskProgress::where('user_id', $user->id)
                    ->where('quest_id', $quest->id)
                    ->sum('points_earned');
                
                $tasksCompleted = QuestTaskProgress::where('user_id', $user->id)
                    ->where('quest_id', $quest->id)
                    ->where('is_correct', true)
                    ->count();
                
                $userQuest->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'progress_current' => $tasksCompleted,
                    'progress_target' => QuestTask::where('quest_id', $quest->id)->count(),
                    'completed_data' => [
                        'total_points' => $totalPoints,
                        'tasks_completed' => $tasksCompleted,
                        'completion_time' => now()->diffInMinutes($userQuest->started_at)
                    ]
                ]);
                
                // Начисляем опыт
                $user->increment('experience', $totalPoints + $quest->reward_exp);
                $user->stats()->increment('total_exp', $totalPoints + $quest->reward_exp);
                $user->stats()->increment('quests_completed', 1);
                
                // Начисляем монеты
                if ($quest->reward_coins > 0) {
                    $user->stats()->increment('total_coins', $quest->reward_coins);
                }
                
                // Проверяем выдачу бейджа
                if ($quest->badge_id) {
                    $user->badges()->syncWithoutDetaching([$quest->badge_id => [
                        'earned_at' => now(),
                        'metadata' => [
                            'points_earned' => $totalPoints,
                            'quest_completed' => $quest->title
                        ]
                    ]]);
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));
        
        return round($angle * $earthRadius); // в метрах
    }
}