@extends('layouts.app')

@section('title', 'Тестовый вход - AutoRuta')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Тестовый вход
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Введите email для быстрой авторизации
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('test.login') }}" method="POST">
            @csrf
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm"
                           placeholder="Ваш email"
                           value="test@example.com">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Войти (тестовый режим)
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Будет создан/найден пользователь и показан код верификации
                </p>
            </div>
        </form>
        
        <div class="text-center">
            <p class="text-sm text-gray-600">
                Или используйте прямую ссылку:
            </p>
            <div class="mt-2 space-y-2">
                <a href="{{ route('test.direct-login', 'user@example.com') }}" 
                   class="inline-block px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Войти как user@example.com
                </a>
                <br>
                <a href="{{ route('test.direct-login', 'admin@autoruta.ru') }}" 
                   class="inline-block px-4 py-2 bg-blue-200 text-blue-800 rounded hover:bg-blue-300 text-sm">
                    Войти как админ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection