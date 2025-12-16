<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteCompletion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'route_id',
        'session_id',
        'quest_id',
        'completed_at',
        'duration_seconds',
        'total_distance',
        'proof_data',
        'gps_data',
        'photos',
        'comment',
        'rating',
        'review',
        'earned_xp',
        'earned_coins',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'proof_data' => 'array',
        'gps_data' => 'array',
        'photos' => 'array',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function session()
    {
        return $this->belongsTo(RouteSession::class);
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}