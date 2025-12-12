<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $activities = [
            'Пользователь вошел в систему',
            'Пользователь создал новый маршрут',
            'Пользователь добавил отзыв',
            'Администратор проверил маршрут',
            'Пользователь завершил квест',
            'Новый пользователь зарегистрировался',
            'Маршрут добавлен в избранное',
            'Обновлен профиль пользователя',
        ];
        
        for ($i = 0; $i < 50; $i++) {
            ActivityLog::create([
                'log_name' => 'system',
                'description' => fake()->randomElement($activities),
                'subject_type' => null,
                'subject_id' => null,
                'causer_type' => User::class,
                'causer_id' => $users->random()->id,
                'properties' => ['ip' => fake()->ipv4()],
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }
}