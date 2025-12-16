<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quest_task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quest_id')->constrained('quests')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('quest_tasks')->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'skipped', 'paused'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('attempts')->default(0);
            $table->text('user_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->json('hints_used')->nullable();
            $table->integer('penalty_points')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'quest_id', 'task_id']);
            $table->index(['user_id', 'quest_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quest_task_progress');
    }
};