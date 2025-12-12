<?php

namespace Database\Seeders;

use App\Models\Quest;
use App\Models\QuestBadge;
use Illuminate\Database\Seeder;

class QuestSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем существующие значки или создаем дефолтный
        $badges = QuestBadge::all();
        
        if ($badges->isEmpty()) {
            // Если таблица пуста, создаем дефолтный значок
            $badge = QuestBadge::create([
                'name' => 'Базовый значок',
                'slug' => 'basic-badge',
                'icon' => 'fas fa-medal',
                'color' => '#FF7A45',
                'description' => 'Базовый значок для квестов',
                'rarity' => 1,
            ]);
            $badgeId = $badge->id;
        } else {
            // Берем случайный существующий значок или NULL
            $badgeId = $badges->random()->id;
        }

        $quests = [
            [
                'title' => 'Первое путешествие',
                'slug' => 'first-journey',
                'description' => 'Начните свое первое путешествие на AutoRuta',
                'short_description' => 'Пройдите свой первый маршрут',
                'type' => 'collection',
                'difficulty' => 'easy',
                'reward_exp' => 100,
                'reward_coins' => 50,
                'badge_id' => $badgeId, // Используем реальный или NULL
                'requirements' => json_encode([
                    ['type' => 'min_level', 'value' => 1]
                ]),
                'is_active' => true,
                'is_featured' => true,
                'is_repeatable' => false,
                'max_completions' => 1,
                'color' => '#10B981',
            ],
            [
                'title' => 'Исследователь недели',
                'slug' => 'weekly-explorer',
                'description' => 'Пройдите 3 различных маршрута за неделю',
                'short_description' => 'Исследуйте новые места каждую неделю',
                'type' => 'challenge',
                'difficulty' => 'medium',
                'reward_exp' => 300,
                'reward_coins' => 150,
                'badge_id' => $badges->isNotEmpty() ? $badges->random()->id : null,
                'requirements' => json_encode([
                    ['type' => 'min_level', 'value' => 3],
                    ['type' => 'completed_quests', 'value' => 1]
                ]),
                'is_active' => true,
                'is_featured' => false,
                'is_repeatable' => true,
                'max_completions' => null,
                'color' => '#3B82F6',
            ],
            [
                'title' => 'Мастер горных дорог',
                'slug' => 'mountain-master',
                'description' => 'Покорите 5 горных маршрутов',
                'short_description' => 'Пройдите сложные горные трассы',
                'type' => 'challenge',
                'difficulty' => 'hard',
                'reward_exp' => 500,
                'reward_coins' => 250,
                'badge_id' => $badges->isNotEmpty() ? $badges->random()->id : null,
                'requirements' => json_encode([
                    ['type' => 'min_level', 'value' => 5],
                    ['type' => 'completed_quests', 'value' => 5]
                ]),
                'is_active' => true,
                'is_featured' => true,
                'is_repeatable' => false,
                'max_completions' => 1,
                'color' => '#EF4444',
            ],
        ];

        foreach ($quests as $quest) {
            // Убедимся, что badge_id существует или установим NULL
            if ($quest['badge_id'] && !QuestBadge::where('id', $quest['badge_id'])->exists()) {
                $quest['badge_id'] = null;
            }
            
            Quest::create($quest);
        }
    }
}