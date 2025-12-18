<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNavigationFieldsToRouteSessions extends Migration
{
    public function up()
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('route_sessions', 'checkpoints_visited')) {
                $table->json('checkpoints_visited')->nullable()->after('current_position');
            }
            if (!Schema::hasColumn('route_sessions', 'distance_traveled')) {
                $table->integer('distance_traveled')->default(0)->after('quest_id');
            }
            if (!Schema::hasColumn('route_sessions', 'duration_seconds')) {
                $table->integer('duration_seconds')->default(0)->after('distance_traveled');
            }
            if (!Schema::hasColumn('route_sessions', 'average_speed')) {
                $table->decimal('average_speed', 5, 2)->nullable()->after('duration_seconds');
            }
            if (!Schema::hasColumn('route_sessions', 'total_distance')) {
                $table->decimal('total_distance', 8, 2)->nullable()->after('average_speed');
            }
            if (!Schema::hasColumn('route_sessions', 'earned_xp')) {
                $table->integer('earned_xp')->default(0)->after('total_distance');
            }
        });
    }

    public function down()
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'checkpoints_visited',
                'distance_traveled',
                'duration_seconds',
                'average_speed',
                'total_distance',
                'earned_xp'
            ]);
        });
    }
}