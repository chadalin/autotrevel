<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\Models\TravelRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'all');
        $user = Auth::user();
        
        $query = Chat::forUser($user->id)
            ->with(['users', 'lastMessage', 'route'])
            ->withUnreadCount($user->id)
            ->orderBy('updated_at', 'desc');

        // Фильтрация по типу
        if ($type === 'private') {
            $query->where('type', 'private');
        } elseif ($type === 'route') {
            $query->where('type', 'route');
        } elseif ($type === 'group') {
            $query->where('type', 'group');
        }

        $chats = $query->paginate(20);

        // Получаем статистику для боковой панели
        $stats = [
            'private' => Chat::private($user->id)->count(),
            'route' => Chat::routeChats($user->id)->count(),
            'group' => Chat::group($user->id)->count(),
        ];

        return view('chats.index', compact('chats', 'stats', 'type'));
    }

    public function show(Chat $chat)
    {
        $user = Auth::user();
        
        // Проверяем, есть ли пользователь в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }
        
        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
            
        // Помечаем сообщения как прочитанные
        $chat->users()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);

        return view('chats.show', compact('chat', 'messages'));
    }

    public function create(Request $request)
    {
        $routeId = $request->input('route_id');
        $userId = $request->input('user_id');
        
        if ($routeId) {
            $route = TravelRoute::findOrFail($routeId);
            // Проверяем существующий чат для маршрута
            $existingChat = Chat::where('route_id', $routeId)
                ->where('type', 'route')
                ->whereHas('users', function($q) use ($route) {
                    $q->where('user_id', Auth::id());
                })
                ->first();
                
            if ($existingChat) {
                return redirect()->route('chats.show', $existingChat);
            }
        }

        // Для приватного чата проверяем, не существует ли уже чат с этим пользователем
        if ($userId) {
            $otherUser = User::findOrFail($userId);
            $existingPrivateChat = Chat::where('type', 'private')
                ->whereHas('users', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->whereHas('users', function($q) {
                    $q->where('user_id', Auth::id());
                })
                ->first();
                
            if ($existingPrivateChat) {
                return redirect()->route('chats.show', $existingPrivateChat);
            }
        }

        return view('chats.create', [
            'route' => $route ?? null,
            'user' => $userId ? User::find($userId) : null,
            'users' => User::where('id', '!=', Auth::id())->get()
        ]);
    }

    public function store(Request $request)
{
    $user = Auth::user();
    
    // Валидация в зависимости от типа чата
    if ($request->type === 'private') {
        $validated = $request->validate([
            'type' => 'required|in:private',
            'user_ids' => 'required|array|size:1',
            'user_ids.*' => 'exists:users,id'
        ]);
    } elseif ($request->type === 'group') {
        $validated = $request->validate([
            'type' => 'required|in:group',
            'name' => 'required|string|max:255',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id'
        ]);
    } elseif ($request->type === 'route') {
        $validated = $request->validate([
            'type' => 'required|in:route',
            'route_id' => 'required|exists:travel_routes,id'
        ]);
    } else {
        return back()->with('error', 'Неверный тип чата');
    }

    DB::beginTransaction();
    
    try {
        if ($validated['type'] === 'private') {
            // Проверяем, не существует ли уже приватный чат
            $existingChat = Chat::where('type', 'private')
                ->whereHas('users', function($q) use ($validated) {
                    $q->where('user_id', $validated['user_ids'][0]);
                })
                ->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->first();
                
            if ($existingChat) {
                return redirect()->route('chats.show', $existingChat);
            }
            
            // Создаем приватный чат
            $chat = Chat::create([
                'type' => 'private',
                'route_id' => null,
            ]);
            
            // Добавляем обоих пользователей
            $chat->users()->attach([$user->id, $validated['user_ids'][0]], [
                'joined_at' => now(),
                'last_read_at' => null
            ]);
            
        } elseif ($validated['type'] === 'route') {
            // Проверяем существующий чат для маршрута
            $existingChat = Chat::where('route_id', $validated['route_id'])
                ->where('type', 'route')
                ->first();
                
            if ($existingChat) {
                // Добавляем пользователя в существующий чат
                if (!$existingChat->users()->where('user_id', $user->id)->exists()) {
                    $existingChat->users()->attach($user->id, [
                        'joined_at' => now(),
                        'last_read_at' => null
                    ]);
                }
                DB::commit();
                return redirect()->route('chats.show', $existingChat)
                    ->with('success', 'Вы присоединились к обсуждению маршрута');
            }
            
            // Создаем новый чат для маршрута
            $chat = Chat::create([
                'type' => 'route',
                'route_id' => $validated['route_id'],
            ]);
            
            // Добавляем создателя
            $chat->users()->attach($user->id, [
                'joined_at' => now(),
                'last_read_at' => null
            ]);
            
        } elseif ($validated['type'] === 'group') {
            // Создаем групповой чат
            $chat = Chat::create([
                'type' => 'group',
                'name' => $validated['name'],
                'route_id' => null,
            ]);
            
            // Добавляем создателя и выбранных участников
            $memberIds = array_merge([$user->id], $validated['member_ids']);
            $attachments = [];
            foreach ($memberIds as $memberId) {
                $attachments[$memberId] = [
                    'joined_at' => now(),
                    'last_read_at' => $memberId == $user->id ? now() : null
                ];
            }
            $chat->users()->attach($attachments);
        }

        DB::commit();
        
        return redirect()->route('chats.show', $chat)
            ->with('success', 'Чат успешно создан');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Ошибка при создании чата: ' . $e->getMessage());
    }
}

    public function addUsers(Chat $chat, Request $request)
{
    $user = Auth::user();
    
    // Проверяем, что пользователь есть в чате и это групповой чат
    if (!$chat->users()->where('user_id', $user->id)->exists() || $chat->type !== 'group') {
        abort(403, 'У вас нет прав для управления этим чатом');
    }
    
    $validated = $request->validate([
        'user_ids' => 'required|array|min:1',
        'user_ids.*' => 'exists:users,id'
    ]);

    // Исключаем уже добавленных пользователей
    $existingUserIds = $chat->users()->pluck('user_id')->toArray();
    $newUserIds = array_diff($validated['user_ids'], $existingUserIds);
    
    if (empty($newUserIds)) {
        return back()->with('info', 'Все выбранные пользователи уже есть в чате');
    }

    // Добавляем новых пользователей
    $attachments = [];
    foreach ($newUserIds as $userId) {
        $attachments[$userId] = [
            'joined_at' => now(),
            'last_read_at' => null
        ];
    }
    
    $chat->users()->attach($attachments);

    // Создаем системное сообщение о добавлении пользователей
    $userNames = User::whereIn('id', $newUserIds)->pluck('name')->implode(', ');
    
    // Используем новый метод для системных сообщений
    $chat->messages()->create([
        'user_id' => $user->id,
        'content' => "{$user->name} добавил(а) в чат: {$userNames}",
        'is_system' => true
    ]);

    return back()->with('success', 'Пользователи успешно добавлены');
}

    public function leave(Chat $chat)
    {
        $user = Auth::user();
        
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'Вы не состоите в этом чате');
        }

        // Удаляем пользователя из чата
        $chat->users()->detach($user->id);

        // Если это приватный чат и остался один пользователь, удаляем чат
        if ($chat->type === 'private' && $chat->users()->count() === 1) {
            $chat->delete();
            return redirect()->route('chats.index')
                ->with('info', 'Чат удалён');
        }

        // Если это групповой чат и пользователь был последним, удаляем чат
        if ($chat->type === 'group' && $chat->users()->count() === 0) {
            $chat->delete();
            return redirect()->route('chats.index')
                ->with('info', 'Чат удалён');
        }

        // Создаем системное сообщение о выходе
        if ($chat->type !== 'private') {
            $chat->messages()->create([
                'user_id' => $user->id,
                'content' => "{$user->name} покинул(а) чат",
                'is_system' => true
            ]);
        }

        return redirect()->route('chats.index')
            ->with('info', 'Вы вышли из чата');
    }
}