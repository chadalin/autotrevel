<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Point extends Model
{
    protected $table = 'points_of_interest'; // Указываем правильное имя таблицы
    
    protected $fillable = [
        'route_id', 'title', 'description', 'type', 'lat', 'lng',
        'order', 'photos', 'metadata'
    ];
    
    protected $casts = [
        'photos' => 'array',
        'metadata' => 'array',
        'lat' => 'float',
        'lng' => 'float'
    ];
    
    public function route(): BelongsTo
    {
        return $this->belongsTo(TravelRoute::class, 'route_id');
    }
    
    public function checkpoints(): HasMany
    {
        return $this->hasMany(RouteCheckpoint::class, 'point_id');
    }
    
    public function quests()
    {
        return $this->belongsToMany(Quest::class, 'quest_point');
    }
    
    // Геттеры для удобства
    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng
        ];
    }
    
    public function getTypeIconAttribute(): string
    {
        $icons = [
            'viewpoint' => 'fas fa-binoculars',
            'cafe' => 'fas fa-coffee',
            'hotel' => 'fas fa-bed',
            'attraction' => 'fas fa-landmark',
            'gas_station' => 'fas fa-gas-pump',
            'camping' => 'fas fa-campground',
            'photo_spot' => 'fas fa-camera',
            'nature' => 'fas fa-tree',
            'historical' => 'fas fa-landmark',
            'other' => 'fas fa-map-marker-alt'
        ];
        
        return $icons[$this->type] ?? 'fas fa-map-marker-alt';
    }
    
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'viewpoint' => 'Смотровая',
            'cafe' => 'Кафе',
            'hotel' => 'Отель',
            'attraction' => 'Достопримечательность',
            'gas_station' => 'Заправка',
            'camping' => 'Кемпинг',
            'photo_spot' => 'Фото точка',
            'nature' => 'Природа',
            'historical' => 'Историческое место',
            'other' => 'Другое'
        ];
        
        return $labels[$this->type] ?? 'Точка интереса';
    }
    
    public function getTypeColorAttribute(): string
    {
        $colors = [
            'viewpoint' => '#F59E0B',
            'cafe' => '#EF4444',
            'hotel' => '#3B82F6',
            'attraction' => '#6366F1',
            'gas_station' => '#6B7280',
            'camping' => '#10B981',
            'photo_spot' => '#8B5CF6',
            'nature' => '#059669',
            'historical' => '#DC2626',
            'other' => '#6B7280'
        ];
        
        return $colors[$this->type] ?? '#6B7280';
    }
}