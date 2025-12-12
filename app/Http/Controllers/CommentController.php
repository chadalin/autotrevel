<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'commentable_type' => 'required|string|in:route,point,quest',
            'commentable_id' => 'required|integer',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'content' => $request->content,
            'user_id' => Auth::id(),
            'commentable_type' => $this->getCommentableType($request->commentable_type),
            'commentable_id' => $request->commentable_id,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
        ]);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);
        
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update(['content' => $request->content]);

        return response()->json([
            'success' => true,
            'comment' => $comment,
        ]);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        // Если удаляем родительский комментарий, удаляем и все ответы
        if ($comment->parent_id === null) {
            $comment->replies()->delete();
        }
        
        $comment->delete();

        return response()->json(['success' => true]);
    }

    public function toggleLike(Comment $comment)
    {
        $result = $comment->toggleLike(Auth::id());

        return response()->json([
            'success' => true,
            'liked' => $result,
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }

    public function pin(Comment $comment)
    {
        $this->authorize('moderate', $comment->commentable);
        
        $comment->update(['is_pinned' => true]);

        return response()->json(['success' => true]);
    }

    public function unpin(Comment $comment)
    {
        $this->authorize('moderate', $comment->commentable);
        
        $comment->update(['is_pinned' => false]);

        return response()->json(['success' => true]);
    }

    private function getCommentableType($type)
    {
        $types = [
            'route' => 'App\Models\Route',
            'point' => 'App\Models\PointOfInterest',
            'quest' => 'App\Models\Quest',
        ];

        return $types[$type] ?? $type;
    }
}