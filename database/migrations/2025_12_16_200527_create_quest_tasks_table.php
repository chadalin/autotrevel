<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quest_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'image', 'code', 'cipher', 'location', 'puzzle', 'quiz']);
            $table->json('content')->nullable();
            $table->integer('order')->default(0);
            $table->integer('points')->default(10);
            $table->integer('time_limit_minutes')->default(15);
            $table->integer('hints_available')->default(3);
            $table->string('required_answer')->nullable();
            $table->foreignId('next_task_id')->nullable()->constrained('quest_tasks')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('points_of_interest')->onDelete('set null');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            
            $table->index(['quest_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quest_tasks');
    }
};