<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Models\UserQuest;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuestService
{
    // Начать квест
    public function startQuest(User $user, Quest $quest)
    {
        // Проверяем, можно ли начать квест
        if (!$this->canStartQuest($user, $quest)) {
            throw new \Exception('Нельзя начать этот квест');
        }
        
        DB::beginTransaction();
        
        try {
            // Создаем запись о начале квеста
            $userQuest = UserQuest::create([
                'user_id' => $user->id,
                'quest_id' => $quest->id,
                'status' => 'in_progress',
                'progress_current' => 0,
                'progress_target' => $quest->tasks()->count(),
                'started_at' => now(),
                'attempts_count' => 0
            ]);
            
            // Добавляем пользователя в чат квеста, если он есть
            if ($quest->chat_id) {
                $chat = Chat::find($quest->chat_id);
                if ($chat && !$chat->users()->where('user_id', $user->id)->exists()) {
                    $chat->users()->attach($user->id, [
                        'joined_at' => now(),
                        'last_read_at' => null
                    ]);
                    
                    // Отправляем приветственное сообщение
                    $chat->messages()->create([
                        'user_id' => $user->id,
                        'content' => "{$user->name} присоединился(ась) к квесту!",
                        'is_system' => true
                    ]);
                }
            } elseif (!$quest->chat_id) {
                // Создаем новый чат для квеста
                $chat = $this->createQuestChat($quest, $user);
                $quest->update(['chat_id' => $chat->id]);
            }
            
            DB::commit();
            
            return $userQuest;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    // Проверить, может ли пользователь начать квест
    public function canStartQuest(User $user, Quest $quest)
    {
        // Проверяем активность квеста
        if (!$quest->isActive()) {
            return false;
        }
        
        // Проверяем, не начал ли уже пользователь этот квест
        $existingQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->whereIn('status', ['in_progress', 'paused'])
            ->exists();
            
        if ($existingQuest) {
            return false;
        }
        
        // Проверяем, не завершил ли пользователь квест (если он не повторяемый)
        if (!$quest->is_repeatable) {
            $completedQuest = UserQuest::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->where('status', 'completed')
                ->exists();
                
            if ($completedQuest) {
                return false;
            }
        }
        
        // Проверяем лимит попыток
        if ($quest->max_completions) {
            $attempts = UserQuest::where('user_id', $user->id)
                ->where('quest_id', $quest->id)
                ->count();
                
            if ($attempts >= $quest->max_completions) {
                return false;
            }
        }
        
        // Проверяем условия квеста (если они есть в JSON поле conditions)
        if ($quest->conditions && !empty($quest->conditions)) {
            foreach ($quest->conditions as $condition) {
                if (!$this->checkCondition($user, $condition)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    // Обновить прогресс квеста
    public function updateQuestProgress(User $user, Quest $quest, $data)
    {
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->whereIn('status', ['in_progress', 'paused'])
            ->first();
            
        if (!$userQuest) {
            return false;
        }
        
        $userQuest->update([
            'progress_current' => $data['progress'] ?? $userQuest->progress_current,
            'completed_data' => array_merge(
                (array) $userQuest->completed_data,
                $data['metadata'] ?? []
            )
        ]);
        
        // Если прогресс достиг цели - завершаем квест
        if ($userQuest->progress_current >= $userQuest->progress_target) {
            $this->completeQuest($user, $quest);
        }
        
        return true;
    }
    
    // Завершить квест
    public function completeQuest(User $user, Quest $quest)
    {
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->whereIn('status', ['in_progress', 'paused'])
            ->first();
            
        if (!$userQuest) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            $userQuest->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            // Начисляем награды
            $user->increment('experience', $quest->reward_exp);
            
            if ($quest->reward_coins > 0) {
                $user->stats()->increment('total_coins', $quest->reward_coins);
            }
            
            // Проверяем выдачу бейджа
            if ($quest->badge_id) {
                $user->badges()->syncWithoutDetaching([$quest->badge_id => [
                    'earned_at' => now(),
                    'metadata' => ['quest_completed' => $quest->title]
                ]]);
            }
            
            // Обновляем статистику
            $user->stats()->increment('quests_completed', 1);
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
    
    // Получить рекомендуемые квесты
    public function getRecommendedQuests(User $user, $limit = 3)
    {
        return Quest::active()
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('quest_id')
                    ->from('user_quests')
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'in_progress']);
            })
            ->where(function($query) use ($user) {
                // Квесты без условий или с условием уровня, которое удовлетворено
                $query->whereNull('conditions')
                      ->orWhere('conditions', '[]')
                      ->orWhereJsonLength('conditions', 0)
                      ->orWhere(function($q) use ($user) {
                          // Для квестов с условиями проверяем, что нет требования по уровню выше текущего
                          $q->whereJsonContains('conditions', ['type' => 'level'])
                            ->where(function($subQ) use ($user) {
                                // Извлекаем значение уровня из JSON
                                $subQ->whereRaw("JSON_EXTRACT(conditions, '$[0].value') <= ?", [$user->level])
                                     ->orWhereRaw("JSON_EXTRACT(conditions, '$[*].value') <= ?", [$user->level]);
                            });
                      });
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
    
    // Получить статистику квеста
    public function getQuestStatistics(Quest $quest)
    {
        $totalAttempts = $quest->users()->count();
        $completed = $quest->users()->wherePivot('status', 'completed')->count();
        $successRate = $totalAttempts > 0 ? round(($completed / $totalAttempts) * 100) : 0;
        
        // Среднее время выполнения
        $avgTime = $quest->users()
            ->wherePivot('status', 'completed')
            ->wherePivot('started_at', '!=', null)
            ->wherePivot('completed_at', '!=', null)
            ->get()
            ->map(function($user) {
                $start = Carbon::parse($user->pivot->started_at);
                $end = Carbon::parse($user->pivot->completed_at);
                return $start->diffInHours($end);
            })
            ->avg();
        
        return [
            'attempts' => $totalAttempts,
            'completed' => $completed,
            'success_rate' => $successRate,
            'avg_completion_time' => $avgTime ? round($avgTime, 1) . ' ч' : 'Нет данных',
        ];
    }
    
    // Приватные методы
    private function checkCondition(User $user, $condition)
    {
        if (!is_array($condition)) {
            return true;
        }
        
        $type = $condition['type'] ?? null;
        $value = $condition['value'] ?? null;
        
        switch ($type) {
            case 'level':
                return $user->level >= ($value ?? 1);
                
            case 'quest_completed':
                $questId = $condition['quest_id'] ?? $value ?? 0;
                return $user->userQuests()
                    ->where('quest_id', $questId)
                    ->where('status', 'completed')
                    ->exists();
                
            case 'route_completed':
                $routeId = $condition['route_id'] ?? $value ?? 0;
                return $user->routeCompletions()
                    ->where('route_id', $routeId)
                    ->exists();
                
            case 'badge_earned':
                $badgeId = $condition['badge_id'] ?? $value ?? 0;
                return $user->badges()
                    ->where('badge_id', $badgeId)
                    ->exists();
                
            default:
                return true;
        }
    }
    
    private function createQuestChat(Quest $quest, User $creator)
    {
        $chat = Chat::create([
            'name' => "Квест: {$quest->title}",
            'type' => 'group'
        ]);
        
        // Добавляем создателя
        $chat->users()->attach($creator->id, [
            'joined_at' => now(),
            'last_read_at' => now()
        ]);
        
        // Отправляем системное сообщение
        $chat->messages()->create([
            'user_id' => $creator->id,
            'content' => "Чат создан для квеста '{$quest->title}'. Обсуждайте задания, делитесь подсказками!",
            'is_system' => true
        ]);
        
        return $chat;
    }
}