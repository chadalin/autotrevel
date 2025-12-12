<?php

namespace Database\Seeders;

use App\Models\QuestBadge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Новичок',
                'slug' => 'novice',
                'icon' => 'fas fa-seedling',
                'color' => '#4CAF50',
                'description' => 'Пройдите свой первый маршрут',
                'rarity' => 1,
            ],
            [
                'name' => 'Исследователь',
                'slug' => 'explorer',
                'icon' => 'fas fa-compass',
                'color' => '#2196F3',
                'description' => 'Пройдите 5 различных маршрутов',
                'rarity' => 2,
            ],
            [
                'name' => 'Мастер путешествий',
                'slug' => 'travel-master',
                'icon' => 'fas fa-crown',
                'color' => '#FFD700',
                'description' => 'Пройдите 20 маршрутов',
                'rarity' => 3,
            ],
            [
                'name' => 'Экстремал',
                'slug' => 'extremal',
                'icon' => 'fas fa-mountain',
                'color' => '#FF5722',
                'description' => 'Пройдите 3 сложных маршрута',
                'rarity' => 4,
            ],
            [
                'name' => 'Легенда',
                'slug' => 'legend',
                'icon' => 'fas fa-trophy',
                'color' => '#9C27B0',
                'description' => 'Достигните 10 уровня',
                'rarity' => 5,
            ],
        ];

        foreach ($badges as $badge) {
            QuestBadge::create($badge);
        }
    }
}