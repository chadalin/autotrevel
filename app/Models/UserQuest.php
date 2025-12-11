<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quest_id',
        'status',
        'progress_current',
        'progress_target',
        'completed_data',
        'started_at',
        'completed_at',
        'attempts_count',
    ];

    protected $casts = [
        'completed_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_current' => 'integer',
        'progress_target' => 'integer',
        'attempts_count' => 'integer',
    ];

    protected $appends = ['progress_percentage'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->progress_target === 0) {
            return 0;
        }

        return round(($this->progress_current / $this->progress_target) * 100);
    }

    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'attempts_count' => $this->attempts_count + 1,
        ]);
    }

    public function updateProgress($progress)
    {
        $this->update([
            'progress_current' => min($progress, $this->progress_target),
        ]);

        if ($this->progress_current >= $this->progress_target) {
            $this->markAsCompleted();
        }
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_current' => $this->progress_target,
        ]);

        // Начисляем награды
        $this->user->addExperience($this->quest->reward_exp);
        $this->user->addCoins($this->quest->reward_coins);

        // Выдаём значок, если есть
        if ($this->quest->badge_id) {
            $this->user->badges()->syncWithoutDetaching([$this->quest->badge_id => [
                'earned_at' => now(),
                'metadata' => json_encode(['quest_id' => $this->quest_id]),
            ]]);
        }

        // Обновляем статистику
        $this->user->stats()->increment('quests_completed');

        // Создаём запись о выполнении
        QuestCompletion::create([
            'user_id' => $this->user_id,
            'quest_id' => $this->quest_id,
            'proof_data' => $this->completed_data,
            'verification_status' => 'pending',
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function canBeRestarted()
    {
        return $this->quest->is_repeatable || 
               ($this->quest->max_completions && 
                $this->attempts_count < $this->quest->max_completions);
    }
}