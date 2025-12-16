<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'route_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Указываем, что модель не использует мягкое удаление
    public $timestamps = true;

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
        return $this->belongsTo(TravelRoute::class, 'route_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // Метод для получения непрочитанных сообщений
    public function scopeWithUnreadCount($query, $userId)
    {
        return $query->withCount(['messages as unread_messages_count' => function($q) use ($userId) {
            $q->where('user_id', '!=', $userId)
              ->whereNull('read_at');
        }]);
    }

    // Метод для чатов текущего пользователя
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('users', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // Метод для получения приватных чатов
    public function scopePrivate($query, $userId)
    {
        return $query->where('type', 'private')
            ->whereHas('users', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }

    // Метод для получения чатов маршрутов
    public function scopeRouteChats($query, $userId)
    {
        return $query->where('type', 'route')
            ->whereHas('users', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }

    // Метод для получения групповых чатов
    public function scopeGroup($query, $userId)
    {
        return $query->where('type', 'group')
            ->whereHas('users', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }
}