<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteSession extends Model
{
    protected $table = 'route_sessions';
    
    protected $fillable = [
        'user_id',
        'route_id',
        'quest_id',
        'status',
        'started_at',
        'paused_at',
        'completed_at',
        'ended_at',
        'current_checkpoint_id',
        'average_speed',
        'total_distance',
        'earned_xp'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function route(): BelongsTo
    {
        return $this->belongsTo(TravelRoute::class);
    }
    
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }
    
    // Исправляем: возвращаем коллекцию, даже если нет quest_id
    public function quests()
    {
        if ($this->quest_id) {
            // Если есть конкретный quest_id, возвращаем коллекцию с одним квестом
            return collect([$this->quest]);
        }
        
        // Иначе возвращаем пустую коллекцию
        return collect();
    }
    
    // ЯВНО указываем имя внешнего ключа
    public function checkpoints(): HasMany
    {
        return $this->hasMany(RouteCheckpoint::class, 'session_id');
    }
    
    public function currentCheckpoint(): BelongsTo
    {
        return $this->belongsTo(RouteCheckpoint::class, 'current_checkpoint_id');
    }
    
    public function completion()
    {
        return $this->hasOne(RouteCompletion::class, 'session_id');
    }
    
    // Методы
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }
    
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    public function getProgressPercentage(): int
    {
        $total = $this->checkpoints()->count();
        $completed = $this->checkpoints()->where('status', 'completed')->count();
        
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
    
    public function getElapsedTime(): string
    {
        if (!$this->started_at) {
            return '00:00';
        }
        
        $end = $this->completed_at ?: $this->paused_at ?: now();
        
        $diff = $this->started_at->diff($end);
        
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    public function getRemainingCheckpointsCount(): int
    {
        return $this->checkpoints()->whereIn('status', ['pending', 'active'])->count();
    }
}