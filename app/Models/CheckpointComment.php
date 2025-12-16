<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkpoint_id',
        'user_id',
        'content',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean'
    ];

    public function checkpoint()
    {
        return $this->belongsTo(RouteCheckpoint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}