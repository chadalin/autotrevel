<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
           // BadgeSeeder::class,
            RouteSeeder::class,
            QuestSeeder::class,
             // Сидеры для квестов и связей с маршрутами
            QuestRoutesSeeder::class,
        ]);
    }
}