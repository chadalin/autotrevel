@extends('layouts.app')

@section('title', 'Квесты - AutoRuta')

@section('content')
<div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold mb-4">Система квестов</h1>
        <p class="text-xl text-gray-300">Исследуйте мир, выполняйте задания, получайте награды!</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <!-- Быстрая статистика пользователя -->
    @auth
        <div class="mb-8">
            @include('quests.partials.user-stats')
        </div>
    @endauth
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Сайдбар -->
        <div class="lg:col-span-1">
            <!-- Фильтры -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 sticky top-24">
                <h3 class="font-bold text-lg text-gray-800 mb-4">Фильтры квестов</h3>
                
                <div class="space-y-4">
                    <!-- Тип квеста -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Тип квеста</h4>
                        <div class="space-y-2">
                            <a href="{{ route('quests.index', ['type' => 'collection']) }}"
                               class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 {{ request('type') == 'collection' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-tasks mr-2"></i>
                                    <span>Коллекционные</span>
                                </div>
                                <span class="text-sm bg-gray-100 px-2 py-1 rounded">12</span>
                            </a>
                            
                            <a href="{{ route('quests.index', ['type' => 'challenge']) }}"
                               class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 {{ request('type') == 'challenge' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-fire mr-2"></i>
                                    <span>Испытания</span>
                                </div>
                                <span class="text-sm bg-gray-100 px-2 py-1 rounded">8</span>
                            </a>
                            
                            <a href="{{ route('quests.index', ['type' => 'weekend']) }}"
                               class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 {{ request('type') == 'weekend' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-campground mr-2"></i>
                                    <span>Выходные</span>
                                </div>
                                <span class="text-sm bg-gray-100 px-2 py-1 rounded">3</span>
                            </a>
                            
                            <a href="{{ route('quests.index', ['type' => 'story']) }}"
                               class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 {{ request('type') == 'story' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-book mr-2"></i>
                                    <span>Сюжетные</span>
                                </div>
                                <span class="text-sm bg-gray-100 px-2 py-1 rounded">5</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Сложность -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Сложность</h4>
                        <div class="space-y-2">
                            @foreach(['easy', 'medium', 'hard', 'expert'] as $difficulty)
                                @php
                                    $colors = [
                                        'easy' => 'bg-green-100 text-green-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'hard' => 'bg-red-100 text-red-800',
                                        'expert' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $labels = [
                                        'easy' => 'Лёгкий',
                                        'medium' => 'Средний',
                                        'hard' => 'Сложный',
                                        'expert' => 'Экспертный',
                                    ];
                                @endphp
                                <a href="{{ route('quests.index', ['difficulty' => $difficulty]) }}"
                                   class="block">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $colors[$difficulty] }} {{ request('difficulty') == $difficulty ? 'ring-2 ring-offset-2 ring-current' : '' }}">
                                        {{ $labels[$difficulty] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Статус -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Статус</h4>
                        <div class="space-y-2">
                            <a href="{{ route('quests.index', ['status' => 'active']) }}"
                               class="flex items-center p-2 rounded-lg hover:bg-gray-50 {{ request('status') == 'active' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <i class="fas fa-play-circle mr-2"></i>
                                <span>Активные</span>
                            </a>
                            
                            <a href="{{ route('quests.index', ['status' => 'featured']) }}"
                               class="flex items-center p-2 rounded-lg hover:bg-gray-50 {{ request('status') == 'featured' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <i class="fas fa-star mr-2"></i>
                                <span>Рекомендованные</span>
                            </a>
                            
                            <a href="{{ route('quests.index', ['status' => 'weekend']) }}"
                               class="flex items-center p-2 rounded-lg hover:bg-gray-50 {{ request('status') == 'weekend' ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                                <i class="fas fa-weekend mr-2"></i>
                                <span>На выходные</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Кнопка сброса -->
                    <a href="{{ route('quests.index') }}"
                       class="block text-center mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                        Сбросить фильтры
                    </a>
                </div>
            </div>
            
            <!-- Мои активные квесты -->
            @auth
                @if($activeQuests->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-4">Мои квесты</h3>
                        <div class="space-y-3">
                            @foreach($activeQuests as $userQuest)
                                <a href="{{ route('quests.show', $userQuest->quest->slug) }}"
                                   class="block p-3 border border-gray-200 rounded-lg hover:border-orange-300 hover:shadow-md transition duration-300">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-800 truncate">{{ $userQuest->quest->title }}</h4>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $userQuest->quest->difficulty_color }}">
                                            {{ $userQuest->quest->difficulty_label }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $userQuest->progress_percentage }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>{{ $userQuest->progress_current }}/{{ $userQuest->progress_target }}</span>
                                        <span>{{ $userQuest->progress_percentage }}%</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('quests.my') }}"
                           class="block mt-4 text-center text-orange-600 hover:text-orange-700 font-medium">
                            Все мои квесты →
                        </a>
                    </div>
                @endif
            @endauth
        </div>
        
        <!-- Основной контент -->
        <div class="lg:col-span-3">
            <!-- Заголовок и сортировка -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold text-gray-800">Все квесты</h2>
                        <p class="text-gray-600">Выберите интересующий вас квест</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">Сортировка:</span>
                        <select id="sort-select" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Сначала новые</option>
                            <option value="popular" {{ $sort == 'popular' ? 'selected' : '' }}>По популярности</option>
                            <option value="difficulty" {{ $sort == 'difficulty' ? 'selected' : '' }}>По сложности</option>
                            <option value="rewards" {{ $sort == 'rewards' ? 'selected' : '' }}>По наградам</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Рекомендованные квесты -->
            <!-- Рекомендованные квесты -->
@if(isset($recommendedQuests) && is_array($recommendedQuests) && count($recommendedQuests) > 0)
    <div class="mt-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Рекомендованные для вас</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($recommendedQuests as $quest)
                @include('quests.partials.quest-card-small', ['quest' => $quest])
            @endforeach
        </div>
    </div>
@endif
            
            <!-- Все квесты -->
            <div id="quests-container">
                @if($quests->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($quests as $quest)
                            @include('quests.partials.quest-card', ['quest' => $quest])
                        @endforeach
                    </div>
                    
                    <!-- Пагинация -->
                    <div class="mt-8">
                        {{ $quests->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-quest"></i>
                        </div>
                        <h3 class="text-2xl font-medium text-gray-600 mb-2">Квесты не найдены</h3>
                        <p class="text-gray-500 mb-6">Попробуйте изменить параметры фильтрации</p>
                    </div>
                @endif
            </div>
            
            <!-- Информационные блоки -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center mr-4">
                            <i class="fas fa-trophy text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">1567</div>
                            <div class="text-sm opacity-90">Выполнено квестов</div>
                        </div>
                    </div>
                    <p class="text-sm opacity-90">Пользователи уже завершили множество увлекательных заданий</p>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center mr-4">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">892</div>
                            <div class="text-sm opacity-90">Активных игроков</div>
                        </div>
                    </div>
                    <p class="text-sm opacity-90">Присоединяйтесь к сообществу исследователей</p>
                </div>
                
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center mr-4">
                            <i class="fas fa-route text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">24</div>
                            <div class="text-sm opacity-90">Новых квестов в месяц</div>
                        </div>
                    </div>
                    <p class="text-sm opacity-90">Регулярно добавляем новые задания</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Сортировка
    $('#sort-select').on('change', function() {
        const params = new URLSearchParams(window.location.search);
        params.set('sort', $(this).val());
        window.location.href = '{{ route('quests.index') }}?' + params.toString();
    });
    
    // Фильтрация через AJAX (опционально)
    $('a[href*="type="], a[href*="difficulty="], a[href*="status="]').on('click', function(e) {
        // Можно добавить AJAX загрузку для плавности
    });
});
</script>
@endpush