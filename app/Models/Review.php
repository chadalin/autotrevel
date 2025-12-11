<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'route_id',
        'comment',
        'scenery_rating',
        'road_quality_rating',
        'safety_rating',
        'infrastructure_rating',
    ];

    protected $casts = [
        'scenery_rating' => 'float',
        'road_quality_rating' => 'float',
        'safety_rating' => 'float',
        'infrastructure_rating' => 'float',
    ];

    protected $appends = ['average_rating'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function getAverageRatingAttribute()
    {
        $ratings = [
            $this->scenery_rating,
            $this->road_quality_rating,
            $this->safety_rating,
            $this->infrastructure_rating,
        ];

        return round(array_sum($ratings) / count($ratings), 1);
    }
}