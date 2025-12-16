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
        'status',
        'quest_id',
        'current_checkpoint_id',
        'average_speed',
        'total_distance',
        'earned_xp',
        'started_at',
        'paused_at',
        'completed_at',
        'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
        'ended_at' => 'datetime',
        'average_speed' => 'decimal:2',
        'total_distance' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function currentCheckpoint()
    {
        return $this->belongsTo(RouteCheckpoint::class, 'current_checkpoint_id');
    }

    public function completions()
    {
        return $this->hasMany(RouteCompletion::class);
    }

    // Вместо прямого отношения к checkpoint-ам используем связь через route
    public function routeCheckpoints()
    {
        return $this->route->checkpoints();
    }

    public function getProgressPercentage(): int
    {
        $total = $this->route->checkpoints()->count();
        if ($total === 0) {
            return 0;
        }

        // Получаем количество пройденных чекпоинтов
        // Предполагаем, что чекпоинты пройдены по порядку
        $currentOrder = $this->currentCheckpoint ? $this->currentCheckpoint->order : 0;
        return min(100, intval(($currentOrder / $total) * 100));
    }

    public function completeCheckpoint(RouteCheckpoint $checkpoint)
    {
        // Обновляем текущий чекпоинт
        $this->update(['current_checkpoint_id' => $checkpoint->id]);
        
        // Если это последний чекпоинт, завершаем сессию
        $lastCheckpoint = $this->route->checkpoints()->orderBy('order', 'desc')->first();
        if ($lastCheckpoint && $checkpoint->id === $lastCheckpoint->id) {
            $this->complete();
        }
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'ended_at' => now()
        ]);

        // Создаем запись о завершении маршрута
        RouteCompletion::create([
            'user_id' => $this->user_id,
            'route_id' => $this->route_id,
            'session_id' => $this->id,
            'quest_id' => $this->quest_id,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? $this->started_at->diffInSeconds(now()) : 0,
            'total_distance' => $this->total_distance,
            'earned_xp' => $this->earned_xp,
            'verification_status' => 'pending'
        ]);
    }

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

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function start()
    {
        $this->update([
            'status' => 'active',
            'started_at' => now()
        ]);
    }

    public function pause()
    {
        $this->update([
            'status' => 'paused',
            'paused_at' => now()
        ]);
    }

    public function resume()
    {
        $this->update([
            'status' => 'active',
            'paused_at' => null
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'ended_at' => now()
        ]);
    }
}