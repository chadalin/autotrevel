<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Models\UserQuest;
use App\Models\Route;
use Illuminate\Support\Facades\DB;

class QuestService
{
    // Начать квест
    public function startQuest(User $user, Quest $quest)
    {
        return DB::transaction(function () use ($user, $quest) {
            // Проверяем, можно ли начать квест
            if (!$this->canStartQuest($user, $quest)) {
                throw new \Exception('Невозможно начать этот квест');
            }

            // Создаём запись о начале квеста
            $userQuest = UserQuest::create([
                'user_id' => $user->id,
                'quest_id' => $quest->id,
                'status' => 'in_progress',
                'progress_target' => $this->calculateProgressTarget($quest),
                'started_at' => now(),
                'attempts_count' => 1,
            ]);

            // Запускаем проверки для квестов типа "challenge"
            if ($quest->type === 'challenge') {
                $this->initializeChallenge($userQuest);
            }

            return $userQuest;
        });
    }

    // Проверить, можно ли начать квест
    public function canStartQuest(User $user, Quest $quest)
    {
        // Проверяем доступность квеста
        if (!$quest->is_available) {
            return false;
        }

        // Проверяем требования
        if (!$quest->checkRequirements($user)) {
            return false;
        }

        // Проверяем, не начат ли уже квест
        $existingQuest = $user->userQuests()
            ->where('quest_id', $quest->id)
            ->first();

        if ($existingQuest) {
            // Если квест завершён и не повторяемый
            if ($existingQuest->isCompleted() && !$quest->is_repeatable) {
                return false;
            }

            // Если квест в процессе
            if ($existingQuest->isInProgress()) {
                return false;
            }

            // Если квест провален, можно перезапустить
            if ($existingQuest->status === 'failed' && !$existingQuest->canBeRestarted()) {
                return false;
            }
        }

        return true;
    }

    // Рассчитать цель прогресса
    private function calculateProgressTarget(Quest $quest)
    {
        switch ($quest->type) {
            case 'collection':
                return $quest->requiredRoutes()->count();
                
            case 'challenge':
                $conditions = $quest->conditions ?? [];
                return $conditions['target'] ?? 1;
                
            case 'weekend':
            case 'story':
            case 'user':
                return $quest->routes()->count();
                
            default:
                return 1;
        }
    }

    // Инициализировать испытание
    private function initializeChallenge(UserQuest $userQuest)
    {
        $quest = $userQuest->quest;
        $conditions = $quest->conditions ?? [];

        switch ($conditions['type'] ?? null) {
            case 'distance':
                // Для испытаний на расстояние прогресс начинается с 0
                $userQuest->update(['progress_current' => 0]);
                break;
                
            case 'routes_completed':
                // Для испытаний на количество маршрутов
                $completedRoutes = $userQuest->user->stats->routes_completed;
                $userQuest->update(['progress_current' => $completedRoutes]);
                break;
        }
    }

    // Обновить прогресс квеста
    public function updateQuestProgress(User $user, Quest $quest, $data = [])
    {
        $userQuest = $user->userQuests()
            ->where('quest_id', $quest->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$userQuest) {
            return false;
        }

        switch ($quest->type) {
            case 'collection':
                return $this->updateCollectionQuest($userQuest, $data);
                
            case 'challenge':
                return $this->updateChallengeQuest($userQuest, $data);
                
            case 'weekend':
                return $this->updateWeekendQuest($userQuest, $data);
        }

        return false;
    }

    // Обновить коллекционный квест
    private function updateCollectionQuest(UserQuest $userQuest, $data)
    {
        $routeId = $data['route_id'] ?? null;
        $proof = $data['proof'] ?? null;

        if (!$routeId) {
            return false;
        }

        // Проверяем, входит ли маршрут в квест
        $questRoute = $userQuest->quest->routes()
            ->where('route_id', $routeId)
            ->first();

        if (!$questRoute) {
            return false;
        }

        // Проверяем, не завершён ли уже этот маршрут
        $completedData = $userQuest->completed_data ?? [];
        if (in_array($routeId, $completedData)) {
            return false;
        }

        // Верифицируем доказательство
        if (!$this->verifyProof($userQuest, $questRoute, $proof)) {
            return false;
        }

        // Добавляем маршрут в выполненные
        $completedData[] = $routeId;
        $progress = count($completedData);

        $userQuest->update([
            'progress_current' => $progress,
            'completed_data' => $completedData,
        ]);

        // Проверяем завершение квеста
        if ($progress >= $userQuest->progress_target) {
            $userQuest->markAsCompleted();
            return 'completed';
        }

        return 'updated';
    }

    // Обновить испытание
    private function updateChallengeQuest(UserQuest $userQuest, $data)
    {
        $quest = $userQuest->quest;
        $conditions = $quest->conditions ?? [];
        $type = $conditions['type'] ?? null;

        switch ($type) {
            case 'distance':
                $distance = $data['distance'] ?? 0;
                $newProgress = $userQuest->progress_current + $distance;
                $userQuest->updateProgress($newProgress);
                break;
                
            case 'routes_completed':
                $userQuest->updateProgress($userQuest->progress_current + 1);
                break;
                
            case 'time':
                // Для временных испытаний
                break;
        }

        return 'updated';
    }

    // Обновить квест выходного дня
    private function updateWeekendQuest(UserQuest $userQuest, $data)
    {
        // Аналогично коллекционному, но с временными ограничениями
        return $this->updateCollectionQuest($userQuest, $data);
    }

