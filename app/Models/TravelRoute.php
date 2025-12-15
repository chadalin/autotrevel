<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelRoute extends Model
{
    protected $table = 'travel_routes';
    
    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'short_description',
        'length_km', 'duration_minutes', 'difficulty', 'road_type',
        'scenery_rating', 'road_quality_rating', 'safety_rating', 'infrastructure_rating',
        'views_count', 'favorites_count', 'completions_count',
        'start_coordinates', 'end_coordinates', 'path_coordinates',
        'cover_image', 'is_published', 'is_featured', 'featured_until'
    ];
    
    protected $casts = [
        'start_coordinates' => 'array',
        'end_coordinates' => 'array',
        'path_coordinates' => 'array',
        'scenery_rating' => 'float',
        'road_quality_rating' => 'float',
        'safety_rating' => 'float',
        'infrastructure_rating' => 'float',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Исправляем эту связь - используем правильную модель Point
    public function points(): HasMany
    {
        return $this->hasMany(Point::class, 'route_id')->orderBy('order');
    }
    
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'route_tag');
    }
    
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'route_id');
    }
    
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_routes');
    }
    
    public function completions(): HasMany
    {
        return $this->hasMany(RouteCompletion::class, 'route_id');
    }
    
    public function sessions(): HasMany
    {
        return $this->hasMany(RouteSession::class, 'route_id');
    }
    
    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(Quest::class, 'quest_routes');
    }
    
    // Геттеры
    public function getDifficultyLabelAttribute(): string
    {
        $labels = [
            'easy' => 'Легкий',
            'medium' => 'Средний',
            'hard' => 'Сложный',
            'expert' => 'Эксперт'
        ];
        
        return $labels[$this->difficulty] ?? $this->difficulty;
    }
    
    public function getDifficultyColorAttribute(): string
    {
        $colors = [
            'easy' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'hard' => 'bg-orange-100 text-orange-800',
            'expert' => 'bg-red-100 text-red-800'
        ];
        
        return $colors[$this->difficulty] ?? 'bg-gray-100 text-gray-800';
    }
    
    public function getRoadTypeLabelAttribute(): string
    {
        $labels = [
            'asphalt' => 'Асфальт',
            'gravel' => 'Гравий',
            'offroad' => 'Внедорожье',
            'mixed' => 'Смешанный'
        ];
        
        return $labels[$this->road_type] ?? $this->road_type;
    }
    
    public function getDurationFormattedAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours} ч {$minutes} мин";
        } elseif ($hours > 0) {
            return "{$hours} ч";
        } else {
            return "{$minutes} мин";
        }
    }
    
    public function getAverageRatingAttribute(): float
    {
        if ($this->reviews_count > 0) {
            $total = (
                $this->scenery_rating +
                $this->road_quality_rating +
                $this->safety_rating +
                $this->infrastructure_rating
            ) / 4;
            
            return round($total, 1);
        }
        
        return 0.0;
    }
    
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }
    
    public function getIsSavedAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->favorites()->where('user_id', auth()->id())->exists();
    }

     /**
 * Получить данные для карты
 */
