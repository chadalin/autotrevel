@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Панель администратора</h1>
    
    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($stats as $key => $value)
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ $value }}</div>
            <div class="text-gray-600 capitalize">{{ str_replace('_', ' ', $key) }}</div>
        </div>
        @endforeach
    </div>
    
    <!-- Быстрые действия -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Быстрые действия</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.routes.index') }}" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-3 rounded-lg text-center font-medium">
                <i class="fas fa-route mb-2 text-2xl"></i>
                <div>Маршруты</div>
            </a>
            <a href="{{ route('admin.users.index') }}" class="bg-green-50 hover:bg-green-100 text-green-700 px-4 py-3 rounded-lg text-center font-medium">
                <i class="fas fa-users mb-2 text-2xl"></i>
                <div>Пользователи</div>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-3 rounded-lg text-center font-medium">
                <i class="fas fa-flag mb-2 text-2xl"></i>
                <div>Жалобы</div>
            </a>
            <a href="{{ route('admin.settings') }}" class="bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-3 rounded-lg text-center font-medium">
                <i class="fas fa-cog mb-2 text-2xl"></i>
                <div>Настройки</div>
            </a>
        </div>
    </div>
    
    <!-- Последние активности -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Последние активности</h2>
        <div class="space-y-4">
            @foreach($recentActivities as $activity)
            <div class="border-b pb-4 last:border-0 last:pb-0">
                <div class="flex justify-between">
                    <div class="font-medium">{{ $activity->name }}</div>
                    <div class="text-gray-500 text-sm">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
                <div class="text-gray-600">{{ $activity->description }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection