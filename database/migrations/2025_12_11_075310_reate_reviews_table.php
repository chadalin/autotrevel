<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};