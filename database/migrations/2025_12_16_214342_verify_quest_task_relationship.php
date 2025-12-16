<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VerifyQuestTaskRelationship extends Migration
{
    public function up()
    {
        // Проверяем существование таблицы quest_tasks
        if (Schema::hasTable('quest_tasks')) {
            // Проверяем существование внешнего ключа quest_id
            Schema::table('quest_tasks', function (Blueprint $table) {
                // Если внешний ключ не существует, добавляем его
                if (!Schema::hasColumn('quest_tasks', 'quest_id')) {
                    $table->unsignedBigInteger('quest_id')->nullable();
                }
                
                // Индекс для quest_id
                $table->index('quest_id');
                
                // Внешний ключ к таблице quests
                $table->foreign('quest_id')
                      ->references('id')
                      ->on('quests')
                      ->onDelete('cascade');
            });
        }

        // Также проверяем связи в таблице quest_task_progress
        if (Schema::hasTable('quest_task_progress')) {
            Schema::table('quest_task_progress', function (Blueprint $table) {
                // Внешние ключи для quest_task_progress
                $table->foreign('task_id')
                      ->references('id')
                      ->on('quest_tasks')
                      ->onDelete('cascade');
                
                $table->foreign('quest_id')
                      ->references('id')
                      ->on('quests')
                      ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // Удаляем внешние ключи если нужно откатить
        if (Schema::hasTable('quest_tasks')) {
            Schema::table('quest_tasks', function (Blueprint $table) {
                $table->dropForeign(['quest_id']);
                $table->dropIndex(['quest_id']);
            });
        }

        if (Schema::hasTable('quest_task_progress')) {
            Schema::table('quest_task_progress', function (Blueprint $table) {
                $table->dropForeign(['task_id']);
                $table->dropForeign(['quest_id']);
            });
        }
    }
}