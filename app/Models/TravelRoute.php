<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'short_description',
        'length_km',
        'duration_minutes',
        'difficulty',
        'road_type',
        'start_coordinates',
        'end_coordinates',
        'path_coordinates',
        'coordinates',
        'cover_image',
        'scenery_rating',
        'road_quality_rating',
        'safety_rating',
        'infrastructure_rating',
        'views_count',
        'favorites_count',
        'completions_count',
        'is_published',
        'is_featured',
        'featured_until',
        'road_quality',
        'elevation_gain',
        'best_season',
    ];

    protected $casts = [
        'start_coordinates' => 'array',
        'end_coordinates' => 'array',
        'path_coordinates' => 'array',
        'coordinates' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'length_km' => 'float',
        'duration_minutes' => 'integer',
        'scenery_rating' => 'float',
        'road_quality_rating' => 'float',
        'safety_rating' => 'float',
        'infrastructure_rating' => 'float',
    ];

    // Отношения
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'route_tag', 'route_id', 'tag_id');
    }

    public function points()
    {
        return $this->hasMany(PointOfInterest::class, 'route_id')->orderBy('order');
    }

    public function checkpoints()
    {
        return $this->hasMany(RouteCheckpoint::class, 'route_id')->orderBy('order');
    }

    public function completions()
    {
        return $this->hasMany(RouteCompletion::class, 'route_id');
    }

    public function savedByUsers()
    {
        return $this->hasMany(SavedRoute::class, 'route_id');
    }

    // Скоуп для подсчета favorites
    public function scopeWithFavoritesCount($query)
    {
        return $query->addSelect([
            'favorites_count' => SavedRoute::selectRaw('count(*)')
                ->whereColumn('route_id', 'travel_routes.id')
        ]);
    }

    // Мутатор для slug
    public function setSlugAttribute($value)
    {
        if (empty($value)) {
            $slug = \Illuminate\Support\Str::slug($this->title);
            $counter = 1;
            $originalSlug = $slug;
            
            while (static::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $this->attributes['slug'] = $slug;
        } else {
            $this->attributes['slug'] = $value;
        }
    }

    // Accessor для favorites_count
    public function getFavoritesCountAttribute()
    {
        if (array_key_exists('favorites_count', $this->attributes)) {
            return $this->attributes['favorites_count'];
        }
        
        return $this->savedByUsers()->count();
    }

    // Дополнительные методы
    public function getFormattedLengthAttribute()
    {
        return number_format($this->length_km, 1) . ' км';
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . ' ч ' . $minutes . ' мин';
        }
        
        return $minutes . ' мин';
    }

    public function getDifficultyLabelAttribute()
    {
        return [
            'easy' => 'Лёгкий',
            'medium' => 'Средний',
            'hard' => 'Сложный'
        ][$this->difficulty] ?? $this->difficulty;
    }

    public function getRoadTypeLabelAttribute()
    {
        return [
            'asphalt' => 'Асфальт',
            'gravel' => 'Гравий',
            'offroad' => 'Бездорожье',
            'mixed' => 'Смешанный'
        ][$this->road_type] ?? $this->road_type;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'route_id');
    }
}