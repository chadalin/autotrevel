<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestTaskProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quest_id',
        'task_id',
        'status',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'attempts',
        'user_answer',
        'is_correct',
        'points_earned',
        'hints_used',
        'penalty_points'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'hints_used' => 'array',
        'is_correct' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function task()
    {
        return $this->belongsTo(QuestTask::class);
    }

    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function complete($isCorrect, $answer = null)
    {
        $completedAt = now();
        $timeSpent = $this->started_at ? $this->started_at->diffInSeconds($completedAt) : 0;
        
        $this->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'time_spent_seconds' => $timeSpent,
            'user_answer' => $answer,
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $this->calculatePoints() : 0
        ]);
    }

    public function useHint($hintIndex)
    {
        $hintsUsed = $this->hints_used ?? [];
        if (!in_array($hintIndex, $hintsUsed)) {
            $hintsUsed[] = $hintIndex;
            $this->update([
                'hints_used' => $hintsUsed,
                'penalty_points' => ($this->penalty_points ?? 0) + 5 // Штраф за подсказку
            ]);
        }
    }

    private function calculatePoints()
    {
        $task = $this->task;
        $basePoints = $task->points ?? 10;
        
        // Штраф за время
        $timePenalty = $this->calculateTimePenalty();
        
        // Штраф за подсказки
        $hintPenalty = ($this->penalty_points ?? 0);
        
        $total = $basePoints - $timePenalty - $hintPenalty;
        
        return max(1, $total); // Минимум 1 балл
    }

    private function calculateTimePenalty()
    {
        if (!$this->completed_at || !$this->started_at) {
            return 0;
        }
        
        $timeLimit = $this->task->time_limit_minutes * 60;
        $timeSpent = $this->time_spent_seconds;
        
        if ($timeLimit === 0 || $timeSpent <= $timeLimit) {
            return 0;
        }
        
        $overtime = $timeSpent - $timeLimit;
        $penalty = floor($overtime / 60) * 2; // 2 балла за каждую минуту опоздания
        
        return min($penalty, 10); // Максимальный штраф 10 баллов
    }

    public function getTimeRemaining()
    {
        if ($this->status !== 'in_progress' || !$this->started_at) {
            return 0;
        }
        
        $timeLimit = $this->task->time_limit_minutes * 60;
        if ($timeLimit === 0) {
            return null;
        }
        
        $elapsed = now()->diffInSeconds($this->started_at);
        $remaining = $timeLimit - $elapsed;
        
        return max(0, $remaining);
    }

    public function isTimeExpired()
    {
        $remaining = $this->getTimeRemaining();
        return $remaining !== null && $remaining <= 0;
    }
}