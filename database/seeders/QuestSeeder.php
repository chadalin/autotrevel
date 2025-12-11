<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quest;
use App\Models\Route;

class QuestSeeder extends Seeder
{
    public function run(): void
    {
        $routes = Route::all();

        $quests = [
            [
                'title' => 'Собиратель красот',
                'description' => 'Посетите 3 самых живописных маршрута платформы. Докажите, что вы ценитель прекрасного!',
                'type' => 'collection',
                'difficulty' => 'medium',
                'reward_exp' => 300,
                'reward_coins' => 100,
                'badge_id' => 2, // исследователь
                'routes' => $routes->pluck('id')->take(3)->toArray(),
            ],
            [
                'title' => 'Испытание бездорожьем',
                'description' => 'Пройдите сложный горный маршрут. Только для опытных водителей!',
                'type' => 'challenge',
                'difficulty' => 'hard',
                'reward_exp' => 500,
                'reward_coins' => 200,
                'badge_id' => 3, // мастер дорог
                'routes' => [$routes->where('difficulty', 'hard')->first()->id],
            ],
            [
                'title' => 'Выходные на природе',
                'description' => 'Специальный квест на эти выходные! Отправляйтесь в путешествие по лесам и озёрам.',
                'type' => 'weekend',
                'difficulty' => 'easy',
                'reward_exp' => 150,
                'reward_coins' => 50,
                'badge_id' => null,
                'routes' => $routes->whereIn('difficulty', ['easy', 'medium'])->pluck('id')->take(2)->toArray(),
                'start_date' => now()->startOfDay(),
                'end_date' => now()->addDays(2)->endOfDay(),
            ],
        ];

        foreach ($quests as $questData) {
            $quest = Quest::create([
                'title' => $questData['title'],
                'slug' => \Illuminate\Support\Str::slug($questData['title']),
                'description' => $questData['description'],
                'short_description' => \Illuminate\Support\Str::limit($questData['description'], 150),
                'type' => $questData['type'],
                'difficulty' => $questData['difficulty'],
                'reward_exp' => $questData['reward_exp'],
                'reward_coins' => $questData['reward_coins'],
                'badge_id' => $questData['badge_id'] ?? null,
                'is_active' => true,
                'is_featured' => $questData['difficulty'] !== 'hard',
                'start_date' => $questData['start_date'] ?? null,
                'end_date' => $questData['end_date'] ?? null,
                'color' => $this->getColorByDifficulty($questData['difficulty']),
            ]);

            // Привязываем маршруты
            foreach ($questData['routes'] as $order => $routeId) {
                $quest->routes()->attach($routeId, [
                    'order' => $order + 1,
                    'is_required' => true,
                ]);
            }
        }
    }

    private function getColorByDifficulty($difficulty)
    {
        return [
            'easy' => '#10B981',
            'medium' => '#F59E0B',
            'hard' => '#EF4444',
            'expert' => '#8B5CF6',
        ][$difficulty] ?? '#6B7280';
    }
}