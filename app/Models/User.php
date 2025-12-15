<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * Отношения
     */
    
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(QuestBadge::class, 'user_badges', 'user_id', 'badge_id')
            ->withTimestamps()
            ->withPivot('earned_at', 'metadata');
    }

    public function questCompletions(): HasMany
    {
        return $this->hasMany(QuestCompletion::class);
    }

    public function savedRoutes(): BelongsToMany
    {
        return $this->belongsToMany(TravelRoute::class, 'saved_routes', 'user_id', 'route_id')
                    ->withTimestamps();
    }

    public function routes(): HasMany
    {
        return $this->hasMany(TravelRoute::class);
    }
    
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    
    public function sessions(): HasMany
    {
        return $this->hasMany(RouteSession::class);
    }
    
    public function completions(): HasMany
    {
        return $this->hasMany(RouteCompletion::class);
    }
    
    // Связь с квестами через промежуточную таблицу user_quests
    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(Quest::class, 'user_quests')
            ->withPivot(['status', 'progress_current', 'progress_target', 'started_at', 'completed_at'])
            ->withTimestamps();
    }
    
    public function activeQuests(): BelongsToMany
    {
        return $this->quests()->wherePivot('status', 'in_progress');
    }
    
    public function userQuests(): HasMany
    {
        return $this->hasMany(UserQuest::class);
    }
    
    public function stats(): HasOne
    {
        return $this->hasOne(UserStats::class);
    }
    
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_user');
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

    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->role === 'admin';
    }

    public function getRankAttribute(): string
    {
        $ranks = [
            [0, 'Новичок'],
            [100, 'Путешественник'],
            [500, 'Искатель приключений'],
            [1000, 'Эксперт'],
            [2000, 'Мастер маршрутов'],
            [5000, 'Легенда дорог']
        ];
        
        $userExp = $this->experience;
        $currentRank = 'Новичок';
        
        foreach ($ranks as $rank) {
            if ($userExp >= $rank[0]) {
                $currentRank = $rank[1];
            }
        }
        
        return $currentRank;
    }
    
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }
}