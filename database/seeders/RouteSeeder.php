<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\User;
use App\Models\Tag;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $tags = Tag::all();

        $routes = [
            [
                'title' => 'Золотое кольцо России',
                'description' => 'Классический маршрут по древним русским городам. Владимир, Суздаль, Ярославль, Ростов Великий.',
                'length_km' => 750,
                'duration_minutes' => 1200,
                'difficulty' => 'easy',
                'road_type' => 'asphalt',
                'scenery_rating' => 4.8,
                'road_quality_rating' => 4.5,
                'tags' => [4], // история
            ],
            [
                'title' => 'Кавказские горы',
                'description' => 'Живописный маршрут через горные перевалы Кавказа с потрясающими видами на Эльбрус.',
                'length_km' => 450,
                'duration_minutes' => 480,
                'difficulty' => 'hard',
                'road_type' => 'mixed',
                'scenery_rating' => 4.9,
                'road_quality_rating' => 3.5,
                'tags' => [1, 6], // горы, бездорожье
            ],
            [
                'title' => 'Озёра Карелии',
                'description' => 'Путешествие по самым красивым озёрам Карелии. Ладожское озеро, Валаам, Кижи.',
                'length_km' => 320,
                'duration_minutes' => 360,
                'difficulty' => 'medium',
                'road_type' => 'gravel',
                'scenery_rating' => 4.7,
                'road_quality_rating' => 3.8,
                'tags' => [2, 3], // озера, леса
            ],
        ];

        foreach ($routes as $index => $routeData) {
            $route = Route::create([
                'user_id' => $user->id,
                'title' => $routeData['title'],
                'slug' => \Illuminate\Support\Str::slug($routeData['title']) . '-' . ($index + 1),
                'description' => $routeData['description'],
                'short_description' => \Illuminate\Support\Str::limit($routeData['description'], 100),
                'length_km' => $routeData['length_km'],
                'duration_minutes' => $routeData['duration_minutes'],
                'difficulty' => $routeData['difficulty'],
                'road_type' => $routeData['road_type'],
                'scenery_rating' => $routeData['scenery_rating'],
                'road_quality_rating' => $routeData['road_quality_rating'],
                'safety_rating' => 4.0,
                'infrastructure_rating' => 3.5,
                'is_published' => true,
                'start_coordinates' => [55.7558 + ($index * 0.1), 37.6176 + ($index * 0.1)],
                'end_coordinates' => [55.8558 + ($index * 0.1), 37.7176 + ($index * 0.1)],
            ]);

            $route->tags()->attach($routeData['tags']);
        }
    }
}