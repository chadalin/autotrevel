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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->json('proof_data')->nullable(); // фото, GPS треки и т.д.
            $table->text('comment')->nullable();
            $table->integer('rating')->nullable(); // оценка от 1 до 5
            $table->timestamps();
            
            $table->unique(['user_id', 'route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_completions');
    }
};