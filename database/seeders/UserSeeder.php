<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@autoruta.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'), // Измените пароль
            'role' => 'admin',
            'level' => 10,
            'experience' => 5000,
            'is_verified' => true,
        ]);

        // Создаем несколько тестовых пользователей
        User::factory(10)->create([
            'role' => 'user',
            'level' => rand(1, 5),
            'experience' => rand(100, 2000),
        ]);

        // Создаем модератора
        User::create([
            'name' => 'Модератор',
            'email' => 'moderator@autoruta.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'moderator',
            'level' => 8,
            'experience' => 3000,
            'is_verified' => true,
        ]);
    }
}