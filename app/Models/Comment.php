<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content',
        'user_id',
        'parent_id',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    protected $appends = ['liked_by_auth_user'];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'comment_likes')->withTimestamps();
    }

    public function getLikedByAuthUserAttribute()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    public function toggleLike($userId)
    {
        $liked = $this->likes()->where('user_id', $userId)->exists();
        
        if ($liked) {
            $this->likes()->detach($userId);
            $this->decrement('likes_count');
        } else {
            $this->likes()->attach($userId);
            $this->increment('likes_count');
        }
        
        return !$liked;
    }
}