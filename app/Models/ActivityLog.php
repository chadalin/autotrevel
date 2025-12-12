<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];
    
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function causer()
    {
        return $this->morphTo();
    }
    
    public function subject()
    {
        return $this->morphTo();
    }
    
    /**
     * Scope для фильтрации по имени лога
     */
    public function scopeInLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }
    
    /**
     * Scope для фильтрации по причине (causer)
     */
    public function scopeCausedBy($query, $causer)
    {
        return $query->where('causer_type', get_class($causer))
                     ->where('causer_id', $causer->id);
    }
    
    /**
     * Scope для фильтрации по субъекту (subject)
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_type', get_class($subject))
                     ->where('subject_id', $subject->id);
    }
}