<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'travel_routes';

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
        'scenery_rating',
        'road_quality_rating',
        'safety_rating',
        'infrastructure_rating',
        'start_coordinates',
        'end_coordinates',
        'path_coordinates',
        'cover_image',
        'is_published',
        'is_featured',
        'featured_until',
    ];

    protected $casts = [
        'start_coordinates' => 'array',
        'end_coordinates' => 'array',
        'path_coordinates' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'scenery_rating' => 'float',
        'road_quality_rating' => 'float',
        'safety_rating' => 'float',
        'infrastructure_rating' => 'float',
        'length_km' => 'float',
    ];

    protected $appends = [
        'average_rating', 
        'difficulty_label', 
        'road_type_label',
        'reviews_count',
        'is_saved'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'route_tag');
    }

    public function points()
    {
        return $this->hasMany(PointOfInterest::class, 'route_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'route_id');
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_routes', 'route_id', 'user_id');
    }

    public function getAverageRatingAttribute()
    {
        if ($this->reviews_count === 0) {
            return 0;
        }

        return round((
            $this->reviews->avg('scenery_rating') +
            $this->reviews->avg('road_quality_rating') +
            $this->reviews->avg('safety_rating') +
            $this->reviews->avg('infrastructure_rating')
        ) / 4, 1);
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getIsSavedAttribute()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->savedByUsers()->where('user_id', auth()->id())->exists();
    }

    public function getDifficultyLabelAttribute()
    {
        return [
            'easy' => 'Лёгкий',
            'medium' => 'Средний',
            'hard' => 'Сложный',
        ][$this->difficulty] ?? $this->difficulty;
    }

    public function getDifficultyColorAttribute()
    {
        return [
            'easy' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'hard' => 'bg-red-100 text-red-800',
        ][$this->difficulty] ?? 'bg-gray-100 text-gray-800';
    }

    public function getRoadTypeLabelAttribute()
    {
        return [
            'asphalt' => 'Асфальт',
            'gravel' => 'Гравий',
            'offroad' => 'Бездорожье',
            'mixed' => 'Смешанный',
        ][$this->road_type] ?? $this->road_type;
    }

    public function getDurationFormattedAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours} ч. {$minutes} мин.";
        }

        return "{$minutes} мин.";
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')
                  ->orWhere('featured_until', '>', now());
            });
    }

    public function scopePopular($query)
    {
        return $query->orderBy('views_count', 'desc')
            ->orderBy('favorites_count', 'desc');
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }


    public function pointsOfInterest()
{
    // Если таблица называется points_of_interest
    return $this->hasMany(PointOfInterest::class, 'route_id');
    
    // Или если совсем другое имя
    // return $this->hasMany(\App\Models\PointOfInterest::class, 'route_id');
}

public function userQuests()
{
    // Если есть таблица quest_route (многие-ко-многим)
    return $this->belongsToMany(Quest::class, 'quest_route', 'route_id', 'quest_id')
        ->withPivot('order', 'is_required', 'verification_data');
}

public function getDurationHoursAttribute()
{
    return $this->attributes['duration_hours'] ?? ceil($this->duration_minutes / 60);
}
public function completions()
{
    return $this->hasMany(RouteCompletion::class);
}

// Также добавьте отношение для пользователя, прошедшего маршрут
public function completedByUsers()
{
    return $this->belongsToMany(User::class, 'route_completions')
                ->withPivot(['photo_path', 'completed_at'])
                ->withTimestamps();
}

// Проверка, прошел ли конкретный пользователь маршрут
public function isCompletedByUser($userId = null)
{
    if (!$userId && auth()->check()) {
        $userId = auth()->id();
    }
    
    if (!$userId) {
        return false;
    }
    
    return $this->completions()->where('user_id', $userId)->exists();
}

// Количество прохождений
public function getCompletionsCountAttribute()
{
    return $this->completions()->count();
}

}