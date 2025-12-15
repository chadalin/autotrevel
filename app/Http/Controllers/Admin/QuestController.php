<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Models\Badge;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth');
       // $this->middleware('admin');
    }
    
    // Список квестов
    public function index(Request $request)
    {
        $query = Quest::with(['badge', 'routes']);
        
        // Фильтрация
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('status') && $request->status) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }
        
        $quests = $query->latest()->paginate(20);
        
        return view('admin.quests.index', compact('quests'));
    }
    
    // Форма создания квеста
    public function create()
    {
        $badges = Badge::orderBy('name')->get();
        $routes = Route::orderBy('title')->get();
        $difficulties = [
            'easy' => 'Легкий',
            'medium' => 'Средний',
            'hard' => 'Сложный',
            'expert' => 'Эксперт',
        ];
        $types = [
            'collection' => 'Коллекция',
            'challenge' => 'Испытание',
            'weekend' => 'Выходной',
            'learning' => 'Обучение',
        ];
        
        return view('admin.quests.create', compact('badges', 'routes', 'difficulties', 'types'));
    }
    
    // Сохранение квеста
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'type' => 'required|in:collection,challenge,weekend,learning',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'min_level' => 'required|integer|min:1',
            'reward_exp' => 'required|integer|min:0',
            'reward_coins' => 'required|integer|min:0',
            'badge_id' => 'nullable|exists:badges,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'routes' => 'required|array',
            'routes.*' => 'exists:travel_routes,id',
            'is_active' => 'boolean',
            'is_repeatable' => 'boolean',
        ]);
        
        // Генерация slug
        $slug = Str::slug($request->title);
        $counter = 1;
        while (Quest::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->title) . '-' . $counter;
            $counter++;
        }
        
        // Создание квеста
        $quest = Quest::create([
            'title' => $request->title,
            'slug' => $slug,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'type' => $request->type,
            'difficulty' => $request->difficulty,
            'min_level' => $request->min_level,
            'reward_exp' => $request->reward_exp,
            'reward_coins' => $request->reward_coins,
            'badge_id' => $request->badge_id,
            'is_active' => $request->has('is_active'),
            'is_repeatable' => $request->has('is_repeatable'),
            'conditions' => $request->conditions ? json_decode($request->conditions) : [],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        
        // Привязка маршрутов
        if ($request->has('routes')) {
            $order = 1;
            foreach ($request->routes as $routeId) {
                $quest->routes()->attach($routeId, ['order' => $order]);
                $order++;
            }
        }
        
        return redirect()->route('admin.quests.index')
            ->with('success', 'Квест успешно создан!');
    }
    
    // Форма редактирования квеста
    public function edit(Quest $quest)
    {
        $badges = Badge::orderBy('name')->get();
        $routes = Route::orderBy('title')->get();
        $selectedRoutes = $quest->routes->pluck('id')->toArray();
        
        $difficulties = [
            'easy' => 'Легкий',
            'medium' => 'Средний',
            'hard' => 'Сложный',
            'expert' => 'Эксперт',
        ];
        
        $types = [
            'collection' => 'Коллекция',
            'challenge' => 'Испытание',
            'weekend' => 'Выходной',
            'learning' => 'Обучение',
        ];
        
        return view('admin.quests.edit', compact('quest', 'badges', 'routes', 'selectedRoutes', 'difficulties', 'types'));
    }
    
    // Обновление квеста
    public function update(Request $request, Quest $quest)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'type' => 'required|in:collection,challenge,weekend,learning',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'min_level' => 'required|integer|min:1',
            'reward_exp' => 'required|integer|min:0',
            'reward_coins' => 'required|integer|min:0',
            'badge_id' => 'nullable|exists:badges,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'routes' => 'required|array',
            'routes.*' => 'exists:travel_routes,id',
            'is_active' => 'boolean',
            'is_repeatable' => 'boolean',
        ]);
        
        // Обновление квеста
        $quest->update([
            'title' => $request->title,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'type' => $request->type,
            'difficulty' => $request->difficulty,
            'min_level' => $request->min_level,
            'reward_exp' => $request->reward_exp,
            'reward_coins' => $request->reward_coins,
            'badge_id' => $request->badge_id,
            'is_active' => $request->has('is_active'),
            'is_repeatable' => $request->has('is_repeatable'),
            'conditions' => $request->conditions ? json_decode($request->conditions) : [],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        
        // Обновление маршрутов
        $quest->routes()->detach();
        if ($request->has('routes')) {
            $order = 1;
            foreach ($request->routes as $routeId) {
                $quest->routes()->attach($routeId, ['order' => $order]);
                $order++;
            }
        }
        
        return redirect()->route('admin.quests.index')
            ->with('success', 'Квест успешно обновлен!');
    }
    
    // Просмотр квеста
    public function show(Quest $quest)
    {
        $quest->load(['badge', 'routes', 'userQuests.user']);
        
        return view('admin.quests.show', compact('quest'));
    }
    
    // Удаление квеста
    public function destroy(Quest $quest)
    {
        // Отвязываем маршруты перед удалением
        $quest->routes()->detach();
        $quest->delete();
        
        return redirect()->route('admin.quests.index')
            ->with('success', 'Квест успешно удален!');
    }
    
    // Активация/деактивация квеста
    public function toggleStatus(Quest $quest)
    {
        $quest->update([
            'is_active' => !$quest->is_active
        ]);
        
        $status = $quest->is_active ? 'активирован' : 'деактивирован';
        
        return redirect()->back()
            ->with('success', "Квест успешно $status!");
    }
    
    // Статистика квеста
    public function stats(Quest $quest)
    {
        $quest->load(['userQuests' => function($query) {
            $query->with('user')
                  ->orderBy('status')
                  ->orderBy('created_at', 'desc');
        }]);
        
        $stats = [
            'total_participants' => $quest->userQuests()->count(),
            'in_progress' => $quest->userQuests()->where('status', 'in_progress')->count(),
            'completed' => $quest->userQuests()->where('status', 'completed')->count(),
            'cancelled' => $quest->userQuests()->where('status', 'cancelled')->count(),
            'completion_rate' => $quest->userQuests()->count() > 0 
                ? round(($quest->userQuests()->where('status', 'completed')->count() / $quest->userQuests()->count()) * 100, 1)
                : 0,
        ];
        
        return view('admin.quests.stats', compact('quest', 'stats'));
    }
}