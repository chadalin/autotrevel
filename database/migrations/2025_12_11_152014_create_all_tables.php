<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Пользователи
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->integer('level')->default(1);
            $table->integer('experience')->default(0);
            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // Добавлено для аутентификации
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Сессии
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 3. Маршруты
        Schema::create('travel_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('length_km', 8, 2);
            $table->integer('duration_minutes');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('road_type', ['asphalt', 'gravel', 'offroad', 'mixed'])->default('asphalt');
            $table->decimal('scenery_rating', 3, 1)->default(0);
            $table->decimal('road_quality_rating', 3, 1)->default(0);
            $table->decimal('safety_rating', 3, 1)->default(0);
            $table->decimal('infrastructure_rating', 3, 1)->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('completions_count')->default(0);
            $table->json('start_coordinates')->nullable();
            $table->json('end_coordinates')->nullable();
            $table->json('path_coordinates')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Теги
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('#6B7280');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // 5. Связь маршрутов и тегов
        Schema::create('route_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 6. Точки интереса
        Schema::create('points_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'viewpoint', 'cafe', 'hotel', 'attraction', 
                'gas_station', 'camping', 'photo_spot', 
                'nature', 'historical', 'other'
            ])->default('viewpoint');
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->integer('order')->default(0);
            $table->json('photos')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 7. Отзывы
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->text('comment')->nullable();
            $table->decimal('scenery_rating', 3, 1)->default(0);
            $table->decimal('road_quality_rating', 3, 1)->default(0);
            $table->decimal('safety_rating', 3, 1)->default(0);
            $table->decimal('infrastructure_rating', 3, 1)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'route_id']);
        });

        // 8. Сохранённые маршруты
        Schema::create('saved_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'route_id']);
        });

        // 9. Значки квестов (СНАЧАЛА!)
        Schema::create('quest_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_svg')->nullable();
            $table->string('color')->default('#FF7A45');
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 10. Квесты (ПОСЛЕ значков!)
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->enum('type', ['collection', 'challenge', 'weekend', 'story', 'user'])->default('collection');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert'])->default('medium');
            $table->integer('reward_exp')->default(100);
            $table->integer('reward_coins')->default(0);
            $table->foreignId('badge_id')->nullable()->constrained('quest_badges')->onDelete('set null');
            $table->json('conditions')->nullable();
            $table->json('requirements')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_repeatable')->default(false);
            $table->integer('max_completions')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#FF7A45');
            $table->timestamps();
            $table->softDeletes();
        });

        // 11. Связь квестов и маршрутов
        Schema::create('quest_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('verification_data')->nullable();
            $table->timestamps();
            $table->unique(['quest_id', 'route_id']);
        });

        // 12. Квесты пользователей
        Schema::create('user_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['available', 'in_progress', 'completed', 'failed'])->default('available');
            $table->integer('progress_current')->default(0);
            $table->integer('progress_target')->default(1);
            $table->json('completed_data')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('attempts_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'quest_id']);
        });

        // 13. Значки пользователей
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('quest_badges')->onDelete('cascade');
            $table->timestamp('earned_at')->useCurrent();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'badge_id']);
        });

        // 14. Завершения квестов
        Schema::create('quest_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->json('proof_data')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 15. Статистика пользователей
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_exp')->default(0);
            $table->integer('total_coins')->default(0);
            $table->integer('quests_completed')->default(0);
            $table->integer('routes_completed')->default(0);
            $table->integer('distance_traveled')->default(0);
            $table->integer('days_active')->default(0);
            $table->json('achievements')->nullable();
            $table->json('weekly_stats')->nullable();
            $table->json('monthly_stats')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        // Удаляем в обратном порядке
        Schema::dropIfExists('user_stats');
        Schema::dropIfExists('quest_completions');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('user_quests');
        Schema::dropIfExists('quest_routes');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('quest_badges');
        Schema::dropIfExists('saved_routes');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('points_of_interest');
        Schema::dropIfExists('route_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('travel_routes');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};