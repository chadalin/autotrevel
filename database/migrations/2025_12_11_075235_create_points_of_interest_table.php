<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'viewpoint',      // Смотровая площадка
                'cafe',           // Кафе/ресторан
                'hotel',          // Отель/гостиница
                'attraction',     // Достопримечательность
                'gas_station',    // АЗС
                'camping',        // Кемпинг/стоянка
                'photo_spot',     // Место для фото
                'nature',         // Природный объект
                'historical',     // Исторический объект
                'other',          // Другое
            ])->default('viewpoint');
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->integer('order')->default(0);
            $table->json('photos')->nullable(); // Массив URL фото
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_of_interest');
    }
};