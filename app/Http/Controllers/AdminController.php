<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\User;
use App\Models\Quest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function __construct()
    {
       // $this->middleware(['auth', 'admin']);
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'total_routes' => Route::count(),
            'published_routes' => Route::where('is_published', true)->count(),
            'draft_routes' => Route::where('is_published', false)->count(),
            'total_quests' => Quest::count(),
            'active_quests' => Quest::where('is_active', true)->count(),
            // Убираем Reports, если модели нет
            // 'total_reports' => Report::where('status', 'pending')->count(),
        ];

        // Исправленный запрос - добавляем префикс таблицы для created_at
        $recentActivities = DB::table('activity_log')
            ->join('users', 'activity_log.causer_id', '=', 'users.id')
            ->select('activity_log.*', 'users.name', 'users.email')
            ->orderBy('activity_log.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivities'));
    }

    public function users(Request $request)
    {
        $query = User::withCount(['routes', 'userQuests']);
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    public function routes(Request $request)
    {
        $query = Route::with(['user', 'tags']);
        
        if ($request->has('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }
        
        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
        }
        
        $routes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.routes.index', compact('routes'));
    }

    public function editRoute(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    public function updateRoute(Route $route, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:100',
            'length_km' => 'required|numeric|min:0.1',
            'duration_minutes' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'road_type' => 'required|in:asphalt,gravel,offroad,mixed',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $route->update($validated);

        return back()->with('success', 'Маршрут успешно обновлен');
    }

    public function deleteRoute(Route $route)
    {
        $route->delete();
        return redirect()->route('admin.routes')->with('success', 'Маршрут удален');
    }

    public function quests(Request $request)
    {
        $query = Quest::with(['badge', 'routes']);
        
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $quests = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.quests.index', compact('quests'));
    }

    // Убираем методы с Report, так как модели нет
    // public function reports()
    // {
    //     // Убрать, если нет модели Report
    // }
    // 
    // public function handleReport(Report $report, Request $request)
    // {
    //     // Убрать, если нет модели Report
    // }

    public function settings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        // Обновление настроек сайта
        $settings = [
            'site_name' => $request->site_name,
            'site_description' => $request->site_description,
            'contact_email' => $request->contact_email,
            'auto_approve_routes' => $request->has('auto_approve_routes'),
            'enable_registration' => $request->has('enable_registration'),
            'max_route_length' => $request->max_route_length,
            'max_file_size_photo' => $request->max_file_size_photo,
            'max_file_size_cover' => $request->max_file_size_cover,
            'default_difficulty' => $request->default_difficulty,
            'default_road_type' => $request->default_road_type,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Настройки обновлены');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
            'banned_until' => $user->is_active ? now() : null,
        ]);

        $status = $user->is_active ? 'разблокирован' : 'заблокирован';
        return back()->with('success', "Пользователь {$status}");
    }

    public function deleteUser(User $user)
    {
        // Перед удалением пользователя можно перенести его контент
        // или сделать soft delete, если используется
        $user->delete();
        return back()->with('success', 'Пользователь удален');
    }

    public function toggleQuestStatus(Quest $quest)
    {
        $quest->update(['is_active' => !$quest->is_active]);
        $status = $quest->is_active ? 'активирован' : 'деактивирован';
        return back()->with('success', "Квест {$status}");
    }

    public function viewActivityLog(Request $request)
    {
        // Проверяем, есть ли модель Activity
        if (class_exists(Activity::class)) {
            $query = Activity::with(['causer' => function($query) {
                $query->select('id', 'name', 'email');
            }]);

            if ($request->has('user_id')) {
                $query->where('causer_id', $request->user_id);
            }

            if ($request->has('type')) {
                $query->where('log_name', $request->type);
            }

            $activities = $query->orderBy('created_at', 'desc')->paginate(50);
        } else {
            // Если нет модели Activity, используем raw query
            $activities = DB::table('activity_log')
                ->join('users', 'activity_log.causer_id', '=', 'users.id')
                ->select('activity_log.*', 'users.name', 'users.email')
                ->orderBy('activity_log.created_at', 'desc')
                ->paginate(50);
        }

        return view('admin.activity_log', compact('activities'));
    }

    public function viewRoutePoints(Route $route)
    {
        $points = $route->points()->orderBy('order')->get();
        return view('admin.routes.points', compact('route', 'points'));
    }

    public function editRoutePoint(Route $route, $pointId)
    {
        $point = $route->points()->findOrFail($pointId);
        return view('admin.routes.edit_point', compact('route', 'point'));
    }

    public function updateRoutePoint(Route $route, $pointId, Request $request)
    {
        $point = $route->points()->findOrFail($pointId);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:viewpoint,cafe,hotel,attraction,gas_station,camping,photo_spot,nature,historical,other',
            'description' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'order' => 'integer',
        ]);

        $point->update($validated);

        return back()->with('success', 'Точка маршрута обновлена');
    }
}