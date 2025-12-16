@extends('layouts.admin')

@section('title', 'Управление квестами')
@section('page-title', 'Квесты')
@section('page-subtitle', 'Управление квестами и заданиями')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <!-- Заголовок и кнопки -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Список квестов</h2>
            <p class="text-gray-600">Всего квестов: {{ $quests->total() }}</p>
        </div>
        
        <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
            <a href="{{ route('admin.quests.create') }}" 
               class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Создать квест
            </a>
        </div>
    </div>
    
    <!-- Фильтры -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.quests.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Название квеста..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                <select name="type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все типы</option>
                    <option value="collection" {{ request('type') == 'collection' ? 'selected' : '' }}>Коллекция</option>
                    <option value="challenge" {{ request('type') == 'challenge' ? 'selected' : '' }}>Испытание</option>
                    <option value="weekend" {{ request('type') == 'weekend' ? 'selected' : '' }}>Выходной</option>
                    <option value="story" {{ request('type') == 'story' ? 'selected' : '' }}>Сюжетный</option>
                    <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>Пользовательский</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Сложность</label>
                <select name="difficulty" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Все</option>
                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Легкий</option>
                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Средний</option>
                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Сложный</option>
                    <option value="expert" {{ request('difficulty') == 'expert' ? 'selected' : '' }}>Эксперт</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    <i class="fas fa-filter mr-2"></i> Фильтр
                </button>
            </div>
        </form>
    </div>
    
    <!-- Таблица квестов -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Квест
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Тип / Сложность
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Задания / Маршруты
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Награды
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статус
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($quests as $quest)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center text-white font-bold">
                                {{ substr($quest->title, 0, 1) }}
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $quest->title }}</div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">
                                    {{ $quest->short_description ?: Str::limit($quest->description, 50) }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="space-y-1">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $quest->type_label }}
                            </span>
                            <div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $quest->difficulty_color }}">
                                    {{ $quest->difficulty_label }}
                                </span>
                            </div>
                        </div>
                    </td>
   <!-- Заменяем строки 133-148 на: -->
<td class="px-6 py-4">
    <div class="space-y-2">
        <div>
            <div class="flex items-center">
                <i class="fas fa-tasks text-purple-500 mr-1 text-sm"></i>
                <span class="text-sm text-gray-900 font-medium">
                    {{ $quest->tasks_count ?? 0 }} заданий
                </span>
                <a href="{{ route('admin.quests.tasks.index', $quest) }}" 
                   class="ml-2 text-xs text-blue-600 hover:text-blue-800 hover:underline">
                    управление
                </a>
            </div>
        </div>
        <div>
            <div class="flex items-center">
                <i class="fas fa-route text-green-500 mr-1 text-sm"></i>
                <span class="text-sm text-gray-900">{{ $quest->routes_count ?? 0 }} маршрутов</span>
            </div>
            @php
                $routeCount = $quest->routes_count ?? 0;
            @endphp
            @if($routeCount > 0 && $quest->routes && $quest->routes->count() > 0)
                <div class="text-xs text-gray-500 mt-1">
                    @php
                        $routeTitles = $quest->routes->take(2)->pluck('title')->toArray();
                    @endphp
                    {{ implode(', ', $routeTitles) }}
                    @if($routeCount > 2)
                        и ещё {{ $routeCount - 2 }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</td>
                    <td class="px-6 py-4">
                        <div class="space-y-1">
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-500 mr-1"></i>
                                <span class="text-sm">{{ $quest->reward_exp }} EXP</span>
                            </div>
                            @if($quest->reward_coins > 0)
                                <div class="flex items-center">
                                    <i class="fas fa-coins text-amber-500 mr-1"></i>
                                    <span class="text-sm">{{ $quest->reward_coins }} монет</span>
                                </div>
                            @endif
                            @if($quest->badge)
                                <div class="flex items-center">
                                    <i class="{{ $quest->badge->icon ?? 'fas fa-medal' }} text-blue-500 mr-1"></i>
                                    <span class="text-sm">{{ $quest->badge->name }}</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col space-y-2">
                            @if($quest->is_active)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 inline-block w-20 text-center">
                                    Активен
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 inline-block w-20 text-center">
                                    Неактивен
                                </span>
                            @endif
                            
                            @if($quest->is_repeatable)
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 inline-block w-24 text-center">
                                    Повторяемый
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 inline-block w-24 text-center">
                                    Одноразовый
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col space-y-2">
                            <!-- Основные действия -->
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.quests.show', $quest) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                   title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.quests.edit', $quest) }}" 
                                   class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" 
                                   title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.quests.stats', $quest) }}" 
                                   class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" 
                                   title="Статистика">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <form action="{{ route('admin.quests.toggle-status', $quest) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('{{ $quest->is_active ? 'Деактивировать' : 'Активировать' }} квест?')">
                                    @csrf
                                    <button type="submit" 
                                            class="text-{{ $quest->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $quest->is_active ? 'yellow' : 'green' }}-900 p-1 rounded hover:bg-{{ $quest->is_active ? 'yellow' : 'green' }}-50"
                                            title="{{ $quest->is_active ? 'Деактивировать' : 'Активировать' }}">
                                        <i class="fas fa-{{ $quest->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.quests.destroy', $quest) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Удалить квест? Это действие нельзя отменить.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" 
                                            title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Действия с заданиями -->
                            <div class="flex space-x-1">
                                <a href="{{ route('admin.quests.tasks.create', $quest) }}" 
                                   class="text-xs bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-2 py-1 rounded flex items-center"
                                   title="Добавить задание">
                                    <i class="fas fa-plus mr-1 text-xs"></i>
                                    <span>Добавить задание</span>
                                </a>
                                
                                @if($quest->tasks_count > 0)
                                <a href="{{ route('admin.quests.tasks.index', $quest) }}" 
                                   class="text-xs bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white px-2 py-1 rounded flex items-center"
                                   title="Все задания">
                                    <i class="fas fa-list mr-1 text-xs"></i>
                                    <span>Все задания</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-quest"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-600 mb-2">Квесты не найдены</h3>
                        <p class="text-gray-500 mb-6">Создайте первый квест или измените параметры фильтрации</p>
                        <a href="{{ route('admin.quests.create') }}" 
                           class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
                            <i class="fas fa-plus mr-2"></i> Создать квест
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Пагинация -->
    @if($quests->hasPages())
        <div class="mt-6">
            {{ $quests->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .route-item {
        transition: all 0.3s ease;
    }
    .route-item:hover {
        transform: translateX(5px);
    }
</style>
@endpush