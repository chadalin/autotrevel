<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Проверяем, авторизован ли пользователь и является ли админом
        if (!Auth::check() || (Auth::user()->role !== 'admin' && Auth::user()->role !== 'moderator')) {
            abort(403, 'Доступ запрещен');
        }
        
        return $next($request);
    }
}