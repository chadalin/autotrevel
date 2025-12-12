<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, авторизован ли пользователь
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Пожалуйста, войдите в систему');
        }

        // Проверяем роль пользователя
        $user = auth()->user();
        
        // Разрешаем доступ админам и модераторам
        if ($user->role === 'admin' || $user->role === 'moderator') {
            return $next($request);
        }

        // Для обычных пользователей - запрет доступа
        abort(403, 'Доступ запрещён. Требуются права администратора.');
    }
}