@extends('layouts.app')

@section('title', $quest->title . ' - Квесты AutoRuta')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Хлебные крошки -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600">
                    <i class="fas fa-home mr-2"></i>Главная
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400"></i>
                    <a href="{{ route('quests.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2">
                        Квесты
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $quest->title }}</span>
                </div>
            </li>
        </ol>
    </nav>
    
    <!-- Заголовок квеста -->
    <div class="bg-gradient-to-r {{ $quest->difficulty == 'easy' ? 'from-green-500 to-green-600' : 
                                   ($quest->difficulty == 'medium' ? 'from-yellow-500 to-yellow-600' : 
                                   ($quest->difficulty == 'hard' ? 'from-red-500 to-red-600' : 'from-purple-500 to-purple-600')) }} 
                        rounded-2xl shadow-xl text-white p-8 mb-8">
        <div class="flex flex-col md:flex-row md:items-start justify-between">
            <div class="mb-6 md:mb-0 md:mr-8">
                <!-- Бейдж типа и сложности -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-white/20 backdrop-blur-sm">
                        <i class="fas fa-{{ $quest->type == 'collection' ? 'tasks' : 
                                           ($quest->type == 'challenge' ? 'fire' : 
                                           ($quest->type == 'weekend' ? 'campground' : 'book')) }} mr-1"></i>
                        {{ $quest->type_label }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-white/20 backdrop-blur-sm">
                        <i class="fas fa-{{ $quest->difficulty == 'easy' ? 'seedling' : 
                                           ($quest->difficulty == 'medium' ? 'walking' : 
                                           ($quest->difficulty == 'hard' ? 'running' : 'fire')) }} mr-1"></i>
                        {{ $quest->difficulty_label }}
                    </span>
                    @if($quest->time_remaining)
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-white/20 backdrop-blur-sm">
                            <i class="fas fa-clock mr-1"></i>{{ $quest->time_remaining }}
                        </span>
                    @endif
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $quest->title }}</h1>
                
                <p class="text-lg opacity-90 mb-6">{{ $quest->short_description ?? Str::limit($quest->description, 200) }}</p>
                
                <!-- Статистика квеста -->
                <div class="flex flex-wrap gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $statistics['total_participants'] }}</div>
                        <div class="text-sm opacity-90">Участников</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $statistics['completion_rate'] }}%</div>
                        <div class="text-sm opacity-90">Завершают</div>
                    </div>
                    @if($statistics['avg_completion_time'])
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ $statistics['avg_completion_time'] }}</div>
                            <div class="text-sm opacity-90">Среднее время</div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Награды и действия -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 min-w-64">
                <h3 class="font-bold text-lg mb-4">Награды</h3>
                
                <div class="space-y-4">
                    <!-- Опыт -->
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-yellow-500 flex items-center justify-center mr-3">
                            <i class="fas fa-star text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xl">{{ $quest->reward_exp }} EXP</div>
                            <div class="text-sm opacity-90">Опыт</div>
                        </div>
                    </div>
                    
                    <!-- Монеты -->
                    @if($quest->reward_coins > 0)
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center mr-3">
                                <i class="fas fa-coins text-white"></i>
                            </div>
                            <div>
                                <div class="font-bold text-xl">{{ $quest->reward_coins }} монет</div>
                                <div class="text-sm opacity-90">Валюта</div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Значок -->
                    @if($quest->badge)
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg {{ $quest->badge->rarity_color }} flex items-center justify-center mr-3">
                                <i class="{{ $quest->badge->icon }} text-white"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $quest->badge->name }}</div>
                                <div class="text-sm opacity-90 {{ $quest->badge->rarity_color }}">{{ $quest->badge->rarity_label }}</div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Кнопки действий -->
                <div class="mt-6 pt-6 border-t border-white/20">
                    @if(Auth::check())
                        @if($userProgress['status'] == 'available' && $canStart)
                            <form action="{{ route('quests.start', $quest) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-white text-gray-900 hover:bg-gray-100 font-bold py-3 rounded-lg transition duration-300 mb-3">
                                    <i class="fas fa-play mr-2"></i> Начать квест
                                </button>
                            </form>
                        @elseif($userProgress['status'] == 'in_progress')
                            <div class="mb-4">
                                <div class="text-sm font-medium mb-2">Прогресс</div>
                                <div class="w-full bg-white/20 rounded-full h-3">
                                    <div class="bg-green-400 h-3 rounded-full" style="width: {{ $userProgress['progress_percentage'] }}%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span>{{ $userProgress['progress'] }}/{{ $userProgress['progress_target'] }}</span>
                                    <span>{{ $userProgress['progress_percentage'] }}%</span>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <a href="{{ route('quests.my') }}" class="block w-full bg-white/20 hover:bg-white/30 text-white font-medium py-2 rounded-lg text-center transition duration-300">
                                    <i class="fas fa-tasks mr-2"></i> Мои квесты
                                </a>
                                <form action="{{ route('quests.cancel', $quest) }}" method="POST" onsubmit="return confirm('Отменить квест?')">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-500/20 hover:bg-red-500/30 text-red-300 font-medium py-2 rounded-lg transition duration-300">
                                        <i class="fas fa-times mr-2"></i> Отменить
                                    </button>
                                </form>
                            </div>
                        @elseif($userProgress['status'] == 'completed')
                            <div class="text-center p-4 bg-green-500/20 rounded-lg mb-4">
                                <i class="fas fa-check-circle text-green-300 text-2xl mb-2"></i>
                                <div class="font-bold text-green-300">Выполнено!</div>
                                <div class="text-sm text-green-200 mt-1">{{ $userProgress['completed_at']->format('d.m.Y') }}</div>
                            </div>
                            
                            @if($quest->is_repeatable)
                                <form action="{{ route('quests.start', $quest) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-white/20 hover:bg-white/30 text-white font-bold py-3 rounded-lg transition duration-300">
                                        <i class="fas fa-redo mr-2"></i> Повторить квест
                                    </button>
                                </form>
                            @endif
                        @else
                            <button disabled class="w-full bg-gray-500 text-white font-bold py-3 rounded-lg cursor-not-allowed">
                                Недоступно
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-white text-gray-900 hover:bg-gray-100 font-bold py-3 rounded-lg text-center transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i> Войти для участия
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Левая колонка -->
        <div class="lg:col-span-2">
            <!-- Описание квеста -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Описание квеста</h2>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($quest->description)) !!}
                </div>
                
                <!-- Условия выполнения -->
                @if($quest->conditions)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Условия выполнения</h3>
                        <div class="space-y-2">
                            @foreach($quest->conditions as $condition)
                                <div class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                    <span class="text-gray-700">{{ $this->formatCondition($condition) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Маршруты квеста -->
            @if($quest->routes->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        Маршруты квеста ({{ $quest->routes->count() }})
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($quest->routes as $route)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 hover:shadow-md transition duration-300">
                                <div class="flex items-start">
                                    <!-- Номер маршрута -->
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center text-white font-bold mr-4 flex-shrink-0">
                                        {{ $loop->iteration }}
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <h3 class="font-bold text-lg text-gray-800">
                                                    <a href="{{ route('routes.show', $route) }}" class="hover:text-orange-600">
                                                        {{ $route->title }}
                                                    </a>
                                                </h3>
                                                <div class="flex items-center mt-1">
                                                    <span class="px-2 py-1 text-xs rounded {{ $route->difficulty_color }} mr-2">
                                                        {{ $route->difficulty_label }}
                                                    </span>
                                                    <span class="text-sm text-gray-600">
                                                        <i class="fas fa-road mr-1"></i>{{ $route->length_km }} км
                                                    </span>
                                                    <span class="text-sm text-gray-600 ml-3">
                                                        <i class="fas fa-clock mr-1"></i>{{ $route->duration_formatted }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Статус выполнения -->
                                            @if(Auth::check() && $userProgress['status'] == 'in_progress')
                                                @php
                                                    $completedData = $userProgress['status'] == 'in_progress' ? 
                                                        (Auth::user()->userQuests()->where('quest_id', $quest->id)->first()->completed_data ?? []) : [];
                                                    $isCompleted = in_array($route->id, $completedData);
                                                @endphp
                                                <div class="ml-4">
                                                    @if($isCompleted)
                                                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i> Выполнено
                                                        </span>
                                                    @else
                                                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-clock mr-1"></i> В процессе
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <p class="text-gray-600 mb-3">{{ $route->short_description ?? Str::limit($route->description, 150) }}</p>
                                        
                                        <!-- Теги маршрута -->
                                        @if($route->tags->count() > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($route->tags->take(3) as $tag)
                                                    <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                                        #{{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <!-- Кнопка "Я проехал" -->
                                        @if(Auth::check() && $userProgress['status'] == 'in_progress' && !$isCompleted)
                                            <div class="mt-4 pt-4 border-t border-gray-100">
                                                <button type="button" 
                                                        data-route-id="{{ $route->id }}"
                                                        data-quest-id="{{ $quest->id }}"
                                                        class="mark-route-completed bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition duration-300">
                                                    <i class="fas fa-check-circle mr-2"></i> Отметить как пройденный
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Форма для отметки выполнения -->
            @if(Auth::check() && $userProgress['status'] == 'in_progress')
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hidden" id="proof-form-container">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Подтверждение выполнения</h3>
                    <form id="proof-form" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="proof-route-id" name="route_id">
                        <input type="hidden" name="quest_id" value="{{ $quest->id }}">
                        
                        <div class="space-y-4">
                            <!-- Способ подтверждения -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Способ подтверждения</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="proof_type" value="photo" class="sr-only peer" checked>
                                        <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 transition duration-300">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-camera text-2xl text-gray-600 mb-2"></i>
                                                <span class="font-medium">Фото с геометкой</span>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="proof_type" value="gpx" class="sr-only peer">
                                        <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 transition duration-300">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-route text-2xl text-gray-600 mb-2"></i>
                                                <span class="font-medium">GPS трек</span>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="proof_type" value="code" class="sr-only peer">
                                        <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 transition duration-300">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-key text-2xl text-gray-600 mb-2"></i>
                                                <span class="font-medium">Секретный код</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Поля для фото -->
                            <div id="photo-fields" class="proof-fields">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Загрузите фото с геометкой</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                        <div class="space-y-1 text-center">
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none">
                                                    <span>Выбрать файл</span>
                                                    <input type="file" name="photo" class="sr-only" accept="image/*">
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF до 5MB</p>
                                        </div>
                                    </div>
                                    <div id="photo-preview" class="mt-4 hidden">
                                        <img class="max-h-48 rounded-lg mx-auto" src="" alt="Предпросмотр фото">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Поля для GPS -->
                            <div id="gpx-fields" class="proof-fields hidden">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Загрузите GPS трек (GPX)</label>
                                    <input type="file" name="gpx_file" class="w-full px-3 py-2 border border-gray-300 rounded-lg" accept=".gpx">
                                </div>
                            </div>
                            
                            <!-- Поля для кода -->
                            <div id="code-fields" class="proof-fields hidden">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Введите секретный код</label>
                                    <input type="text" name="secret_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Код из описания маршрута">
                                </div>
                            </div>
                            
                            <!-- Комментарий -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий (необязательно)</label>
                                <textarea name="comment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Поделитесь впечатлениями..."></textarea>
                            </div>
                            
                            <!-- Кнопки -->
                            <div class="flex justify-end space-x-3">
                                <button type="button" id="cancel-proof" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    Отмена
                                </button>
                                <button type="submit" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-2 rounded-lg font-medium hover:from-orange-600 hover:to-red-700">
                                    <i class="fas fa-check mr-2"></i> Отправить
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
            
            <!-- Правила квеста -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Правила квеста</h2>
                <div class="space-y-3 text-gray-700">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <span>Для выполнения квеста необходимо проехать все указанные маршруты</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-camera text-green-500 mt-1 mr-3"></i>
                        <span>Каждый маршрут требует подтверждения (фото с геометкой, GPS трек или код)</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-clock text-yellow-500 mt-1 mr-3"></i>
                        @if($quest->end_date)
                            <span>Квест доступен до {{ $quest->end_date->format('d.m.Y H:i') }}</span>
                        @else
                            <span>Квест доступен бессрочно</span>
                        @endif
                    </div>
                    @if($quest->is_repeatable)
                        <div class="flex items-start">
                            <i class="fas fa-redo text-purple-500 mt-1 mr-3"></i>
                            <span>Этот квест можно выполнять повторно</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Правая колонка -->
        <div class="space-y-6">
            <!-- Статистика квеста -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4">Статистика квеста</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Участники</span>
                            <span class="text-sm font-bold text-gray-800">{{ $statistics['total_participants'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Завершили</span>
                            <span class="text-sm font-bold text-gray-800">{{ $statistics['completion_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $statistics['completion_rate'] }}%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">В процессе</span>
                            <span class="text-sm font-bold text-gray-800">{{ $statistics['in_progress'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $statistics['total_participants'] > 0 ? ($statistics['in_progress'] / $statistics['total_participants']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    @if($statistics['avg_completion_time'])
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-800">{{ $statistics['avg_completion_time'] }}</div>
                                <div class="text-sm text-gray-600">Среднее время выполнения</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Популярные маршруты в квесте -->
            @if(isset($statistics['popular_routes']) && $statistics['popular_routes']->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Популярные маршруты</h3>
                    
                    <div class="space-y-3">
                        @foreach($statistics['popular_routes'] as $route)
                            <a href="{{ route('routes.show', $route) }}" class="block group">
                                <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition duration-300">
                                    @if($route->cover_image)
                                        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                                            <img src="{{ Storage::url($route->cover_image) }}" alt="{{ $route->title }}" 
                                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-800 truncate group-hover:text-orange-600">{{ $route->title }}</h4>
                                        <div class="flex items-center mt-1">
                                            <span class="text-xs text-gray-600">
                                                <i class="fas fa-users mr-1"></i>{{ $route->user_quests_count }} прошли
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Похожие квесты -->
            @if($similarQuests->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Похожие квесты</h3>
                    
                    <div class="space-y-4">
                        @foreach($similarQuests as $similar)
                            <a href="{{ route('quests.show', $similar->slug) }}" class="block group">
                                <div class="p-3 border border-gray-200 rounded-lg hover:border-orange-300 hover:shadow-md transition duration-300">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-800 group-hover:text-orange-600">{{ $similar->title }}</h4>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $similar->difficulty_color }}">
                                            {{ $similar->difficulty_label }}
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        <span class="mr-3">{{ $similar->reward_exp }} EXP</span>
                                        <i class="fas fa-users mr-1"></i>
                                        <span>{{ $similar->participants_count }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Полезные ссылки -->
            <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4">Полезные ссылки</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('quests.leaderboard') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-trophy text-blue-600"></i>
                            </div>
                            <span class="font-medium text-gray-800">Таблица лидеров</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('quests.achievements') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-medal text-green-600"></i>
                            </div>
                            <span class="font-medium text-gray-800">Мои достижения</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('quests.badges') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                                <i class="fas fa-award text-purple-600"></i>
                            </div>
                            <span class="font-medium text-gray-800">Мои значки</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Переключение способов подтверждения
    $('input[name="proof_type"]').on('change', function() {
        $('.proof-fields').addClass('hidden');
        $('#' + $(this).val() + '-fields').removeClass('hidden');
    });
    
    // Отметка маршрута как пройденного
    $('.mark-route-completed').on('click', function() {
        const routeId = $(this).data('route-id');
        const questId = $(this).data('quest-id');
        
        $('#proof-route-id').val(routeId);
        $('#proof-form-container').removeClass('hidden');
        
        // Прокручиваем к форме
        $('html, body').animate({
            scrollTop: $('#proof-form-container').offset().top - 100
        }, 500);
    });
    
    // Отмена подтверждения
    $('#cancel-proof').on('click', function() {
        $('#proof-form-container').addClass('hidden');
    });
    
    // Превью фото
    $('input[name="photo"]').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview').removeClass('hidden').find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Отправка формы подтверждения
    $('#proof-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: "{{ route('quests.progress.update', $quest) }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('Прогресс обновлён!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(response.message || 'Ошибка', 'error');
                }
            },
            error: function(xhr) {
                showNotification('Ошибка сервера', 'error');
            }
        });
    });
    
    // Форматирование условий квеста
    function formatCondition(condition) {
        // Вспомогательная функция для отображения условий
        return condition;
    }
    
    // Уведомления
    function showNotification(message, type = 'info') {
        const bgColor = type === 'success' ? 'bg-green-500' : 
                       type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        const toast = $(`
            <div class="fixed bottom-4 right-4 z-50 animate-slide-up">
                <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-3"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
@endpush