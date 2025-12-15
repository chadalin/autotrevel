@extends('layouts.admin')

@section('title', 'Маршруты - Админ панель')
@section('page-title', 'Управление маршрутами')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <!-- Заголовок и поиск -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Маршруты</h3>
            <p class="text-gray-600">Всего: {{ $routes->total() }}</p>
        </div>
        
        <form method="GET" action="{{ url('/admin/routes') }}" class="flex space-x-2">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Все статусы</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Опубликованные</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Черновики</option>
            </select>
            
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Поиск..." 
                   class="px-4 py-2 border border-gray-300 rounded-lg">
                   
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-search"></i>
            </button>
            
            @if(request('search') || request('status'))
                <a href="{{ url('/admin/routes') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>
    
    <!-- Таблица маршрутов -->
    @if($routes->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-route text-4xl mb-4"></i>
            <p>Маршруты не найдены</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Автор</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Длина</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($routes as $route)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                @if($route->cover_image)
                                    <div class="flex-shrink-0 h-10 w-10 mr-3">
                                        <img src="{{ Storage::url($route->cover_image) }}" 
                                             alt="{{ $route->title }}"
                                             class="h-10 w-10 rounded-lg object-cover">
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($route->title, 30) }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($route->description, 40) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <p class="text-sm text-gray-900">{{ $route->user->name ?? 'Неизвестно' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($route->is_published)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Опубликован</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Черновик</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $route->length_km }} км
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $route->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('routes.show', $route) }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ url("/admin/routes/{$route->id}/edit") }}" 
                                   class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ url("/admin/routes/{$route->id}") }}" 
                                      onsubmit="return confirm('Удалить маршрут?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        <div class="mt-6">
            {{ $routes->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection