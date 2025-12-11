<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStats;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    private $achievements = [
        // Квестовые достижения
        'first_quest' => [
            'title' => 'Первые шаги',
            'description' => 'Завершите первый квест',
            'icon' => 'fas fa-flag',
            'color' => '#10B981',
            'condition' => 'quests_completed >= 1',
        ],
        'quest_master' => [
            'title' => 'Мастер квестов',
            'description' => 'Завершите 10 квестов',
            'icon' => 'fas fa-crown',
            'color' => '#F59E0B',
            'condition' => 'quests_completed >= 10',
        ],
        'weekend_warrior' => [
            'title' => 'Воин выходного дня',
            'description' => 'Завершите 5 квестов выходного дня',
            'icon' => 'fas fa-campground',
            'color' => '#3B82F6',
            'condition' => 'weekend_quests_completed >= 5',
        ],
        
        // Маршрутные достижения
        'road_explorer' => [
            'title' => 'Исследователь дорог',
            'description' => 'Проедите 1000 км',
            'icon' => 'fas fa-road',
            'color' => '#6366F1',
            'condition' => 'distance_traveled >= 1000',
        ],
        'mountain_conqueror' => [
            'title' => 'Покоритель гор',
            'description' => 'Завершите 5 горных маршрутов',
            'icon' => 'fas fa-mountain',
            'color' => '#EF4444',
            'condition' => 'mountain_routes_completed >= 5',
        ],
        
        // Социальные достижения
        'popular_author' => [
            'title' => 'Популярный автор',
            'description' => 'Ваш маршрут добавлен в избранное 10 раз',
            'icon' => 'fas fa-users',
            'color' => '#EC4899',
            'condition' => 'route_favorites >= 10',
        ],
        
        // Уровневые достижения
        'level_10' => [
            'title' => 'Опытный путешественник',
            'description' => 'Достигните 10 уровня',
            'icon' => 'fas fa-star',
            'color' => '#F59E0B',
            'condition' => 'level >= 10',
        ],
        'level_25' => [
            'title' => 'Ветеран дорог',
            'description' => 'Достигните 25 уровня',
            'icon' => 'fas fa-trophy',
            'color' => '#8B5CF6',
            'condition' => 'level >= 25',
        ],
    ];

    // Проверить и выдать достижения
    public function checkAchievements(User $user)
    {
        $stats = $user->stats;
        $achievements = $stats->achievements ?? [];
        $newAchievements = [];

        foreach ($this->achievements as $key => $achievement) {
            // Если достижение уже получено, пропускаем
            if (in_array($key, $achievements)) {
                continue;
            }

            // Проверяем условие
            if ($this->checkCondition($achievement['condition'], $user, $stats)) {
                $achievements[] = $key;
                $newAchievements[] = [
                    'key' => $key,
                    'title' => $achievement['title'],
                    'description' => $achievement['description'],
                    'icon' => $achievement['icon'],
                    'color' => $achievement['color'],
                ];
            }
        }

        // Сохраняем обновлённый список достижений
        if (!empty($newAchievements)) {
            $stats->update(['achievements' => $achievements]);
            
            // Отправляем уведомления о новых достижениях
            foreach ($newAchievements as $achievement) {
                $this->notifyAboutAchievement($user, $achievement);
            }
        }

        return $newAchievements;
    }

    // Проверить условие достижения
    private function checkCondition($condition, User $user, UserStats $stats)
    {
        // Парсим условие
        if (preg_match('/(\w+)\s*(>=|<=|==|>|<)\s*(\d+)/', $condition, $matches)) {
            $field = $matches[1];
            $operator = $matches[2];
            $value = (int) $matches[3];

            $actualValue = $this->getFieldValue($field, $user, $stats);

            switch ($operator) {
                case '>=':
                    return $actualValue >= $value;
                case '<=':
                    return $actualValue <= $value;
                case '==':
                    return $actualValue == $value;
                case '>':
                    return $actualValue > $value;
                case '<':
                    return $actualValue < $value;
            }
        }

        // Специальные условия
        switch ($condition) {
            case 'weekend_quests_completed >= 5':
                $count = $user->userQuests()
                    ->whereHas('quest', function ($query) {
                        $query->where('type', 'weekend');
                    })
                    ->where('status', 'completed')
                    ->count();
                return $count >= 5;
                
            case 'mountain_routes_completed >= 5':
                $count = $user->userQuests()
                    ->whereHas('quest', function ($query) {
                        $query->whereHas('routes', function ($q) {
                            $q->whereHas('tags', function ($t) {
                                $t->where('name', 'горы');
                            });
                        });
                    })
                    ->where('status', 'completed')
                    ->count();
                return $count >= 5;
                
            case 'route_favorites >= 10':
                $count = $user->routes()->sum('favorites_count');
                return $count >= 10;
        }

        return false;
    }

    // Получить значение поля
    private function getFieldValue($field, User $user, UserStats $stats)
    {
        switch ($field) {
            case 'quests_completed':
                return $stats->quests_completed;
            case 'routes_completed':
                return $stats->routes_completed;
            case 'distance_traveled':
                return $stats->distance_traveled;
            case 'level':
                return $user->level;
            case 'days_active':
                return $stats->days_active;
            default:
                return 0;
        }
    }

    // Уведомить о достижении
    private function notifyAboutAchievement(User $user, $achievement)
    {
        // Здесь будет логика отправки уведомления
        // Можно использовать Laravel Notifications, WebSocket или просто записать в базу
        
        Log::info("User {$user->id} earned achievement: {$achievement['title']}");
        
        // В реальном приложении:
        // Notification::send($user, new AchievementEarnedNotification($achievement));
    }

    // Получить все достижения пользователя
    public function getUserAchievements(User $user)
    {
        $stats = $user->stats;
        $earned = $stats->achievements ?? [];
        
        $result = [];
        
        foreach ($this->achievements as $key => $achievement) {
            $result[] = [
                'key' => $key,
                'title' => $achievement['title'],
                'description' => $achievement['description'],
                'icon' => $achievement['icon'],
                'color' => $achievement['color'],
                'earned' => in_array($key, $earned),
                'earned_at' => in_array($key, $earned) ? now() : null, // В реальном приложении нужно хранить дату получения
            ];
        }
        
        return $result;
    }
}