    // Верифицировать доказательство
    private function verifyProof(UserQuest $userQuest, $questRoute, $proof)
    {
        // Простая проверка для начала
        // В реальном приложении здесь будет сложная логика верификации
        
        if (!$proof) {
            return false;
        }

        // Проверка фото с геометками
        if (isset($proof['photo'])) {
            return $this->verifyPhotoWithGeo($proof['photo'], $questRoute);
        }

        // Проверка кода
        if (isset($proof['code'])) {
            $verificationData = $questRoute->pivot->verification_data ?? [];
            $secretCode = $verificationData['secret_code'] ?? null;
            
            return $secretCode && $proof['code'] === $secretCode;
        }

        // Проверка GPS трека
        if (isset($proof['gps_track'])) {
            return $this->verifyGpsTrack($proof['gps_track'], $questRoute);
        }

        return false;
    }

    private function verifyPhotoWithGeo($photoData, $questRoute)
    {
        // В реальном приложении здесь будет проверка EXIF данных фото
        // и сравнение с координатами маршрута
        return true;
    }

    private function verifyGpsTrack($gpsTrack, $questRoute)
    {
        // В реальном приложении здесь будет анализ GPS трека
        return true;
    }

    // Получить рекомендованные квесты для пользователя
    public function getRecommendedQuests(User $user, $limit = 5)
    {
        $availableQuests = $user->getAvailableQuests();

        // Сортируем по релевантности
        return $availableQuests->sortByDesc(function ($quest) use ($user) {
            $score = 0;

            // Предпочтение по сложности (исходя из уровня пользователя)
            $difficultyScore = [
                'easy' => 1,
                'medium' => 2,
                'hard' => 3,
                'expert' => 4,
            ];

            $userLevel = $user->level;
            $questDifficulty = $difficultyScore[$quest->difficulty] ?? 1;

            if (abs($userLevel - $questDifficulty) <= 1) {
                $score += 3;
            }

            // Предпочтение по типу (если пользователь часто выполнял подобные)
            $userQuests = $user->userQuests()->with('quest')->get();
            $completedTypes = $userQuests->where('status', 'completed')
                ->pluck('quest.type')
                ->countBy();

            $mostCompletedType = $completedTypes->sortDesc()->keys()->first();
            if ($mostCompletedType === $quest->type) {
                $score += 2;
            }

            // Предпочтение по географии (маршруты рядом с пользователем)
            // Здесь нужно добавить логику определения местоположения пользователя

            // Предпочтение по времени (выходные квесты в пятницу)
            if ($quest->type === 'weekend' && now()->isFriday()) {
                $score += 5;
            }

            // Предпочтение новым квестам
            if ($quest->created_at->gt(now()->subDays(7))) {
                $score += 1;
            }

            return $score;
        })->take($limit);
    }

    // Сгенерировать квест выходного дня
    public function generateWeekendQuest()
    {
        // Логика генерации случайного квеста на выходные
        $regions = ['Московская область', 'Ленинградская область', 'Золотое кольцо', 'Карелия'];
        $themes = ['Озёра', 'Горы', 'Исторические места', 'Заброшенные объекты'];
        
        $region = $regions[array_rand($regions)];
        $theme = $themes[array_rand($themes)];
        
        // Ищем маршруты по региону и теме
        $routes = Route::published()
            ->whereHas('tags', function ($query) use ($theme) {
                $query->where('name', 'like', "%{$theme}%");
            })
            ->limit(3)
            ->get();

        if ($routes->isEmpty()) {
            return null;
        }

        $quest = Quest::create([
            'title' => "Выходные в {$region}: {$theme}",
            'slug' => 'weekend-' . str_slug($region) . '-' . str_slug($theme) . '-' . time(),
            'description' => "Специальный квест на эти выходные! Посетите самые интересные места {$region} на тему '{$theme}'.",
            'type' => 'weekend',
            'difficulty' => 'medium',
            'reward_exp' => 150,
            'reward_coins' => 50,
            'is_active' => true,
            'start_date' => now()->next('Friday')->startOfDay(),
            'end_date' => now()->next('Sunday')->endOfDay(),
            'color' => '#3B82F6',
            'icon' => 'fas fa-weekend',
        ]);

        foreach ($routes as $index => $route) {
            $quest->routes()->attach($route->id, [
                'order' => $index,
                'is_required' => true,
            ]);
        }

        return $quest;
    }

    // Получить статистику квеста
    public function getQuestStatistics(Quest $quest)
    {
        $totalParticipants = $quest->userQuests()->count();
        $completed = $quest->userQuests()->where('status', 'completed')->count();
        $inProgress = $quest->userQuests()->where('status', 'in_progress')->count();
        $failed = $quest->userQuests()->where('status', 'failed')->count();

        $completionTime = null;
        if ($completed > 0) {
            $avgTime = $quest->userQuests()
                ->where('status', 'completed')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
                ->first()
                ->avg_hours;
            
            $completionTime = round($avgTime) . ' часов';
        }

        return [
            'total_participants' => $totalParticipants,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'failed' => $failed,
            'completion_rate' => $totalParticipants > 0 ? round(($completed / $totalParticipants) * 100) : 0,
            'avg_completion_time' => $completionTime,
            'popular_routes' => $this->getPopularRoutesInQuest($quest),
        ];
    }

    private function getPopularRoutesInQuest(Quest $quest)
{
    // Получаем маршруты, связанные с квестом
    $routeIds = $quest->routes()->pluck('id');
    
    if ($routeIds->isEmpty()) {
        return collect();
    }
    
    // Находим популярные маршруты (по просмотрам или завершениям)
    return Route::whereIn('id', $routeIds)
        ->with(['user', 'tags'])
        ->orderByDesc('views_count')
        ->orderByDesc('favorites_count')
        ->limit(5)
        ->get();
}
}