<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Chat $chat, Request $request)
    {
        $user = Auth::user();
        
        // Проверяем, есть ли пользователь в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }
        
        $validated = $request->validate([
            'content' => 'required_without:attachment|string|max:2000',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        $messageData = [
            'user_id' => $user->id,
            'content' => $validated['content'] ?? null,
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat_attachments', 'public');
            $messageData['attachment'] = $path;
        }

        $message = $chat->messages()->create($messageData);

        // Обновляем время последнего сообщения в чате
        $chat->touch();

        // Обновляем время last_read_at для отправителя
        $chat->users()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => $message->load('user'),
                'html' => view('chats.partials.message', compact('message'))->render()
            ]);
        }

        return back()->with('success', 'Сообщение отправлено');
    }

    public function destroy(Message $message)
    {
        $user = Auth::user();
        
        // Проверяем, что сообщение принадлежит пользователю
        if ($message->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Вы не можете удалить это сообщение');
        }
        
        // Удаляем вложение если есть
        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment);
        }
        
        $message->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Сообщение удалено');
    }

    public function markAsRead(Chat $chat)
    {
        $user = Auth::user();
        
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }
        
        $chat->users()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }
}