<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('#6B7280');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('route_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_tag');
        Schema::dropIfExists('tags');
    }
};