<?php

namespace Database\Seeders;

use App\Models\Quest;
use App\Models\Badge;
use App\Models\Route;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class QuestRoutesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Создание квестов с маршрутами...');
        
        // Создаем теги для маршрутов
        $tags = [
            Tag::create(['name' => 'Горы', 'color' => '#3B82F6']),
            Tag::create(['name' => 'Озеро', 'color' => '#10B981']),
            Tag::create(['name' => 'Лес', 'color' => '#059669']),
            Tag::create(['name' => 'Исторический', 'color' => '#8B5CF6']),
            Tag::create(['name' => 'Живописный', 'color' => '#EC4899']),
        ];
        
        // Создаем значки для квестов
        $badges = [
            Badge::create([
                'name' => 'Первопроходец',
                'description' => 'Завершите свой первый квест',
                'icon' => 'fas fa-compass',
                'rarity' => 'common',
                'color' => '#3B82F6',
            ]),
            Badge::create([
                'name' => 'Искатель приключений',
                'description' => 'Пройдите горные маршруты',
                'icon' => 'fas fa-mountain',
                'rarity' => 'rare',
                'color' => '#10B981',
            ]),
            Badge::create([
                'name' => 'Мастер дорог',
                'description' => 'Пройдите сложные маршруты',
                'icon' => 'fas fa-road',
                'rarity' => 'epic',
                'color' => '#8B5CF6',
            ]),
        ];
        
        // Создаем маршруты если их нет
        if (Route::count() == 0) {
            $this->command->info('Создание тестовых маршрутов...');
            
            $routes = [
                Route::create([
                    'title' => 'Золотое кольцо Алтая',
                    'slug' => 'golden-ring-altay',
                    'description' => 'Красивый маршрут по предгорьям Алтая с потрясающими видами на горные хребты и чистейшие озера.',
                    'short_description' => 'Живописный маршрут по Алтайским горам',
                    'user_id' => 1,
                    'start_lat' => 51.958102,
                    'start_lng' => 85.960323,
                    'end_lat' => 52.000000,
                    'end_lng' => 86.000000,
                    'length_km' => 450,
                    'duration_minutes' => 480,
                    'difficulty' => 'medium',
                    'road_type' => 'mixed',
                    'views_count' => 150,
                    'favorites_count' => 45,
                    'completions_count' => 23,
                    'cover_image' => 'routes/covers/altay.jpg',
                ]),
                
                Route::create([
                    'title' => 'Байкальская кругосветка',
                    'slug' => 'baikal-round',
                    'description' => 'Путешествие вокруг самого глубокого озера в мире. Уникальная природа и культурные достопримечательности.',
                    'short_description' => 'Путешествие вокруг озера Байкал',
                    'user_id' => 1,
                    'start_lat' => 52.286974,
                    'start_lng' => 104.305018,
                    'end_lat' => 52.286974,
                    'end_lng' => 104.305018,
                    'length_km' => 2100,
                    'duration_minutes' => 2880,
                    'difficulty' => 'hard',
                    'road_type' => 'asphalt',
                    'views_count' => 280,
                    'favorites_count' => 89,
                    'completions_count' => 34,
                    'cover_image' => 'routes/covers/baikal.jpg',
                ]),
                
                Route::create([
                    'title' => 'Карельские озера',
                    'slug' => 'karelian-lakes',
                    'description' => 'Маршрут по земле тысячи озер. Хвойные леса, гранитные скалы и кристально чистые водоемы.',
                    'short_description' => 'Путешествие по озерам Карелии',
                    'user_id' => 1,
                    'start_lat' => 61.785097,
                    'start_lng' => 34.346878,
                    'end_lat' => 62.000000,
                    'end_lng' => 34.500000,
                    'length_km' => 320,
                    'duration_minutes' => 360,
                    'difficulty' => 'easy',
                    'road_type' => 'asphalt',
                    'views_count' => 120,
                    'favorites_count' => 37,
                    'completions_count' => 18,
                    'cover_image' => 'routes/covers/karelia.jpg',
                ]),
                
                Route::create([
                    'title' => 'Крымские горы',
                    'slug' => 'crimean-mountains',
                    'description' => 'Восхождение на горные вершины Крыма с панорамными видами на Черное море.',
                    'short_description' => 'Горный маршрут по Крыму',
                    'user_id' => 1,
                    'start_lat' => 44.495194,
                    'start_lng' => 34.166598,
                    'end_lat' => 44.600000,
                    'end_lng' => 34.300000,
                    'length_km' => 180,
                    'duration_minutes' => 240,
                    'difficulty' => 'medium',
                    'road_type' => 'mixed',
                    'views_count' => 95,
                    'favorites_count' => 28,
                    'completions_count' => 15,
                    'cover_image' => 'routes/covers/crimea.jpg',
                ]),
                
                Route::create([
                    'title' => 'Уральские перевалы',
                    'slug' => 'ural-passes',
                    'description' => 'Преодоление горных перевалов Уральского хребта. Суровая природа и технически сложные участки.',
                    'short_description' => 'Сложный горный маршрут по Уралу',
                    'user_id' => 1,
                    'start_lat' => 56.838011,
                    'start_lng' => 60.597474,
                    'end_lat' => 57.000000,
                    'end_lng' => 60.800000,
                    'length_km' => 280,
                    'duration_minutes' => 320,
                    'difficulty' => 'hard',
                    'road_type' => 'gravel',
                    'views_count' => 75,
                    'favorites_count' => 22,
                    'completions_count' => 9,
                    'cover_image' => 'routes/covers/ural.jpg',
                ]),
                
                Route::create([
                    'title' => 'Самый простой маршрут для новичков',
                    'slug' => 'easy-beginner-route',
                    'description' => 'Идеальный маршрут для первого путешествия. Простая дорога, красивые виды и удобные остановки.',
                    'short_description' => 'Простой маршрут для начинающих',
                    'user_id' => 1,
                    'start_lat' => 55.7558,
                    'start_lng' => 37.6173,
                    'end_lat' => 55.8000,
                    'end_lng' => 37.7000,
                    'length_km' => 80,
                    'duration_minutes' => 90,
                    'difficulty' => 'easy',
                    'road_type' => 'asphalt',
                    'views_count' => 50,
                    'favorites_count' => 15,
                    'completions_count' => 8,
                    'cover_image' => 'routes/covers/beginner.jpg',
                ]),
            ];
            
            // Привязываем теги к маршрутам
            foreach ($routes as $route) {
                $route->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('id')->toArray()
                );
            }
        } else {
            $routes = Route::all();
        }
        
        // Создаем квесты
        $quests = [
            Quest::create([
                'title' => 'Первое путешествие',
                'slug' => 'first-journey',
                'short_description' => 'Идеальный квест для начала вашего пути',
                'description' => 'Этот квест создан специально для новичков. Вам нужно проехать простой маршрут, чтобы получить первый опыт и понять основы работы с квестами. Не беспокойтесь о сложностях - мы подготовили для вас самый легкий маршрут с подробным описанием.',
                'type' => 'learning',
                'difficulty' => 'easy',
                'min_level' => 1,
                'reward_exp' => 100,
                'reward_coins' => 50,
                'badge_id' => $badges[0]->id,
                'is_active' => true,
                'is_repeatable' => true,
                'conditions' => ['Проехать маршрут полностью', 'Сделать фото на старте', 'Отметить точку финиша'],
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ]),
            
            Quest::create([
                'title' => 'Горный перевал',
                'slug' => 'mountain-pass',
                'short_description' => 'Покорите горные маршруты',
                'description' => 'Сложный маршрут через горы и перевалы. Этот квест потребует от вас навыков вождения по горной местности. Вы увидите потрясающие панорамы, преодолеете серпантины и испытаете свои силы. Рекомендуется для опытных путешественников.',
                'type' => 'challenge',
                'difficulty' => 'hard',
                'min_level' => 3,
                'reward_exp' => 300,
                'reward_coins' => 150,
                'badge_id' => $badges[1]->id,
                'is_active' => true,
                'is_repeatable' => false,
                'conditions' => ['Преодолеть все перевалы', 'Сделать фото на высшей точке', 'Записать GPS трек'],
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
            ]),
            
            Quest::create([
                'title' => 'Озерное трио',
                'slug' => 'lake-trio',
                'short_description' => 'Посетите три самых красивых озера',
                'description' => 'В этом квесте вам предстоит посетить три уникальных озера, каждое из которых имеет свою особенность. От кристально чистых вод до исторических мест вокруг - этот маршрут поразит вас красотой природы.',
                'type' => 'collection',
                'difficulty' => 'medium',
                'min_level' => 2,
                'reward_exp' => 200,
                'reward_coins' => 100,
                'badge_id' => null,
                'is_active' => true,
                'is_repeatable' => true,
                'conditions' => ['Посетить все три озера', 'Сделать фото у каждого озера', 'Найти секретные точки'],
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
            ]),
            
            Quest::create([
                'title' => 'Исторический тур',
                'slug' => 'historical-tour',
                'short_description' => 'Путешествие по историческим местам',
                'description' => 'Погрузитесь в историю, посещая древние крепости, монастыри и музеи под открытым небом. Этот квест сочетает в себе красоту природы и богатство культурного наследия.',
                'type' => 'learning',
                'difficulty' => 'medium',
                'min_level' => 2,
                'reward_exp' => 180,
                'reward_coins' => 90,
                'badge_id' => null,
                'is_active' => true,
                'is_repeatable' => true,
                'conditions' => ['Посетить все исторические объекты', 'Собрать коды на локациях', 'Написать отзыв о каждом месте'],
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(4),
            ]),
            
            Quest::create([
                'title' => 'Экстремальный уикенд',
                'slug' => 'extreme-weekend',
                'short_description' => 'Экстремальный маршрут на выходные',
                'description' => 'Всего два дня, но какие! Этот интенсивный маршрут создан для тех, кто хочет получить максимум впечатлений за короткое время. Готовьтесь к адреналину и незабываемым видам!',
                'type' => 'weekend',
                'difficulty' => 'hard',
                'min_level' => 4,
                'reward_exp' => 250,
                'reward_coins' => 120,
                'badge_id' => $badges[2]->id,
                'is_active' => true,
                'is_repeatable' => false,
                'conditions' => ['Завершить за 48 часов', 'Пройти все контрольные точки', 'Снять видео проезда'],
                'start_date' => now(),
                'end_date' => now()->addWeeks(2),
            ]),
        ];
        
        // Привязываем маршруты к квестам с порядком
        $this->command->info('Привязка маршрутов к квестам...');
        
        // Квест 1: Первое путешествие (1 маршрут)
        $quests[0]->routes()->attach([
            $routes[5]->id => ['order' => 1] // Самый простой маршрут
        ]);
        
        // Квест 2: Горный перевал (3 маршрута)
        $quests[1]->routes()->attach([
            $routes[0]->id => ['order' => 1], // Алтай
            $routes[3]->id => ['order' => 2], // Крым
            $routes[4]->id => ['order' => 3], // Урал
        ]);
        
        // Квест 3: Озерное трио (3 маршрута с озерами)
        $quests[2]->routes()->attach([
            $routes[1]->id => ['order' => 1], // Байкал
            $routes[2]->id => ['order' => 2], // Карелия
            $routes[0]->id => ['order' => 3], // Алтай (там тоже есть озера)
        ]);
        
        // Квест 4: Исторический тур (2 маршрута)
        $quests[3]->routes()->attach([
            $routes[3]->id => ['order' => 1], // Крым (исторические места)
            $routes[1]->id => ['order' => 2], // Байкал (исторические поселения)
        ]);
        
        // Квест 5: Экстремальный уикенд (2 сложных маршрута)
        $quests[4]->routes()->attach([
            $routes[4]->id => ['order' => 1], // Урал (сложный)
            $routes[0]->id => ['order' => 2], // Алтай (сложные участки)
        ]);
        
        $this->command->info('✅ Создано квестов: ' . count($quests));
        $this->command->info('✅ Создано маршрутов: ' . count($routes));
        $this->command->info('✅ Все маршруты привязаны к квестам');
        
        // Создаем тестового пользователя если его нет
        if (\App\Models\User::count() == 0) {
            $this->command->info('Создание тестового пользователя...');
            
            \App\Models\User::create([
                'name' => 'Тестовый Пользователь',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'exp' => 50,
                'level' => 2,
                'coins' => 100,
            ]);
            
            $this->command->info('✅ Создан тестовый пользователь: test@example.com / password');
        }
    }
}