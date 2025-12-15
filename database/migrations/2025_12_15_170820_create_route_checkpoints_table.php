<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('order')->default(0);
            $table->string('type')->default('checkpoint'); // checkpoint, viewpoint, stop
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_checkpoints');
    }
};