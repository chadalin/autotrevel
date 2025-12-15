@extends('layouts.app')

@section('title', 'Тестовый код - AutoRuta')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Тестовый код верификации</h2>
            <p class="text-gray-600 mb-6">Используйте этот код для входа</p>
            
            <div class="mb-8">
                <div class="text-5xl font-bold text-gray-800 mb-4 tracking-wider">
                    {{ $code }}
                </div>
                <div class="text-sm text-gray-500">
                    (обычно отправляется на email)
                </div>
            </div>
            
            <div class="bg-gray-100 p-4 rounded-lg mb-6">
                <h3 class="font-medium text-gray-800 mb-2">Информация:</h3>
                <p class="text-sm"><span class="font-medium">Email:</span> {{ $email }}</p>
                <p class="text-sm"><span class="font-medium">Пользователь:</span> {{ $user->name }}</p>
                <p class="text-sm"><span class="font-medium">ID:</span> {{ $user->id }}</p>
                <p class="text-sm"><span class="font-medium">Создан:</span> {{ $user->wasRecentlyCreated ? 'Только что' : 'Уже был в базе' }}</p>
            </div>
            
            <div class="space-y-4">
                <!-- Форма для проверки кода -->
                <form action="{{ route('test.verify-code') }}" method="POST">
                    @csrf
                    <input type="hidden" name="code" value="{{ $code }}">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-lg font-bold hover:from-green-600 hover:to-emerald-700">
                        Подтвердить этот код
                    </button>
                </form>
                
                <a href="{{ route('home') }}" 
                   class="block w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-3 rounded-lg font-bold text-center hover:from-orange-600 hover:to-red-700">
                    На главную
                </a>
                
                <a href="{{ route('test.login.form') }}" 
                   class="block w-full bg-gray-200 text-gray-800 py-3 rounded-lg font-bold text-center hover:bg-gray-300">
                    Войти с другим email
                </a>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Вы уже авторизованы в системе как {{ $user->name }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection