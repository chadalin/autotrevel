<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_exp')->default(0);
            $table->integer('total_coins')->default(0);
            $table->integer('quests_completed')->default(0);
            $table->integer('routes_completed')->default(0);
            $table->integer('distance_traveled')->default(0); // в км
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
        Schema::dropIfExists('user_stats');
    }
};