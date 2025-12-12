<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'level',
        'experience',
        'role',
        'verification_code',
        'verification_code_expires_at',
        'is_verified',
        'email_verified_at',
    ];

    protected $hidden = [
        'verification_code',
        'verification_code_expires_at',
        'remember_token',
    ];

    protected $casts = [
        'verification_code_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'level' => 'integer',
        'experience' => 'integer',
    ];

    protected $appends = ['next_level_exp', 'level_progress'];

    // ... существующие методы ...

    // Новые отношения

    /**
     * Генерирует код верификации
     */
    public function generateVerificationCode()
    {
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15),
        ]);

        return $code;
    }

    /**
     * Проверяет код верификации
     */
    public function verifyCode($code)
    {
        if (!$this->verification_code || !$this->verification_code_expires_at) {
            return false;
        }

        if ($this->verification_code_expires_at->isPast()) {
            return false;
        }

        if ($this->verification_code !== $code) {
            return false;
        }

        $this->update([
            'verification_code' => null,
            'verification_code_expires_at' => null,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        return true;
    }


    public function userQuests()
    {
        return $this->hasMany(UserQuest::class);
    }

    public function badges()
    {
        return $this->belongsToMany(QuestBadge::class, 'user_badges', 'user_id', 'badge_id')
            ->withTimestamps()
            ->withPivot('earned_at', 'metadata');
    }

    public function stats()
    {
        return $this->hasOne(UserStats::class);
    }

    public function questCompletions()
    {
        return $this->hasMany(QuestCompletion::class);
    }

    // Новые методы
    public function addExperience($amount)
    {
        $this->increment('experience', $amount);
        
        // Проверяем повышение уровня
        while ($this->experience >= $this->next_level_exp) {
            $this->level_up();
        }
        
        $this->save();
    }

    public function addCoins($amount)
    {
        $stats = $this->stats()->firstOrCreate([]);
        $stats->addCoins($amount);
    }

    private function level_up()
    {
        $expForNextLevel = $this->next_level_exp;
        $remainingExp = $this->experience - $expForNextLevel;
        
        $this->level += 1;
        $this->experience = $remainingExp;
        
        // Можно добавить уведомление о новом уровне
        // Notification::send($this, new LevelUpNotification($this->level));
    }

    public function getNextLevelExpAttribute()
    {
        return (int) (100 * pow($this->level + 1, 1.5));
    }

    public function getLevelProgressAttribute()
    {
        $expForCurrentLevel = (int) (100 * pow($this->level, 1.5));
        $expForNextLevel = $this->next_level_exp;
        $expInLevel = $this->experience;
        $expNeeded = $expForNextLevel - $expForCurrentLevel;

        return [
            'current' => $expInLevel,
            'needed' => $expNeeded,
            'percentage' => $expNeeded > 0 ? round(($expInLevel / $expNeeded) * 100) : 100,
        ];
    }

    public function getAvailableQuests()
    {
        return Quest::active()
            ->whereDoesntHave('userQuests', function ($query) {
                $query->where('user_id', $this->id)
                      ->whereIn('status', ['in_progress', 'completed']);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('userQuests', function ($q) {
                    $q->where('user_id', $this->id);
                })
                ->orWhereHas('userQuests', function ($q) {
                    $q->where('user_id', $this->id)
                      ->where('status', 'failed')
                      ->whereHas('quest', function ($q2) {
                          $q2->where('is_repeatable', true);
                      });
                });
            })
            ->get()
            ->filter(function ($quest) {
                return $quest->checkRequirements($this);
            });
    }

    public function getActiveQuests()
    {
        return $this->userQuests()->where('status', 'in_progress')->with('quest')->get();
    }

    public function getCompletedQuests()
    {
        return $this->userQuests()->where('status', 'completed')->with('quest')->get();
    }

    // При создании пользователя создаём статистику
    protected static function booted()
    {
        static::created(function ($user) {
            $user->stats()->create();
        });
    }

    public function savedRoutes()
{
    return $this->belongsToMany(Route::class, 'saved_routes', 'user_id', 'route_id')
                ->withTimestamps();
}
}