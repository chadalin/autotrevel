<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_completions', function (Blueprint $table) {
            $table->id();
            
            // Основные связи
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('quest_id')->nullable();
            
            // Основные данные
            $table->timestamp('completed_at');
            $table->integer('duration_seconds')->nullable();
            $table->decimal('total_distance', 8, 2)->nullable();
            
            // Доказательства и проверка
            $table->json('proof_data')->nullable();
            $table->json('gps_data')->nullable();
            $table->json('photos')->nullable();
            $table->text('comment')->nullable();
            
            // Рейтинг и отзыв
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();
            
            // Статистика
            $table->integer('earned_xp')->default(0);
            $table->integer('earned_coins')->default(0);
            
            // Статус
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                  ->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы
            $table->index(['user_id', 'route_id']);
            $table->index(['route_id', 'completed_at']);
            $table->index(['user_id', 'completed_at']);
            $table->index('verification_status');
            
            // Внешние ключи
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('travel_routes')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('route_sessions')->onDelete('set null');
            $table->foreign('quest_id')->references('id')->on('quests')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_completions');
    }
};