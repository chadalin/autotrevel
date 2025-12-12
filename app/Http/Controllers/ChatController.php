<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Auth::user()->chats()
            ->with(['users' => function($query) {
                $query->where('id', '!=', Auth::id());
            }, 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('user_id', '!=', Auth::id())
                    ->where('read_at', null);
            }])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('chats.index', compact('chats'));
    }

    public function show(Chat $chat)
    {
        $this->authorize('view', $chat);
        
        $chat->load(['users', 'messages.user']);
        
        // Отмечаем сообщения как прочитанные
        $chat->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now()
        ]);

        return view('chats.show', compact('chat'));
    }

    public function createPrivate($userId)
    {
        $otherUser = User::findOrFail($userId);
        
        // Проверяем существующий чат
        $chat = Chat::where('type', 'private')
            ->whereHas('users', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('users', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->first();

        if (!$chat) {
            $chat = Chat::create(['type' => 'private']);
            $chat->users()->attach([Auth::id(), $userId]);
        }

        return redirect()->route('chats.show', $chat);
    }

    public function createRouteChat(Route $route)
    {
        $chat = $route->chat()->first();
        
        if (!$chat) {
            $chat = Chat::create([
                'type' => 'route',
                'route_id' => $route->id,
                'name' => 'Чат маршрута: ' . $route->title
            ]);
            
            // Добавляем автора маршрута
            $chat->users()->attach($route->user_id);
            
            // Добавляем текущего пользователя
            if (Auth::id() !== $route->user_id) {
                $chat->users()->attach(Auth::id());
            }
        }

        return redirect()->route('chats.show', $chat);
    }

    public function destroy(Chat $chat)
    {
        $this->authorize('delete', $chat);
        $chat->delete();
        
        return redirect()->route('chats.index')->with('success', 'Чат удалён');
    }
}