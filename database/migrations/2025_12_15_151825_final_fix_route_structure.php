<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Сначала создаем недостающие таблицы в правильном порядке
        $this->createTableIfNotExists('route_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('order')->default(0);
            $table->string('type')->default('checkpoint');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('route_id')->references('id')->on('travel_routes')->onDelete('cascade');
            $table->index(['route_id', 'order']);
        });
        
        // 1. Исправляем route_sessions - просто добавляем недостающие поля
        if (Schema::hasTable('route_sessions')) {
            Schema::table('route_sessions', function (Blueprint $table) {
                // Добавляем основные поля
                $this->addColumnIfNotExists($table, 'user_id', function() use ($table) {
                    $table->unsignedBigInteger('user_id')->after('id');
                });
                
                $this->addColumnIfNotExists($table, 'route_id', function() use ($table) {
                    $table->unsignedBigInteger('route_id')->after('user_id');
                });
                
                $this->addColumnIfNotExists($table, 'status', function() use ($table) {
                    $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])
                          ->default('active')
                          ->after('route_id');
                });
                
                $this->addColumnIfNotExists($table, 'quest_id', function() use ($table) {
                    $table->unsignedBigInteger('quest_id')->nullable()->after('status');
                });
                
                // Добавляем временные метки
                $timestamps = [
                    'started_at',
                    'paused_at', 
                    'completed_at',
                    'ended_at'
                ];
                
                foreach ($timestamps as $timestamp) {
                    $this->addColumnIfNotExists($table, $timestamp, function() use ($table, $timestamp) {
                        $table->timestamp($timestamp)->nullable();
                    });
                }
                
                // Добавляем статистические поля
                $this->addColumnIfNotExists($table, 'average_speed', function() use ($table) {
                    $table->decimal('average_speed', 5, 2)->nullable();
                });
                
                $this->addColumnIfNotExists($table, 'total_distance', function() use ($table) {
                    $table->decimal('total_distance', 8, 2)->nullable();
                });
                
                $this->addColumnIfNotExists($table, 'earned_xp', function() use ($table) {
                    $table->integer('earned_xp')->default(0);
                });
                
                // Добавляем current_checkpoint_id позже
            });
            
            // Добавляем foreign keys отдельно после создания всех полей
            $this->addForeignKeyIfNotExists('route_sessions', 'user_id', 'users', 'id', 'cascade');
            $this->addForeignKeyIfNotExists('route_sessions', 'route_id', 'travel_routes', 'id', 'cascade');
            $this->addForeignKeyIfNotExists('route_sessions', 'quest_id', 'quests', 'id', 'set null');
        }
        
        // 2. Добавляем индексы в route_checkpoints если их нет
        if (Schema::hasTable('route_checkpoints')) {
            $this->addIndexIfNotExists('route_checkpoints', ['route_id', 'order'], 'route_checkpoints_route_id_order_index');
            
            // Добавляем foreign key для point_id если его нет
            if (Schema::hasColumn('route_checkpoints', 'point_id')) {
                $this->addForeignKeyIfNotExists('route_checkpoints', 'point_id', 'points_of_interest', 'id', 'cascade');
            }
        }
        
        // 3. Теперь добавляем current_checkpoint_id в route_sessions
        if (Schema::hasTable('route_sessions') && Schema::hasTable('route_checkpoints')) {
            Schema::table('route_sessions', function (Blueprint $table) {
                $this->addColumnIfNotExists($table, 'current_checkpoint_id', function() use ($table) {
                    $table->unsignedBigInteger('current_checkpoint_id')->nullable()->after('ended_at');
                });
            });
            
            $this->addForeignKeyIfNotExists('route_sessions', 'current_checkpoint_id', 'route_checkpoints', 'id', 'set null');
        }
        
        // 4. Добавляем session_id в route_completions
        if (Schema::hasTable('route_completions')) {
            Schema::table('route_completions', function (Blueprint $table) {
                $this->addColumnIfNotExists($table, 'session_id', function() use ($table) {
                    $table->unsignedBigInteger('session_id')->nullable()->after('route_id');
                });
            });
            
            $this->addForeignKeyIfNotExists('route_completions', 'session_id', 'route_sessions', 'id', 'set null');
            
            // Добавляем foreign keys для существующих полей
            $this->addForeignKeyIfNotExists('route_completions', 'route_id', 'travel_routes', 'id', 'cascade');
            
            if (Schema::hasColumn('route_completions', 'quest_id')) {
                $this->addForeignKeyIfNotExists('route_completions', 'quest_id', 'quests', 'id', 'set null');
            }
        }
        
        // 5. Создаем таблицы если их нет
        $this->createTableIfNotExists('checkpoint_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checkpoint_id');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            
            $table->index(['checkpoint_id']);
            
            // Foreign keys - БЕЗ проверки, т.к. таблица route_checkpoints уже должна существовать
            $table->foreign('checkpoint_id')->references('id')->on('route_checkpoints')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
        
        $this->createTableIfNotExists('checkpoint_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checkpoint_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            
            $table->index(['checkpoint_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('checkpoint_id')->references('id')->on('route_checkpoints')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_comments');
        Schema::dropIfExists('checkpoint_photos');
        
        // Удаляем добавленные столбцы
        if (Schema::hasTable('route_sessions')) {
            Schema::table('route_sessions', function (Blueprint $table) {
                $columns = [
                    'user_id', 'route_id', 'status', 'quest_id',
                    'started_at', 'paused_at', 'completed_at', 'ended_at',
                    'current_checkpoint_id', 'average_speed', 'total_distance',
                    'earned_xp'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('route_sessions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        
        if (Schema::hasTable('route_completions')) {
            Schema::table('route_completions', function (Blueprint $table) {
                if (Schema::hasColumn('route_completions', 'session_id')) {
                    $table->dropColumn('session_id');
                }
            });
        }
    }
    
    // Вспомогательные методы
    
    private function addColumnIfNotExists($table, $columnName, $callback)
    {
        if (!Schema::hasColumn($table->getTable(), $columnName)) {
            $callback();
        }
    }
    
    private function addForeignKeyIfNotExists($tableName, $column, $referenceTable, $referenceColumn, $onDelete = 'cascade')
    {
        // Проверяем, есть ли foreign key
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$tableName, $column]);
        
        if (empty($foreignKeys) && 
            Schema::hasTable($tableName) && 
            Schema::hasColumn($tableName, $column) &&
            Schema::hasTable($referenceTable)) {
            
            Schema::table($tableName, function (Blueprint $table) use ($column, $referenceTable, $referenceColumn, $onDelete) {
                $table->foreign($column)->references($referenceColumn)->on($referenceTable)->onDelete($onDelete);
            });
        }
    }
    
    private function addIndexIfNotExists($tableName, $columns, $indexName = null)
    {
        if (!$indexName) {
            $indexName = $tableName . '_' . implode('_', $columns) . '_index';
        }
        
        // Проверяем, есть ли индекс
        $indexes = DB::select("
            SHOW INDEX FROM $tableName 
            WHERE Key_name = ?
        ", [$indexName]);
        
        if (empty($indexes) && Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }
    
    private function createTableIfNotExists($tableName, $callback)
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, $callback);
        }
    }
};