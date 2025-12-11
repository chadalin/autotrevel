<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['available', 'in_progress', 'completed', 'failed'])->default('available');
            $table->integer('progress_current')->default(0);
            $table->integer('progress_target')->default(1);
            $table->json('completed_data')->nullable(); // Данные о выполнении
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('attempts_count')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'quest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_quests');
    }
};