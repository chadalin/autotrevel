@extends('layouts.admin')

@section('title', 'Квесты - Админ панель')
@section('page-title', 'Управление квестами')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <!-- Заголовок и поиск -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Квесты</h3>
            <p class="text-gray-600">Всего: {{ $quests->total() }}</p>
        </div>
        
        <form method="GET" action="{{ url('/admin/quests') }}" class="flex space-x-2">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Все статусы</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
            </select>
            
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-search"></i>
            </button>
            
            @if(request('status'))
                <a href="{{ url('/admin/quests') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>
    
    <!-- Таблица квестов -->
    @if($quests->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-tasks text-4xl mb-4"></i>
            <p>Квесты не найдены</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Описание</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Награда</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участники</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($quests as $quest)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $quest->title }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-600">{{ Str::limit($quest->description, 50) }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($quest->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Активен</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Неактивен</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $quest->reward_xp ?? 0 }} XP
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $quest->participants_count ?? 0 }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex space-x-2">
                                <form method="POST" action="{{ url("/admin/quests/{$quest->id}/toggle") }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-blue-600 hover:text-blue-800">
                                        @if($quest->is_active)
                                            <i class="fas fa-pause"></i> Деактивировать
                                        @else
                                            <i class="fas fa-play"></i> Активировать
                                        @endif
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
            {{ $quests->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection