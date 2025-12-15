<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'rarity',
        'color',
    ];
    
    public function getRarityLabelAttribute(): string
    {
        return match($this->rarity) {
            'common' => 'Обычный',
            'rare' => 'Редкий',
            'epic' => 'Эпический',
            'legendary' => 'Легендарный',
            default => 'Обычный',
        };
    }
    
    public function getRarityColorAttribute(): string
    {
        return match($this->rarity) {
            'common' => 'bg-gray-100 text-gray-800',
            'rare' => 'bg-blue-100 text-blue-800',
            'epic' => 'bg-purple-100 text-purple-800',
            'legendary' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}