<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // Для тестирования временно отключим
        'send-code',
        'verify-code',
        'test-csrf',
    ];
}