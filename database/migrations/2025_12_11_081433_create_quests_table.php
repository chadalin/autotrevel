<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->json('conditions')->nullable(); // Условия выполнения
            $table->json('requirements')->nullable(); // Требования для начала
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_repeatable')->default(false);
            $table->integer('max_completions')->nullable(); // Максимальное количество выполнений
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#FF7A45');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quests');
    }
};