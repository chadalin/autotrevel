<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\UserQuest;
use App\Models\QuestBadge;
use App\Services\QuestService;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestController extends Controller
{
    protected $questService;
    protected $achievementService;

    public function __construct(QuestService $questService, AchievementService $achievementService)
    {
        $this->questService = $questService;
        $this->achievementService = $achievementService;
    }

    // Список всех квестов
    public function index(Request $request)
    {
        $query = Quest::with(['badge', 'routes']);

        // Фильтры
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'featured':
                    $query->featured();
                    break;
                case 'weekend':
                    $query->where('type', 'weekend')->active();
                    break;
            }
        }

        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->withCount('userQuests')->orderBy('user_quests_count', 'desc');
                break;
            case 'difficulty':
                $query->orderByRaw("FIELD(difficulty, 'easy', 'medium', 'hard', 'expert')");
                break;
            case 'rewards':
                $query->orderBy('reward_exp', 'desc');
                break;
            default:
                $query->latest();
        }

        $quests = $query->paginate(12);
        
        // Статистика для пользователя
        $userStats = null;
        $activeQuests = [];
        $recommendedQuests = [];
        
        if (Auth::check()) {
            $user = Auth::user();
            $userStats = $user->stats;
            $activeQuests = $user->getActiveQuests();
            $recommendedQuests = $this->questService->getRecommendedQuests($user, 3);
        }

        return view('quests.index', compact(
            'quests', 
            'userStats', 
            'activeQuests', 
            'recommendedQuests',
            'sort'
        ));
    }

    // Детальная страница квеста
    public function show($slug)
    {
        $quest = Quest::with([
            'badge',
            'routes.user',
            'routes.tags',
            'routes.points'
        ])->where('slug', $slug)->firstOrFail();

        $userProgress = null;
        $canStart = false;
        
        if (Auth::check()) {
            $user = Auth::user();
            $userProgress = $quest->getProgressForUser($user);
            $canStart = $this->questService->canStartQuest($user, $quest);
            
            // Обновляем статистику активности
            $user->stats->recordActivity();
        }

        // Статистика квеста
        $statistics = $this->questService->getQuestStatistics($quest);

        // Похожие квесты
        $similarQuests = Quest::where('type', $quest->type)
            ->where('id', '!=', $quest->id)
            ->where('difficulty', $quest->difficulty)
            ->active()
            ->limit(4)
            ->get();

        return view('quests.show', compact(
            'quest',
            'userProgress',
            'canStart',
            'statistics',
            'similarQuests'
        ));
    }

    // Начать квест
    public function start(Request $request, Quest $quest)
    {
        $user = Auth::user();

        try {
            $userQuest = $this->questService->startQuest($user, $quest);
            
            return redirect()->route('quests.show', $quest->slug)
                ->with('success', 'Квест начат! Удачи в приключении!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    // Отметить прогресс квеста
    public function updateProgress(Request $request, Quest $quest)
    {
        $user = Auth::user();
        
        $result = $this->questService->updateQuestProgress($user, $quest, $request->all());
        
        if ($result === false) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось обновить прогресс'
            ], 400);
        }
        
        $userQuest = $user->userQuests()->where('quest_id', $quest->id)->first();
        
        // Проверяем достижения
        $this->achievementService->checkAchievements($user);
        
        return response()->json([
            'success' => true,
            'result' => $result,
            'progress' => [
                'current' => $userQuest->progress_current,
                'target' => $userQuest->progress_target,
                'percentage' => $userQuest->progress_percentage,
            ]
        ]);
    }

    // Отменить квест
    public function cancel(Request $request, Quest $quest)
    {
        $user = Auth::user();
        
        $userQuest = $user->userQuests()
            ->where('quest_id', $quest->id)
            ->where('status', 'in_progress')
            ->first();
            
        if (!$userQuest) {
            return redirect()->back()
                ->with('error', 'Квест не найден или уже завершён');
        }
        
        $userQuest->markAsFailed();
        
        return redirect()->route('quests.index')
            ->with('info', 'Квест отменён');
    }

    // Мои квесты
    public function myQuests(Request $request)
{
    $user = auth()->user();
    $status = $request->get('status', 'active');
    
    // Получаем статистику пользователя
    $userStats = [
        'total_exp' => $user->exp,
        'level' => $user->level,
        'active_quests' => $user->userQuests()->where('status', 'in_progress')->count(),
        'completed_quests' => $user->userQuests()->where('status', 'completed')->count(),
        'total_badges' => $user->badges()->count(),
        'next_level_exp' => $user->next_level_exp,
        'exp_to_next_level' => max(0, $user->next_level_exp - $user->exp),
        'level_progress' => $user->exp > 0 ? min(100, ($user->exp / $user->next_level_exp) * 100) : 0,
    ];
    
    $query = Quest::query();
    
    switch ($status) {
        case 'active':
            $quests = $user->userQuests()
                ->where('status', 'in_progress')
                ->with('quest')
                ->paginate(12);
            break;
            
        case 'completed':
            $quests = $user->userQuests()
                ->where('status', 'completed')
                ->with('quest')
                ->latest('completed_at')
                ->paginate(12);
            break;
            
        case 'available':
            // Квесты, доступные для начала (не начатые и не завершённые)
            $startedQuestIds = $user->userQuests()
                ->whereIn('status', ['in_progress', 'completed'])
                ->pluck('quest_id');
            
            $query->whereNotIn('id', $startedQuestIds)
                  ->where('is_active', true)
                  ->where(function($q) use ($user) {
                      // Проверка уровня
                      $q->whereNull('min_level')
                        ->orWhere('min_level', '<=', $user->level);
                  });
            
            $quests = $query->paginate(12);
            break;
            
        default:
            $quests = collect();
    }
    
    return view('quests.my', compact('quests', 'status', 'userStats'));
}

    // Лидерборд
    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 'all_time'); // all_time, weekly, monthly
        
        $query = \App\Models\UserStats::with('user')
            ->join('users', 'user_stats.user_id', '=', 'users.id')
            ->select('user_stats.*', 'users.name', 'users.avatar');
        
        switch ($period) {
            case 'weekly':
                // За последние 7 дней
                $query->where('user_stats.updated_at', '>=', now()->subDays(7))
                      ->orderByRaw('(user_stats.quests_completed * 3 + user_stats.routes_completed) DESC');
                break;
            case 'monthly':
                // За последние 30 дней
                $query->where('user_stats.updated_at', '>=', now()->subDays(30))
                      ->orderByRaw('(user_stats.quests_completed * 3 + user_stats.routes_completed) DESC');
                break;
            default:
                // За всё время
                $query->orderBy('total_exp', 'desc');
        }
        
        $leaderboard = $query->limit(50)->get();
        
        // Позиция текущего пользователя
        $userPosition = null;
        if (Auth::check()) {
            $userStats = Auth::user()->stats;
            
            switch ($period) {
                case 'weekly':
                    $position = \App\Models\UserStats::where('updated_at', '>=', now()->subDays(7))
                        ->whereRaw('(quests_completed * 3 + routes_completed) > ?', [
                            ($userStats->quests_completed * 3 + $userStats->routes_completed)
                        ])
                        ->count() + 1;
                    break;
                case 'monthly':
                    $position = \App\Models\UserStats::where('updated_at', '>=', now()->subDays(30))
                        ->whereRaw('(quests_completed * 3 + routes_completed) > ?', [
                            ($userStats->quests_completed * 3 + $userStats->routes_completed)
                        ])
                        ->count() + 1;
                    break;
                default:
                    $position = \App\Models\UserStats::where('total_exp', '>', $userStats->total_exp)->count() + 1;
            }
            
            $userPosition = $position;
        }
        
        return view('quests.leaderboard', compact('leaderboard', 'period', 'userPosition'));
    }

    // Достижения
    public function achievements()
    {
        $user = Auth::user();
        
        $achievements = $this->achievementService->getUserAchievements($user);
        $userStats = $user->stats;
        
        // Группируем достижения по категориям
        $groupedAchievements = [
            'quests' => array_filter($achievements, function($a) {
                return str_contains(strtolower($a['description']), 'квест');
            }),
            'routes' => array_filter($achievements, function($a) {
                return str_contains(strtolower($a['description']), 'маршрут') || 
                       str_contains(strtolower($a['description']), 'км') ||
                       str_contains(strtolower($a['description']), 'дорог');
            }),
            'social' => array_filter($achievements, function($a) {
                return str_contains(strtolower($a['description']), 'автор') || 
                       str_contains(strtolower($a['description']), 'избранное');
            }),
            'levels' => array_filter($achievements, function($a) {
                return str_contains(strtolower($a['description']), 'уровень');
            }),
        ];
        
        // Прогресс по всем достижениям
        $total = count($achievements);
        $earned = count(array_filter($achievements, function($a) {
            return $a['earned'];
        }));
        $progress = $total > 0 ? round(($earned / $total) * 100) : 0;
        
        return view('quests.achievements', compact(
            'groupedAchievements',
            'userStats',
            'progress',
            'total',
            'earned'
        ));
    }

    // Мои значки
    public function badges()
    {
        $user = Auth::user();
        
        $badges = QuestBadge::withCount(['users' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
        ->orderBy('rarity')
        ->get()
        ->map(function($badge) use ($user) {
            $badge->earned = $badge->users_count > 0;
            $badge->earned_at = $badge->earned ? 
                $user->badges()->where('badge_id', $badge->id)->first()->pivot->earned_at : 
                null;
            return $badge;
        });
        
        // Группируем по редкости
        $groupedBadges = [
            'legendary' => $badges->where('rarity', 'legendary'),
            'epic' => $badges->where('rarity', 'epic'),
            'rare' => $badges->where('rarity', 'rare'),
            'common' => $badges->where('rarity', 'common'),
        ];
        
        $earnedCount = $badges->where('earned', true)->count();
        $totalCount = $badges->count();
        
        return view('quests.badges', compact('groupedBadges', 'earnedCount', 'totalCount'));
    }

    // API: Получить квесты для карты
    public function mapQuests()
    {
        $quests = Quest::active()
            ->with(['routes' => function($query) {
                $query->select('id', 'title', 'start_coordinates', 'end_coordinates');
            }])
            ->get()
            ->map(function($quest) {
                return [
                    'id' => $quest->id,
                    'title' => $quest->title,
                    'slug' => $quest->slug,
                    'type' => $quest->type,
                    'difficulty' => $quest->difficulty,
                    'color' => $quest->color,
                    'routes' => $quest->routes->map(function($route) {
                        return [
                            'id' => $route->id,
                            'title' => $route->title,
                            'start' => $route->start_coordinates,
                            'end' => $route->end_coordinates,
                        ];
                    })->toArray(),
                ];
            });
        
        return response()->json($quests);
    }

    // Админ: Список квестов для модерации
    public function adminIndex(Request $request)
    {
        $this->authorize('admin', Quest::class);
        
        $query = Quest::with(['badge', 'userQuests' => function($q) {
            $q->where('status', 'completed');
        }]);
        
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'pending':
                    // Квесты, требующие модерации
                    $query->whereHas('completions', function($q) {
                        $q->where('verification_status', 'pending');
                    });
                    break;
            }
        }
        
        $quests = $query->paginate(20);
        
        return view('admin.quests.index', compact('quests'));
    }

    // Админ: Создание квеста
    public function adminCreate()
    {
        $this->authorize('admin', Quest::class);
        
        $badges = QuestBadge::all();
        $routes = \App\Models\Route::published()->get();
        
        return view('admin.quests.create', compact('badges', 'routes'));
    }

    // Админ: Сохранение квеста
    public function adminStore(Request $request)
    {
        $this->authorize('admin', Quest::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:100',
            'type' => 'required|in:collection,challenge,weekend,story,user',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'reward_exp' => 'required|integer|min:1',
            'reward_coins' => 'required|integer|min:0',
            'badge_id' => 'nullable|exists:quest_badges,id',
            'routes' => 'required_if:type,collection,weekend,story,user|array',
            'routes.*' => 'exists:routes,id',
            'conditions' => 'nullable|array',
            'requirements' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'cover_image' => 'nullable|image|max:5120',
            'color' => 'nullable|string|size:7',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_repeatable' => 'boolean',
            'max_completions' => 'nullable|integer|min:1',
        ]);
        
        // Создаём квест
        $quest = Quest::create([
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']) . '-' . time(),
            'description' => $validated['description'],
            'short_description' => Str::limit($validated['description'], 150),
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'reward_exp' => $validated['reward_exp'],
            'reward_coins' => $validated['reward_coins'],
            'badge_id' => $validated['badge_id'] ?? null,
            'conditions' => $validated['conditions'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'color' => $validated['color'] ?? '#FF7A45',
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_repeatable' => $validated['is_repeatable'] ?? false,
            'max_completions' => $validated['max_completions'] ?? null,
        ]);
        
        // Привязываем маршруты
        if (!empty($validated['routes'])) {
            foreach ($validated['routes'] as $index => $routeId) {
                $quest->routes()->attach($routeId, [
                    'order' => $index,
                    'is_required' => true,
                ]);
            }
        }
        
        // Обработка обложки
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('quests/covers', 'public');
            $quest->update(['cover_image' => $path]);
        }
        
        return redirect()->route('admin.quests.show', $quest)
            ->with('success', 'Квест успешно создан!');
    }

    // Админ: Модерация выполненных квестов
    public function adminModerate(Request $request)
    {
        $this->authorize('admin', Quest::class);
        
        $status = $request->get('status', 'pending');
        
        $completions = \App\Models\QuestCompletion::with(['user', 'quest', 'verifier'])
            ->when($status !== 'all', function($query) use ($status) {
                $query->where('verification_status', $status);
            })
            ->latest()
            ->paginate(20);
        
        return view('admin.quests.moderate', compact('completions', 'status'));
    }

    // Админ: Верификация выполнения квеста
    public function adminVerify(Request $request, $completionId)
    {
        $this->authorize('admin', Quest::class);
        
        $completion = \App\Models\QuestCompletion::findOrFail($completionId);
        
        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validated['status'] === 'verified') {
            $completion->verify(Auth::user(), $validated['notes']);
        } else {
            $completion->reject($validated['notes']);
        }
        
        return redirect()->back()
            ->with('success', 'Решение сохранено');
    }
}