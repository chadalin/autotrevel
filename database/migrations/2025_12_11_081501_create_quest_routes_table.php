<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('verification_data')->nullable(); // Данные для верификации
            $table->timestamps();
            
            $table->unique(['quest_id', 'route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_routes');
    }
};