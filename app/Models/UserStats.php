<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStats extends Model
{
    use HasFactory;

    protected $table = 'user_stats';

    protected $fillable = [
        'user_id',
        'total_exp',
        'total_coins',
        'quests_completed',
        'routes_completed',
        'distance_traveled',
        'days_active',
        'achievements',
        'weekly_stats',
        'monthly_stats',
        'last_active_at',
    ];

    protected $casts = [
        'achievements' => 'array',
        'weekly_stats' => 'array',
        'monthly_stats' => 'array',
        'last_active_at' => 'datetime',
        'total_exp' => 'integer',
        'total_coins' => 'integer',
        'quests_completed' => 'integer',
        'routes_completed' => 'integer',
        'distance_traveled' => 'integer',
        'days_active' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addExperience($amount)
    {
        $this->increment('total_exp', $amount);
        $this->user->addExperience($amount);
    }

    public function addCoins($amount)
    {
        $this->increment('total_coins', $amount);
    }

    public function completeRoute($distance)
    {
        $this->increment('routes_completed');
        $this->increment('distance_traveled', $distance);
    }

    public function recordActivity()
    {
        $today = now()->format('Y-m-d');
        $lastActive = $this->last_active_at ? $this->last_active_at->format('Y-m-d') : null;

        if ($lastActive !== $today) {
            $this->increment('days_active');
            $this->last_active_at = now();
            $this->save();
        }
    }

    public function getLevelProgress()
    {
        $currentLevel = $this->user->level;
        $expForCurrentLevel = $this->calculateExpForLevel($currentLevel);
        $expForNextLevel = $this->calculateExpForLevel($currentLevel + 1);
        $expInLevel = $this->total_exp - $expForCurrentLevel;
        $expNeeded = $expForNextLevel - $expForCurrentLevel;

        return [
            'level' => $currentLevel,
            'exp_current' => $expInLevel,
            'exp_needed' => $expNeeded,
            'progress_percentage' => $expNeeded > 0 ? round(($expInLevel / $expNeeded) * 100) : 100,
        ];
    }

    private function calculateExpForLevel($level)
    {
        return (int) (100 * pow($level, 1.5));
    }

    public function getRank()
    {
        $totalExp = $this->total_exp;

        if ($totalExp >= 10000) return 'Легенда путешествий';
        if ($totalExp >= 5000) return 'Мастер маршрутов';
        if ($totalExp >= 2000) return 'Опытный исследователь';
        if ($totalExp >= 1000) return 'Активный путешественник';
        if ($totalExp >= 500) return 'Начинающий исследователь';
        return 'Новичок';
    }
}