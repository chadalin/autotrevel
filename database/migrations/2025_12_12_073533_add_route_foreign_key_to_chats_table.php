<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Просто добавляем внешний ключ
        Schema::table('chats', function (Blueprint $table) {
            $table->foreign('route_id')
                  ->references('id')
                  ->on('travel_routes')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['chats_route_id_foreign']);
        });
    }
};