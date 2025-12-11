<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('length_km', 8, 2);
            $table->integer('duration_minutes');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('road_type', ['asphalt', 'gravel', 'offroad', 'mixed'])->default('asphalt');
            $table->decimal('scenery_rating', 3, 1)->default(0);
            $table->decimal('road_quality_rating', 3, 1)->default(0);
            $table->decimal('safety_rating', 3, 1)->default(0);
            $table->decimal('infrastructure_rating', 3, 1)->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('completions_count')->default(0);
            $table->json('start_coordinates')->nullable(); // [lat, lng]
            $table->json('end_coordinates')->nullable();   // [lat, lng]
            $table->json('path_coordinates')->nullable();  // [[lat,lng], ...]
            $table->string('cover_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_routes');
    }
};