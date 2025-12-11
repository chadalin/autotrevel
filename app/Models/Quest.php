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
    ];

    protected $casts = [
        'conditions' => 'array',
        'requirements' => 'array',
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
        return $this->belongsToMany(Route::class, 'quest_routes')
            ->withPivot('order', 'is_required', 'verification_data')
            ->orderBy('quest_routes.order');
    }

    public requiredRoutes()
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
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
        $requirements = $this->requirements ?? [];

        if (empty($requirements)) {
            return true;
        }

        foreach ($requirements as $requirement) {
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
                    $badgeIds = $user->badges()->pluck('badge_id')->toArray();
                    $missing = array_diff($requirement['badges'], $badgeIds);
                    if (!empty($missing)) {
                        return false;
                    }
                    break;
                    
                case 'min_distance':
                    $stats = $user->stats;
                    if ($stats->distance_traveled < $requirement['value']) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }
}