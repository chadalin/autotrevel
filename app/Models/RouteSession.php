<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'route_id',
        'quest_id',
        'status',
        'current_position',
        'checkpoints_visited',
        'started_at',
        'paused_at',
        'completed_at',
        'distance_traveled',
        'duration_seconds',
        'current_checkpoint_id'
    ];

    protected $casts = [
        'current_position' => 'array',
        'checkpoints_visited' => 'array',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Связь с маршрутом
    public function route()
    {
        return $this->belongsTo(TravelRoute::class, 'route_id');
    }

    // Связь с квестом
    public function quest()
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Связь с текущим чекпоинтом
    public function currentCheckpoint()
    {
        return $this->belongsTo(RouteCheckpoint::class, 'current_checkpoint_id');
    }

    // Метод для получения чекпоинтов маршрута
    public function getRouteCheckpoints()
    {
        if (!$this->route) {
            return collect();
        }
        
        return $this->route->checkpoints()->orderBy('order')->get();
    }

    // Метод для получения прогресса
    public function getProgress()
    {
        $checkpoints = $this->getRouteCheckpoints();
        $total = $checkpoints->count();
        
        if ($total === 0) {
            return [
                'percentage' => 0,
                'completed' => 0,
                'total' => 0
            ];
        }
        
        $completed = $this->checkpoints_visited ? count($this->checkpoints_visited) : 0;
        $percentage = round(($completed / $total) * 100);
        
        return [
            'percentage' => $percentage,
            'completed' => $completed,
            'total' => $total
        ];
    }

    // Проверка статусов
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPaused()
    {
        return $this->status === 'paused';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // Получение пройденного времени
    public function getElapsedTime()
    {
        if (!$this->started_at) {
            return '0:00';
        }
        
        $endTime = $this->completed_at ?? ($this->paused_at ?? now());
        $seconds = $endTime->diffInSeconds($this->started_at);
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return sprintf('%d:%02d', $hours, $minutes);
        }
        
        return sprintf('%d мин', $minutes);
    }
}