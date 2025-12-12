<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'route_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user')
            ->withPivot('joined_at', 'last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function route()
{
    return $this->belongsTo(\App\Models\Route::class, 'route_id');
}

    public function getUnreadCountAttribute($userId)
    {
        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>', function($query) use ($userId) {
                $query->select('last_read_at')
                    ->from('chat_user')
                    ->where('chat_id', $this->id)
                    ->where('user_id', $userId);
            })
            ->count();
    }

    public function addParticipant($userId)
    {
        $this->users()->syncWithoutDetaching([$userId => ['joined_at' => now()]]);
    }

    public function removeParticipant($userId)
    {
        $this->users()->detach($userId);
    }
}