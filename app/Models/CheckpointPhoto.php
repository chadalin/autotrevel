<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckpointPhoto extends Model
{
    protected $fillable = [
        'checkpoint_id',
        'path',
        'uploaded_by'
    ];
    
    public function checkpoint()
    {
        return $this->belongsTo(RouteCheckpoint::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}