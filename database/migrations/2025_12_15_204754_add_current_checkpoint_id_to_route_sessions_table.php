// database/migrations/xxxx_xx_xx_xxxxxx_add_current_checkpoint_id_to_route_sessions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('route_sessions', 'current_checkpoint_id')) {
                $table->foreignId('current_checkpoint_id')
                    ->nullable()
                    ->constrained('route_checkpoints')
                    ->onDelete('set null')
                    ->after('quest_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('route_sessions', 'current_checkpoint_id')) {
                $table->dropForeign(['current_checkpoint_id']);
                $table->dropColumn('current_checkpoint_id');
            }
        });
    }
};