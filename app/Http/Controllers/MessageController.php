<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, Chat $chat)
    {
        $this->authorize('send', $chat);
        
        $request->validate([
            'content' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $message = new Message([
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $message->attachment = $path;
        }

        $chat->messages()->save($message);
        $chat->touch(); // Обновляем updated_at чата

        // Реальное время - отправка через WebSocket/Socket.io
        // broadcast(new NewMessage($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message->load('user'),
        ]);
    }

    public function update(Request $request, Message $message)
    {
        $this->authorize('update', $message);
        
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $message->update(['content' => $request->content]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);
        $message->delete();

        return response()->json(['success' => true]);
    }

    public function markAsRead(Message $message)
    {
        if ($message->chat->users->contains(Auth::id())) {
            $message->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}