@extends('layouts.app')

@section('title', 'Мои квесты - AutoRuta')

@section('content')
<div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold mb-4">Мои квесты</h1>
        <p class="text-xl text-gray-300">Отслеживайте свой прогресс и достижения</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <!-- Статистика пользователя -->
    @include('quests.partials.user-stats')
    
    <!-- Навигация -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('quests.my') }}?status=active"
               class="px-4 py-2 rounded-lg font-medium transition duration-300 {{ $status == 'active' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <i class="fas fa-play-circle mr-2"></i> Активные
            </a>
            
            <a href="{{ route('quests.my') }}?status=available"
               class="px-4 py-2 rounded-lg font-medium transition duration-300 {{ $status == 'available' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <i class="fas fa-plus-circle mr-2"></i> Доступные
            </a>
            
            <a href="{{ route('quests.my') }}?status=completed"
               class="px-4 py-2 rounded-lg font-medium transition duration-300 {{ $status == 'completed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <i class="fas fa-check-circle mr-2"></i> Завершённые
            </a>
            
            <a href="{{ route('quests.achievements') }}"
               class="px-4 py-2 rounded-lg font-medium transition duration-300 bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700">
                <i class="fas fa-trophy mr-2"></i> Достижения
            </a>
            
            <a href="{{ route('quests.badges') }}"
               class="px-4 py-2 rounded-lg font-medium transition duration-300 bg-gradient-to-r from-purple-500 to-purple-600 text-white hover:from-purple-600 hover:to-purple-700">
                <i class="fas fa-award mr-2"></i> Значки
            </a>
        </div>
    </div>
    
    <!-- Контент в зависимости от статуса -->
    @if($status == 'active')
        <!-- Активные квесты -->
        @if($quests->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($quests as $userQuest)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <!-- Заголовок -->
                        <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-bold text-lg truncate">{{ $userQuest->quest->title }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full {{ $userQuest->quest->difficulty_color }}">
                                    {{ $userQuest->quest->difficulty_label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-300 truncate">{{ $userQuest->quest->short_description }}</p>
                        </div>
                        
                        <!-- Прогресс -->
                        <div class="p-6">
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Прогресс</span>
                                    <span>{{ $userQuest->progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full" 
                                         style="width: {{ $userQuest->progress_percentage }}%"></div>
                                </div>
                                <div class="text-center text-sm text-gray-600 mt-1">
                                    {{ $userQuest->progress_current }}/{{ $userQuest->progress_target }} выполнено
                                </div>
                            </div>
                            
                            <!-- Время -->
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-play mr-2"></i>
                                    <span>{{ $userQuest->started_at->format('d.m.Y') }}</span>
                                </div>
                                @if($userQuest->quest->end_date)
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span>{{ $userQuest->quest->time_remaining }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Действия -->
                            <div class="space-y-2">
                                <a href="{{ route('quests.show', $userQuest->quest->slug) }}"
                                   class="block w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white py-2 rounded-lg font-medium text-center transition duration-300">
                                    <i class="fas fa-eye mr-2"></i> Продолжить
                                </a>
                                
                                <form action="{{ route('quests.cancel', $userQuest->quest) }}" method="POST" onsubmit="return confirm('Отменить квест?')">
                                    @csrf
                                    <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg font-medium transition duration-300">
                                        <i class="fas fa-times mr-2"></i> Отменить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-quest"></i>
                </div>
                <h3 class="text-2xl font-medium text-gray-600 mb-2">Нет активных квестов</h3>
                <p class="text-gray-500 mb-6">Начните новый квест и отправляйтесь в приключение!</p>
                <a href="{{ route('quests.index') }}" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
                    <i class="fas fa-plus mr-2"></i> Найти квест
                </a>
            </div>
        @endif
        
    @elseif($status == 'available')
        <!-- Доступные квесты -->
        @if($quests->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($quests as $quest)
                    @include('quests.partials.quest-card', ['quest' => $quest])
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="text-2xl font-medium text-gray-600 mb-2">Все доступные квесты выполнены!</h3>
                <p class="text-gray-500 mb-6">Вы молодец! Зайдите позже или повысьте уровень для доступа к новым квестам.</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('quests.achievements') }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
                        <i class="fas fa-trophy mr-2"></i> Достижения
                    </a>
                    <a href="{{ route('quests.leaderboard') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
                        <i class="fas fa-chart-line mr-2"></i> Лидерборд
                    </a>
                </div>
            </div>
        @endif
        
    @elseif($status == 'completed')
        <!-- Завершённые квесты -->
        @if($quests->count() > 0)
            <div class="space-y-6">
                @foreach($quests as $userQuest)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="md:flex">
                            <!-- Левая часть -->
                            <div class="md:w-2/3 p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="font-bold text-xl text-gray-800">{{ $userQuest->quest->title }}</h3>
                                        <div class="flex items-center mt-2">
                                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $userQuest->quest->difficulty_color }} mr-3">
                                                {{ $userQuest->quest->difficulty_label }}
                                            </span>
                                            <span class="text-gray-600">
                                                <i class="fas fa-calendar-check mr-1"></i>
                                                {{ $userQuest->completed_at->format('d.m.Y') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-green-600">+{{ $userQuest->quest->reward_exp }} EXP</div>
                                        <div class="text-sm text-gray-600">Награда</div>
                                    </div>
                                </div>
                                
                                <p class="text-gray-700 mb-4">{{ $userQuest->quest->short_description }}</p>
                                
                                <!-- Значок, если есть -->
                                @if($userQuest->quest->badge)
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg {{ $userQuest->quest->badge->rarity_color }} flex items-center justify-center mr-3">
                                            <i class="{{ $userQuest->quest->badge->icon }}"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $userQuest->quest->badge->name }}</div>
                                            <div class="text-sm {{ $userQuest->quest->badge->rarity_color }}">{{ $userQuest->quest->badge->rarity_label }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Правая часть -->
                            <div class="md:w-1/3 bg-gradient-to-br from-green-50 to-green-100 p-6 flex flex-col justify-center">
                                <div class="text-center">
                                    <div class="text-green-500 text-4xl mb-2">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="font-bold text-green-700 mb-1">Завершено</div>
                                    <div class="text-sm text-green-600">{{ $userQuest->completed_at->diffForHumans() }}</div>
                                    
                                    @if($userQuest->quest->is_repeatable)
                                        <form action="{{ route('quests.start', $userQuest->quest) }}" method="POST" class="mt-4">
                                            @csrf
                                            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-medium transition duration-300">
                                                <i class="fas fa-redo mr-2"></i> Повторить
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Пагинация -->
            <div class="mt-8">
                {{ $quests->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <h3 class="text-2xl font-medium text-gray-600 mb-2">Нет завершённых квестов</h3>
                <p class="text-gray-500 mb-6">Начните свой первый квест и получите первую награду!</p>
                <a href="{{ route('quests.index') }}" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
                    <i class="fas fa-plus mr-2"></i> Найти квест
                </a>
            </div>
        @endif
    @endif
</div>
@endsection