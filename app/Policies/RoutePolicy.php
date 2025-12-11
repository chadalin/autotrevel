<?php

namespace App\Policies;

use App\Models\Route;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Route $route)
    {
        // Маршрут могут просматривать все, если он опубликован
        // Или владелец, даже если не опубликован
        return $route->is_published || $user->id === $route->user_id;
    }

    public function create(User $user)
    {
        // Любой авторизованный пользователь может создавать маршруты
        return $user !== null;
    }

    public function update(User $user, Route $route)
    {
        // Обновлять может только владелец
        return $user->id === $route->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Route $route)
    {
        // Удалять может только владелец или админ
        return $user->id === $route->user_id || $user->role === 'admin';
    }
}