public function getMapData(): array
{
    $data = [
        'id' => $this->id,
        'title' => $this->title,
        'start_coordinates' => $this->start_coordinates,
        'end_coordinates' => $this->end_coordinates,
        'points' => [],
        'polyline' => []
    ];
    
    // Получаем точки маршрута
    if ($this->points) {
        foreach ($this->points as $point) {
            $data['points'][] = [
                'id' => $point->id,
                'title' => $point->title,
                'description' => $point->description,
                'type' => $point->type,
                'lat' => $point->lat,
                'lng' => $point->lng,
                'order' => $point->order,
                'type_icon' => $point->type_icon,
                'type_label' => $point->type_label,
                'type_color' => $point->type_color
            ];
        }
    }
    
    // Генерируем полилинию из координат пути или из точек
    if ($this->path_coordinates && is_array($this->path_coordinates)) {
        // Используем сохраненные координаты пути
        $data['polyline'] = $this->path_coordinates;
    } elseif ($this->points && $this->points->count() > 0) {
        // Генерируем полилинию из точек маршрута
        $data['polyline'] = $this->points->sortBy('order')->map(function($point) {
            return [$point->lat, $point->lng];
        })->toArray();
    }
    
    // Добавляем начальные координаты если есть и они валидны
    if ($this->start_coordinates && 
        is_array($this->start_coordinates) && 
        isset($this->start_coordinates['lat']) && 
        isset($this->start_coordinates['lng'])) {
        $data['polyline'] = array_merge(
            [[$this->start_coordinates['lat'], $this->start_coordinates['lng']]], 
            $data['polyline']
        );
    }
    
    // Добавляем конечные координаты если есть и они валидны
    if ($this->end_coordinates && 
        is_array($this->end_coordinates) && 
        isset($this->end_coordinates['lat']) && 
        isset($this->end_coordinates['lng'])) {
        $data['polyline'][] = [$this->end_coordinates['lat'], $this->end_coordinates['lng']];
    }
    
    return $data;
}
    
    /**
     * Получить данные для карты в упрощенном формате
     */
    public function getSimpleMapData(): array
    {
        $points = [];
        
        if ($this->points) {
            foreach ($this->points as $point) {
                $points[] = [
                    'id' => $point->id,
                    'lat' => $point->lat,
                    'lng' => $point->lng,
                    'title' => $point->title,
                    'type' => $point->type
                ];
            }
        }
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'points' => $points,
            'bounds' => $this->getRouteBounds()
        ];
    }
    
    /**
     * Получить границы маршрута для карты
     */
   /**
 * Получить границы маршрута для карты
 */
public function getRouteBounds(): array
{
    $bounds = [
        'north' => -90,
        'south' => 90,
        'east' => -180,
        'west' => 180
    ];
    
    // Добавляем стартовые координаты если они есть и валидны
    if ($this->start_coordinates && 
        is_array($this->start_coordinates) && 
        isset($this->start_coordinates['lat']) && 
        isset($this->start_coordinates['lng'])) {
        
        $lat = (float) $this->start_coordinates['lat'];
        $lng = (float) $this->start_coordinates['lng'];
        
        $bounds['north'] = max($bounds['north'], $lat);
        $bounds['south'] = min($bounds['south'], $lat);
        $bounds['east'] = max($bounds['east'], $lng);
        $bounds['west'] = min($bounds['west'], $lng);
    }
    
    // Добавляем конечные координаты если они есть и валидны
    if ($this->end_coordinates && 
        is_array($this->end_coordinates) && 
        isset($this->end_coordinates['lat']) && 
        isset($this->end_coordinates['lng'])) {
        
        $lat = (float) $this->end_coordinates['lat'];
        $lng = (float) $this->end_coordinates['lng'];
        
        $bounds['north'] = max($bounds['north'], $lat);
        $bounds['south'] = min($bounds['south'], $lat);
        $bounds['east'] = max($bounds['east'], $lng);
        $bounds['west'] = min($bounds['west'], $lng);
    }
    
    // Добавляем точки маршрута
    if ($this->points) {
        foreach ($this->points as $point) {
            $bounds['north'] = max($bounds['north'], (float) $point->lat);
            $bounds['south'] = min($bounds['south'], (float) $point->lat);
            $bounds['east'] = max($bounds['east'], (float) $point->lng);
            $bounds['west'] = min($bounds['west'], (float) $point->lng);
        }
    }
    
    // Если нет данных, возвращаем дефолтные границы (Москва)
    if ($bounds['north'] == -90) {
        return [
            'north' => 55.85,
            'south' => 55.65,
            'east' => 37.75,
            'west' => 37.45
        ];
    }
    
    // Добавляем немного отступа
    $padding = 0.01;
    $bounds['north'] += $padding;
    $bounds['south'] -= $padding;
    $bounds['east'] += $padding;
    $bounds['west'] -= $padding;
    
    return $bounds;
}
}