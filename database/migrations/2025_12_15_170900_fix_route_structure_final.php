<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Создаем таблицу badges (значки) - если еще не создана
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('icon')->nullable(); // Иконка FontAwesome
                $table->string('rarity')->default('common'); // common, rare, epic, legendary
                $table->string('color')->nullable(); // Цвет значка
                $table->timestamps();
            });
        }

        // 2. Создаем таблицу quests (квесты) - если еще не создана
        if (!Schema::hasTable('quests')) {
            Schema::create('quests', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('short_description')->nullable();
                $table->text('description')->nullable();
                $table->string('type')->default('collection'); // collection, challenge, weekend, learning
                $table->string('difficulty')->default('easy'); // easy, medium, hard, expert
                $table->integer('min_level')->default(1);
                $table->integer('reward_exp')->default(0);
                $table->integer('reward_coins')->default(0);
                $table->foreignId('badge_id')->nullable()->constrained('badges')->onDelete('set null');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_repeatable')->default(false);
                $table->json('conditions')->nullable(); // Условия выполнения
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->timestamps();
            });
        }

        // 3. Создаем таблицу quest_route (связь квестов с маршрутами) - если еще не создана
        if (!Schema::hasTable('quest_route')) {
            Schema::create('quest_route', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quest_id')->constrained('quests')->onDelete('cascade');
                $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
                $table->integer('order')->default(0);
                $table->timestamps();
                
                $table->unique(['quest_id', 'route_id']);
            });
        }

        // 4. Создаем таблицу user_quests (прогресс пользователей в квестах) - если еще не создана
        if (!Schema::hasTable('user_quests')) {
            Schema::create('user_quests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('quest_id')->constrained('quests')->onDelete('cascade');
                $table->string('status')->default('available'); // available, in_progress, completed, cancelled
                $table->integer('progress_current')->default(0);
                $table->integer('progress_target')->default(0);
                $table->json('completed_data')->nullable(); // ID пройденных маршрутов
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'quest_id']);
            });
        }

        // 5. Создаем таблицу user_badges (значки пользователей) - если еще не создана
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
                $table->timestamp('earned_at')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'badge_id']);
            });
        }

        // 6. Добавляем недостающие колонки в users таблицу
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Проверяем и добавляем exp если нет
                if (!Schema::hasColumn('users', 'exp')) {
                    $table->integer('exp')->default(0)->after('email_verified_at');
                }
                
                // Проверяем и добавляем level если нет
                if (!Schema::hasColumn('users', 'level')) {
                    $table->integer('level')->default(1)->after('exp');
                }
                
                // Проверяем и добавляем coins если нет
                if (!Schema::hasColumn('users', 'coins')) {
                    $table->integer('coins')->default(0)->after('level');
                }
                
                // Проверяем и добавляем bio если нет
                if (!Schema::hasColumn('users', 'bio')) {
                    $table->text('bio')->nullable()->after('coins');
                }
                
                // Проверяем и добавляем avatar если нет
                if (!Schema::hasColumn('users', 'avatar')) {
                    $table->string('avatar')->nullable()->after('bio');
                }
                
                // Проверяем и добавляем settings если нет
                if (!Schema::hasColumn('users', 'settings')) {
                    $table->json('settings')->nullable()->after('avatar');
                }
            });
        }

        // 7. Обновляем таблицу travel_routes если нужно (без зависимостей от end_lng)
        if (Schema::hasTable('travel_routes')) {
            Schema::table('travel_routes', function (Blueprint $table) {
                // Определяем позицию для добавления колонок
                $addAfter = 'updated_at'; // По умолчанию добавляем в конец
                
                // Проверяем наличие колонок для позиционирования
                if (Schema::hasColumn('travel_routes', 'end_lng')) {
                    $addAfter = 'end_lng';
                } elseif (Schema::hasColumn('travel_routes', 'end_longitude')) {
                    $addAfter = 'end_longitude';
                }
                
                // Добавляем coordinates если нет
                if (!Schema::hasColumn('travel_routes', 'coordinates')) {
                    $table->json('coordinates')->nullable()->after($addAfter);
                }
                
                // Добавляем road_quality если нет
                if (!Schema::hasColumn('travel_routes', 'road_quality')) {
                    $table->string('road_quality')->nullable()->after('coordinates');
                }
                
                // Добавляем elevation_gain если нет
                if (!Schema::hasColumn('travel_routes', 'elevation_gain')) {
                    $table->integer('elevation_gain')->nullable()->after('road_quality');
                }
                
                // Добавляем best_season если нет
                if (!Schema::hasColumn('travel_routes', 'best_season')) {
                    $table->string('best_season')->nullable()->after('elevation_gain');
                }
                
                // Добавляем cover_image если нет
                if (!Schema::hasColumn('travel_routes', 'cover_image')) {
                    $table->string('cover_image')->nullable()->after('best_season');
                }
            });
        }
    }

    public function down(): void
    {
        // В обратном порядке удаляем таблицы
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('user_quests');
        Schema::dropIfExists('quest_route');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('badges');
        
        // Удаляем добавленные колонки
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = ['exp', 'level', 'coins', 'bio', 'avatar', 'settings'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        
        if (Schema::hasTable('travel_routes')) {
            Schema::table('travel_routes', function (Blueprint $table) {
                $columns = ['coordinates', 'road_quality', 'elevation_gain', 'best_season', 'cover_image'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('travel_routes', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};