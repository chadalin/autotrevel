<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('points_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('viewpoint'); // viewpoint, cafe, hotel, attraction, etc.
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->integer('order')->default(0);
            $table->string('photo')->nullable();
            $table->timestamps();
            
            $table->index(['route_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('points_of_interest');
    }
};