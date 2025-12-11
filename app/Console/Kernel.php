<?php

protected function schedule(Schedule $schedule)
{
    // Генерация квестов на выходные каждую среду
    $schedule->command('quests:generate-weekend')->wednesdays()->at('10:00');
    
    // Проверка просроченных квестов ежедневно
    $schedule->command('quests:check-expired')->dailyAt('03:00');
    
    // Сброс недельной статистики
    $schedule->command('stats:reset-weekly')->weekly()->mondays()->at('00:00');
}