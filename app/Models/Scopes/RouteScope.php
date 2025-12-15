<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RouteScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Автоматически заменяем 'routes.' на 'travel_routes.' в запросах
        if (strpos($builder->toSql(), 'routes.') !== false) {
            // Этот scope будет автоматически заменять имена таблиц
        }
    }
}