<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        // Получаем статистику пользователя
        $stats = [
            'routes_created' => $user->travelRoutes()->count(),
            'routes_completed' => $user->routeCompletions()->count(),
            'quests_completed' => $user->userQuests()->where('status', 'completed')->count(),
            'badges_earned' => $user->badges()->count(),
        ];

        // Последние активности
        $recentActivities = $user->routeCompletions()
            ->with('route')
            ->orderBy('completed_at', 'desc')
            ->take(5)
            ->get();

        // Последние созданные маршруты
        $recentRoutes = $user->travelRoutes()
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('users.show', compact('user', 'stats', 'recentActivities', 'recentRoutes'));
    }

    public function achievements(User $user)
    {
        $badges = $user->badges()
            ->orderBy('rarity', 'desc')
            ->orderBy('sort_order')
            ->paginate(12);

        $quests = $user->userQuests()
            ->with('quest')
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        return view('users.achievements', compact('user', 'badges', 'quests'));
    }

    public function routes(User $user)
    {
        $routes = $user->travelRoutes()
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('users.routes', compact('user', 'routes'));
    }

    public function activity(User $user)
    {
        $completions = $user->routeCompletions()
            ->with('route')
            ->orderBy('completed_at', 'desc')
            ->paginate(15);

        $reviews = $user->reviews()
            ->with('route')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('users.activity', compact('user', 'completions', 'reviews'));
    }
}