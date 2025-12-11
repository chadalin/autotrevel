<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Route;
use App\Models\Tag;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем теги
        $tags = [
            ['name' => 'горы', 'slug' => 'mountains', 'color' => '#EF4444', 'icon' => '⛰️'],
            ['name' => 'озера', 'slug' => 'lakes', 'color' => '#3B82F6', 'icon' => '🏞️'],
            ['name' => 'леса', 'slug' => 'forests', 'color' => '#10B981', 'icon' => '🌲'],
            ['name' => 'история', 'slug' => 'history', 'color' => '#8B5CF6', 'icon' => '🏛️'],
            ['name' => 'фото', 'slug' => 'photo', 'color' => '#EC4899', 'icon' => '📸'],
            ['name' => 'бездорожье', 'slug' => 'offroad', 'color' => '#F59E0B', 'icon' => '🚙'],
            ['name' => 'семейный', 'slug' => 'family', 'color' => '#6366F1', 'icon' => '👨‍👩‍👧‍👦'],
            ['name' => 'гастрономия', 'slug' => 'food', 'color' => '#F97316', 'icon' => '🍴'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }

        // Создаем тестового пользователя
        $user = User::create([
            'name' => 'Иван Путешественник',
            'email' => 'test@autoruta.ru',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Создаем тестовые маршруты
        $routes = [
            [
                'title' => 'Золотое кольцо России',
                'description' => 'Классический маршрут по древним русским городам с богатой историей и архитектурой.',
                'length_km' => 750,
                'duration_minutes' => 1200,
                'difficulty' => 'easy',
                'road_type' => 'asphalt',
                'scenery_rating' => 4.5,
                'road_quality_rating' => 4.0,
                'safety_rating' => 4.8,
                'infrastructure_rating' => 4.7,
                'is_published' => true,
                'is_featured' => true,
                'start_coordinates' => [56.130886, 40.409491], // Владимир
                'end_coordinates' => [57.767565, 40.926895],   // Ярославль
            ],
            [
                'title' => 'Кавказские перевалы',
                'description' => 'Живописный маршрут через горные перевалы Кавказа с потрясающими видами.',
                'length_km' => 450,
                'duration_minutes' => 480,
                'difficulty' => 'hard',
                'road_type' => 'mixed',
                'scenery_rating' => 4.9,
                'road_quality_rating' => 3.5,
                'safety_rating' => 4.0,
                'infrastructure_rating' => 3.8,
                'is_published' => true,
                'is_featured' => true,
                'start_coordinates' => [43.585525, 39.723062], // Сочи
                'end_coordinates' => [43.296482, 42.460246],   // Приэльбрусье
            ],
        ];

        foreach ($routes as $routeData) {
            $route = Route::create(array_merge($routeData, [
                'user_id' => $user->id,
                'slug' => \Illuminate\Support\Str::slug($routeData['title']),
                'short_description' => Str::limit($routeData['description'], 100),
            ]));

            // Привязываем случайные теги
            $route->tags()->attach(Tag::inRandomOrder()->limit(3)->pluck('id'));
        }
    }
}