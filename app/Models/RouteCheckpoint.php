<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteCheckpoint extends Model
{
    use HasFactory;

    protected $table = 'route_checkpoints';
    
    protected $fillable = [
        'route_session_id',
        'point_id',
        'order',
        'status',
        'arrived_at',
        'completed_at',
        'photo_path',
        'notes',
        'coordinates',
        'verification_data',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
        'coordinates' => 'array',
        'verification_data' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';

    // Отношения
    public function session()
    {
        return $this->belongsTo(RouteSession::class, 'route_session_id');
    }

    public function point()
    {
        return $this->belongsTo(RoutePoint::class, 'point_id');
    }

    // Методы
    public function markAsArrived($coordinates = null)
    {
        $this->update([
            'status' => self::STATUS_ARRIVED,
            'arrived_at' => now(),
            'coordinates' => $coordinates,
        ]);
    }

    public function markAsCompleted($photoPath = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'photo_path' => $photoPath,
            'notes' => $notes,
        ]);
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getPhotoUrl()
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

      public function route()
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function comments()
    {
        return $this->hasMany(CheckpointComment::class);
    }

    public function photos()
    {
        return $this->hasMany(CheckpointPhoto::class);
    }

    public function getFullTypeAttribute()
    {
        $types = [
            'checkpoint' => 'Контрольная точка',
            'viewpoint' => 'Смотровая площадка',
            'rest' => 'Место отдыха',
            'cafe' => 'Кафе',
            'hotel' => 'Отель',
            'attraction' => 'Достопримечательность',
            'gas_station' => 'Заправка',
            'camping' => 'Кемпинг',
            'photo_spot' => 'Фото-точка',
            'nature' => 'Природа',
            'historical' => 'Историческое место',
            'other' => 'Другое'
        ];

        return $types[$this->type] ?? $this->type;
    }
}