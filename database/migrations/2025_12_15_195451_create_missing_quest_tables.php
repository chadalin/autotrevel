<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. route_sessions если не существует
        if (!Schema::hasTable('route_sessions')) {
            Schema::create('route_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
                $table->foreignId('quest_id')->nullable()->constrained()->onDelete('set null');
                $table->string('status')->default('active'); // active, paused, completed, cancelled
                $table->json('current_position')->nullable(); // текущая позиция {lat, lng}
                $table->json('checkpoints_visited')->nullable(); // посещенные чекпоинты
                $table->timestamp('started_at')->nullable();
                $table->timestamp('paused_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->integer('distance_traveled')->default(0); // в метрах
                $table->integer('duration_seconds')->default(0); // в секундах
                $table->timestamps();
            });
        }

        // 2. route_checkpoints если не существует
        if (!Schema::hasTable('route_checkpoints')) {
            Schema::create('route_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->integer('order')->default(0);
                $table->string('type')->default('checkpoint'); // checkpoint, viewpoint, stop, photo_point
                $table->string('secret_code')->nullable(); // код для подтверждения
                $table->timestamps();
            });
        }

        // 3. quest_proofs если не существует (доказательства выполнения квестов)
        if (!Schema::hasTable('quest_proofs')) {
            Schema::create('quest_proofs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('quest_id')->constrained()->onDelete('cascade');
                $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
                $table->string('type'); // photo, gpx, code
                $table->string('file_path')->nullable(); // путь к файлу
                $table->string('secret_code')->nullable(); // если тип code
                $table->text('comment')->nullable();
                $table->json('metadata')->nullable(); // дополнительные данные
                $table->boolean('approved')->default(false);
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 4. user_achievements если не существует (достижения пользователей)
        if (!Schema::hasTable('user_achievements')) {
            Schema::create('user_achievements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type'); // distance, routes_completed, quests_completed и т.д.
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('requirements')->nullable(); // требования для получения
                $table->json('progress')->nullable(); // текущий прогресс
                $table->boolean('unlocked')->default(false);
                $table->timestamp('unlocked_at')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'type', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('quest_proofs');
        Schema::dropIfExists('route_checkpoints');
        Schema::dropIfExists('route_sessions');
    }
};