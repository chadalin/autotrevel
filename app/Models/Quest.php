<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'type',
        'difficulty',
        'reward_exp',
        'reward_coins',
        'badge_id',
        'conditions',
        'quest_data', // JSON с заданиями и контентом
        'requirements',
        'sort_order',
        'is_active',
        'is_featured',
        'is_repeatable',
        'max_completions',
        'start_date',
        'end_date',
        'cover_image',
        'icon',
        'color',
        'chat_id' ,// ID чата для участников квеста
    ];

    protected $casts = [
        'conditions' => 'array',
        'requirements' => 'array',
        'quest_data' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_repeatable' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'reward_exp' => 'integer',
        'reward_coins' => 'integer',
    ];

    protected $appends = [
        'type_label',
        'difficulty_label',
        'difficulty_color',
        'participants_count',
        'completion_rate',
        'is_available',
        'time_remaining',
    ];

    public function routes()
{
    return $this->belongsToMany(Route::class, 'quest_routes', 'quest_id', 'route_id')
        ->withPivot('order', 'is_required', 'verification_data')
        ->orderBy('quest_routes.order')
        ->select(['travel_routes.*', 'quest_routes.order as pivot_order']); // Явно указываем таблицу
}

    public function requiredRoutes()
    {
        return $this->routes()->wherePivot('is_required', true);
    }

    public function badge()
    {
        return $this->belongsTo(QuestBadge::class, 'badge_id');
    }

    public function userQuests()
    {
        return $this->hasMany(UserQuest::class);
    }

    public function completions()
    {
        return $this->hasMany(QuestCompletion::class);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'collection' => 'Коллекционный',
            'challenge' => 'Испытание',
            'weekend' => 'Выходной',
            'story' => 'Сюжетный',
            'user' => 'Пользовательский',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getDifficultyLabelAttribute()
    {
        $labels = [
            'easy' => 'Лёгкий',
            'medium' => 'Средний',
            'hard' => 'Сложный',
            'expert' => 'Экспертный',
        ];

        return $labels[$this->difficulty] ?? $this->difficulty;
    }

    public function getDifficultyColorAttribute()
    {
        $colors = [
            'easy' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'hard' => 'bg-red-100 text-red-800',
            'expert' => 'bg-purple-100 text-purple-800',
        ];

        return $colors[$this->difficulty] ?? 'bg-gray-100 text-gray-800';
    }

    public function getParticipantsCountAttribute()
    {
        return $this->userQuests()->count();
    }

    public function getCompletionRateAttribute()
    {
        $total = $this->participants_count;
        $completed = $this->userQuests()->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    public function getIsAvailableAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $this->start_date->gt($now)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($now)) {
            return false;
        }

        return true;
    }

    public function getTimeRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $remaining = now()->diff($this->end_date);

        if ($remaining->invert) {
            return 'Завершён';
        }

        if ($remaining->days > 0) {
            return "{$remaining->days} д. {$remaining->h} ч.";
        }

        return "{$remaining->h} ч. {$remaining->i} мин.";
    }

    public function getProgressForUser(User $user)
    {
        $userQuest = $this->userQuests()->where('user_id', $user->id)->first();

        if (!$userQuest) {
            return [
                'status' => 'available',
                'progress' => 0,
                'progress_percentage' => 0,
                'started_at' => null,
                'completed_at' => null,
            ];
        }

        return [
            'status' => $userQuest->status,
            'progress' => $userQuest->progress_current,
            'progress_target' => $userQuest->progress_target,
            'progress_percentage' => $userQuest->progress_target > 0 
                ? round(($userQuest->progress_current / $userQuest->progress_target) * 100)
                : 0,
            'started_at' => $userQuest->started_at,
            'completed_at' => $userQuest->completed_at,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function checkRequirements(User $user)
{
    // Получаем требования из JSON поля
    $requirements = $this->requirements;
    
    // Если это строка JSON, декодируем
    if (is_string($requirements)) {
        $requirements = json_decode($requirements, true);
    }
    
    // Если не массив или пусто, возвращаем true
    if (empty($requirements) || !is_array($requirements)) {
        return true;
    }

    foreach ($requirements as $requirement) {
        // Проверяем структуру требования
        if (!isset($requirement['type']) || !isset($requirement['value'])) {
            break; // Вместо continue
        }
        
        switch ($requirement['type']) {
            case 'min_level':
                if ($user->level < $requirement['value']) {
                    return false;
                }
                break;
                
            case 'completed_quests':
                $completed = $user->userQuests()->where('status', 'completed')->count();
                if ($completed < $requirement['value']) {
                    return false;
                }
                break;
                
            case 'required_badges':
                // Проверяем наличие поля badges
                if (!isset($requirement['badges']) || !is_array($requirement['badges'])) {
                    break; // Вместо continue
                }
                $badgeIds = $user->badges()->pluck('badge_id')->toArray();
                $missing = array_diff($requirement['badges'], $badgeIds);
                if (!empty($missing)) {
                    return false;
                }
                break;
                
            case 'min_distance':
                $stats = $user->stats;
                if (!$stats || $stats->distance_traveled < $requirement['value']) {
                    return false;
                }
                break;
                
            default:
                // Неизвестный тип требования - пропускаем
                break; // Вместо continue
        }
    }

    return true;
}

 public function users()
    {
        return $this->belongsToMany(User::class, 'user_quests')
            ->withPivot('status', 'progress_current', 'progress_target', 'completed_data', 'started_at', 'completed_at', 'attempts_count')
            ->withTimestamps();
    } 
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
     public function getCurrentTasksAttribute()
    {
        return $this->quest_data['tasks'] ?? [];
    }

    public function getTask($taskId)
    {
        $tasks = $this->current_tasks;
        foreach ($tasks as $task) {
            if ($task['id'] == $taskId) {
                return $task;
            }
        }
        return null;
    }

    public function isActive()
    {
        $now = now();
        $isWithinDates = true;
        
        if ($this->start_date && $this->start_date > $now) {
            $isWithinDates = false;
        }
        
        if ($this->end_date && $this->end_date < $now) {
            $isWithinDates = false;
        }
        
        return $this->is_active && $isWithinDates;
    }

    

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Статистика квеста
    public function getStatisticsAttribute()
    {
        $totalAttempts = $this->users()->count();
        $completed = $this->users()->wherePivot('status', 'completed')->count();
        $successRate = $totalAttempts > 0 ? round(($completed / $totalAttempts) * 100) : 0;
        
        return [
            'attempts' => $totalAttempts,
            'completed' => $completed,
            'success_rate' => $successRate,
            'avg_completion_time' => $this->getAverageCompletionTime(),
        ];
    }

    private function getAverageCompletionTime()
    {
        $completedQuests = $this->users()
            ->wherePivot('status', 'completed')
            ->wherePivot('completed_at', '!=', null)
            ->wherePivot('started_at', '!=', null)
            ->get()
            ->map(function($user) {
                $started = \Carbon\Carbon::parse($user->pivot->started_at);
                $completed = \Carbon\Carbon::parse($user->pivot->completed_at);
                return $started->diffInHours($completed);
            });
        
        return $completedQuests->count() > 0 ? 
            round($completedQuests->avg(), 1) . ' ч' : 
            'Нет данных';
    }

}