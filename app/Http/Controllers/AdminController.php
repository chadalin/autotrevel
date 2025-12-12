<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\User;
use App\Models\Quest;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
      //  $this->middleware(['auth', 'admin']);
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'total_routes' => Route::count(),
           // 'pending_routes' => Route::where('status', 'pending')->count(),
            'total_quests' => Quest::count(),
            'active_quests' => Quest::active()->count(),
           // 'total_reports' => Report::where('status', 'pending')->count(),
        ];

        $recentActivities = DB::table('activity_log')
            ->join('users', 'activity_log.causer_id', '=', 'users.id')
            ->select( 'users.name', 'users.email')
            ->orderBy('created_at', 'desc')
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
            $query->where('status', $request->status);
        }
        
        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        $routes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.routes.index', compact('routes'));
    }

    public function moderateRoute(Route $route, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'moderator_comment' => 'required_if:status,rejected|string|max:500',
        ]);

        $route->update([
            'status' => $request->status,
            'moderator_comment' => $request->moderator_comment,
            'moderated_at' => now(),
            'moderated_by' => auth()->id(),
        ]);

        // Отправляем уведомление автору
        // Notification::send($route->user, new RouteModeratedNotification($route));

        return back()->with('success', 'Маршрут успешно проверен');
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

    public function reports()
    {
        $reports = Report::with(['user', 'reportable'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.reports.index', compact('reports'));
    }

    public function handleReport(Report $report, Request $request)
    {
        $request->validate([
            'action' => 'required|in:dismiss,ban,warn,delete_content',
            'moderator_comment' => 'required|string|max:500',
        ]);

        $report->update([
            'status' => 'resolved',
            'moderator_id' => auth()->id(),
            'moderator_comment' => $request->moderator_comment,
            'resolved_at' => now(),
        ]);

        // Выполняем действие
        switch ($request->action) {
            case 'delete_content':
                $report->reportable->delete();
                break;
            case 'ban':
                $report->user->update(['banned_until' => now()->addDays(7)]);
                break;
            case 'warn':
                // Отправляем предупреждение
                break;
        }

        return back()->with('success', 'Жалоба обработана');
    }

    public function settings()
    {
        return view('admin.settings');
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
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Настройки обновлены');
    }
}