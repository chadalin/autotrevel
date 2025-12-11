<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'icon_svg',
        'color',
        'rarity',
        'sort_order',
    ];

    protected $appends = ['rarity_label', 'rarity_color'];

    public function quests()
    {
        return $this->hasMany(Quest::class, 'badge_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges', 'badge_id', 'user_id')
            ->withTimestamps()
            ->withPivot('earned_at', 'metadata');
    }

    public function getRarityLabelAttribute()
    {
        $labels = [
            'common' => 'Обычный',
            'rare' => 'Редкий',
            'epic' => 'Эпический',
            'legendary' => 'Легендарный',
        ];

        return $labels[$this->rarity] ?? $this->rarity;
    }

    public function getRarityColorAttribute()
    {
        $colors = [
            'common' => 'text-gray-600 bg-gray-100',
            'rare' => 'text-blue-600 bg-blue-100',
            'epic' => 'text-purple-600 bg-purple-100',
            'legendary' => 'text-yellow-600 bg-yellow-100',
        ];

        return $colors[$this->rarity] ?? 'text-gray-600 bg-gray-100';
    }

    public function getEarnedByUser(User $user)
    {
        return $user->badges()->where('badge_id', $this->id)->exists();
    }
}