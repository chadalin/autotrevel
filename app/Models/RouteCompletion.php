<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'route_id',
        'quest_id',
        'photo_path',
        'verification_data',
        'verified',
        'verification_notes',
        'completed_at',
    ];

    protected $casts = [
        'verification_data' => 'array',
        'verified' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    public function getCoordinatesAttribute()
    {
        return $this->verification_data['coordinates'] ?? null;
    }

    public function getAccuracyAttribute()
    {
        return $this->verification_data['accuracy'] ?? null;
    }
}