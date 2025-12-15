// database/migrations/xxxx_xx_xx_xxxxxx_add_fields_to_route_sessions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            // Проверяем, существуют ли поля перед добавлением
            if (!Schema::hasColumn('route_sessions', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('id');
            }
            
            if (!Schema::hasColumn('route_sessions', 'route_id')) {
                $table->foreignId('route_id')->constrained('travel_routes')->onDelete('cascade')->after('user_id');
            }
            
            if (!Schema::hasColumn('route_sessions', 'quest_id')) {
                $table->foreignId('quest_id')->nullable()->constrained()->onDelete('set null')->after('route_id');
            }
            
            if (!Schema::hasColumn('route_sessions', 'status')) {
                $table->string('status')->default('active')->after('quest_id');
            }
            
            if (!Schema::hasColumn('route_sessions', 'current_position')) {
                $table->json('current_position')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('route_sessions', 'checkpoints_visited')) {
                $table->json('checkpoints_visited')->nullable()->after('current_position');
            }
            
            if (!Schema::hasColumn('route_sessions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('checkpoints_visited');
            }
            
            if (!Schema::hasColumn('route_sessions', 'paused_at')) {
                $table->timestamp('paused_at')->nullable()->after('started_at');
            }
            
            if (!Schema::hasColumn('route_sessions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('paused_at');
            }
            
            if (!Schema::hasColumn('route_sessions', 'distance_traveled')) {
                $table->integer('distance_traveled')->default(0)->after('completed_at');
            }
            
            if (!Schema::hasColumn('route_sessions', 'duration_seconds')) {
                $table->integer('duration_seconds')->default(0)->after('distance_traveled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            // Удаляем только если они существуют
            $columns = [
                'user_id', 'route_id', 'quest_id', 'status', 
                'current_position', 'checkpoints_visited', 'started_at',
                'paused_at', 'completed_at', 'distance_traveled', 'duration_seconds'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('route_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};