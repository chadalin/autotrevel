<?php

namespace App\Http\Controllers;

use App\Models\QuestBadge;
use Illuminate\Http\Request;

class QuestBadgeController extends Controller
{
    public function index()
    {
        $badges = QuestBadge::orderBy('rarity')
            ->orderBy('sort_order')
            ->paginate(24);

        return view('quests.badges.index', compact('badges'));
    }

    public function show(QuestBadge $badge)
    {
        $badge->load('quests', 'users');
        
        // Получаем статистику по владельцам
        $ownersCount = $badge->users()->count();
        $totalUsers = \App\Models\User::count();
        $percentage = $totalUsers > 0 ? round(($ownersCount / $totalUsers) * 100, 2) : 0;

        // Последние полученные значки
        $recentEarned = $badge->users()
            ->orderBy('user_badges.created_at', 'desc')
            ->take(10)
            ->get();

        return view('quests.badges.show', compact('badge', 'ownersCount', 'percentage', 'recentEarned'));
    }
}