@extends('layouts.app')

@section('title', $quest->title . ' - Квесты')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Хлебные крошки -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Главная
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('quests.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2">
                        Квесты
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
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
                        @if($quest->type == 'collection')
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($quest->type == 'challenge')
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($quest->type == 'weekend')
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $quest->type == 'collection' ? 'Коллекция' : 
                           ($quest->type == 'challenge' ? 'Испытание' : 
                           ($quest->type == 'weekend' ? 'Выходные' : 
                           ($quest->type == 'story' ? 'История' : 'Пользовательский'))) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-white/20 backdrop-blur-sm">
                        @if($quest->difficulty == 'easy')
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($quest->difficulty == 'medium')
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($quest->difficulty == 'hard')
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $quest->difficulty == 'easy' ? 'Легкий' : 
                           ($quest->difficulty == 'medium' ? 'Средний' : 
                           ($quest->difficulty == 'hard' ? 'Сложный' : 'Эксперт')) }}
                    </span>
                    @if($quest->end_date && $quest->end_date > now())
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-white/20 backdrop-blur-sm">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            До {{ $quest->end_date->format('d.m') }}
                        </span>
                    @endif
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $quest->title }}</h1>
                
                <p class="text-lg opacity-90 mb-6">{{ $quest->short_description ?? Str::limit($quest->description, 200) }}</p>
                
                <!-- Статистика квеста -->
                <div class="flex flex-wrap gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $statistics['total_participants'] ?? 0 }}</div>
                        <div class="text-sm opacity-90">Участников</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $statistics['success_rate'] ?? 0 }}%</div>
                        <div class="text-sm opacity-90">Завершают</div>
                    </div>
                    @if($statistics['avg_completion_time'] ?? false)
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
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" clip-rule="evenodd"/>
                            </svg>
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
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                </svg>
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
                            <div class="w-10 h-10 rounded-lg {{ 
                                $quest->badge->rarity == 'common' ? 'bg-gray-500' : 
                                ($quest->badge->rarity == 'rare' ? 'bg-blue-500' : 
                                ($quest->badge->rarity == 'epic' ? 'bg-purple-500' : 'bg-yellow-500')) 
                            }} flex items-center justify-center mr-3">
                                @if($quest->badge->icon_svg)
                                    <div class="w-5 h-5 text-white">
                                        {!! $quest->badge->icon_svg !!}
                                    </div>
                                @else
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745a3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745a3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745a3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="font-bold">{{ $quest->badge->name }}</div>
                                <div class="text-sm opacity-90 {{ 
                                    $quest->badge->rarity == 'common' ? 'text-gray-300' : 
                                    ($quest->badge->rarity == 'rare' ? 'text-blue-300' : 
                                    ($quest->badge->rarity == 'epic' ? 'text-purple-300' : 'text-yellow-300')) 
                                }}">
                                    {{ ucfirst($quest->badge->rarity) }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Кнопки действий -->
                <div class="mt-6 pt-6 border-t border-white/20">
                    @if(Auth::check())
                        @php
                            $userProgress = $quest->users->where('id', Auth::id())->first();
                            $progressData = $userProgress ? $userProgress->pivot : null;
                        @endphp
                        
                        @if(!$progressData || $progressData->status == 'available')
                            @if($canStart)
                                <form action="{{ route('quests.start', $quest) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-white text-gray-900 hover:bg-gray-100 font-bold py-3 rounded-lg transition duration-300 mb-3">
                                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                        </svg>
                                        Начать квест
                                    </button>
                                </form>
                            @else
                                <button disabled class="w-full bg-gray-500 text-white font-bold py-3 rounded-lg cursor-not-allowed">
                                    Недоступно
                                </button>
                            @endif
                        @elseif($progressData->status == 'in_progress')
                            <div class="mb-4">
                                <div class="text-sm font-medium mb-2">Прогресс</div>
                                <div class="w-full bg-white/20 rounded-full h-3">
                                    <div class="bg-green-400 h-3 rounded-full" style="width: {{ 
                                        $progressData->progress_target > 0 ? 
                                        ($progressData->progress_current / $progressData->progress_target) * 100 : 0 
                                    }}%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span>{{ $progressData->progress_current }}/{{ $progressData->progress_target }}</span>
                                    <span>{{ 
                                        $progressData->progress_target > 0 ? 
                                        round(($progressData->progress_current / $progressData->progress_target) * 100) : 0 
                                    }}%</span>
                                </div>
                            </div>
                            
                            <!-- Кнопка перехода к первому незавершенному маршруту -->
                            @php
                                $incompleteRoute = null;
                                if (Auth::check() && $progressData->status == 'in_progress') {
                                    $completedData = $progressData->completed_data ?? [];
                                    
                                    if (is_array($completedData) && isset($completedData['completed_routes'])) {
                                        $incompleteRoute = $quest->routes->first(function($route) use ($completedData) {
                                            return !in_array($route->id, $completedData['completed_routes'] ?? []);
                                        });
                                    } else {
                                        $incompleteRoute = $quest->routes->first();
                                    }
                                }
                            @endphp
                            
                            @if($incompleteRoute)
                                <div class="mb-3">
                                    <a href="{{ route('routes.show', $incompleteRoute) }}" 
                                       class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white font-bold py-3 rounded-lg text-center transition duration-300 mb-2">
                                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                        Перейти к маршруту
                                    </a>
                                </div>
                            @endif
                            
                            <div class="space-y-2">
                                <a href="{{ route('quests.interactive.task', $quest->slug) }}" 
                                   class="block w-full bg-white/20 hover:bg-white/30 text-white font-medium py-2 rounded-lg text-center transition duration-300">
                                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Продолжить квест
                                </a>
                                <form action="{{ route('quests.cancel', $quest) }}" method="POST" onsubmit="return confirm('Отменить квест?')">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-500/20 hover:bg-red-500/30 text-red-300 font-medium py-2 rounded-lg transition duration-300">
                                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                        Отменить
                                    </button>
                                </form>
                            </div>
                        @elseif($progressData->status == 'completed')
                            <div class="text-center p-4 bg-green-500/20 rounded-lg mb-4">
                                <svg class="w-8 h-8 text-green-300 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <div class="font-bold text-green-300">Выполнено!</div>
                                <div class="text-sm text-green-200 mt-1">{{ 
                                    $progressData->completed_at ? $progressData->completed_at->format('d.m.Y') : 'Дата неизвестна' 
                                }}</div>
                            </div>
                            
                            @if($quest->is_repeatable)
                                <form action="{{ route('quests.start', $quest) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-white/20 hover:bg-white/30 text-white font-bold py-3 rounded-lg transition duration-300">
                                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                        </svg>
                                        Повторить квест
                                    </button>
                                </form>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-white text-gray-900 hover:bg-gray-100 font-bold py-3 rounded-lg text-center transition duration-300">
                            <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Войти для участия
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
                @if($quest->conditions && is_array($quest->conditions) && count($quest->conditions) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Условия выполнения</h3>
                        <div class="space-y-2">
                            @foreach($quest->conditions as $condition)
                                @if(is_array($condition) && isset($condition['type']))
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-gray-700">
                                            @if($condition['type'] == 'level')
                                                Уровень {{ $condition['value'] }} или выше
                                            @elseif($condition['type'] == 'quest_completed')
                                                Выполнен квест #{{ $condition['quest_id'] }}
                                            @elseif($condition['type'] == 'route_completed')
                                                Пройден маршрут #{{ $condition['route_id'] }}
                                            @elseif($condition['type'] == 'badge_earned')
                                                Получен значок #{{ $condition['badge_id'] }}
                                            @endif
                                        </span>
                                    </div>
                                @elseif(is_string($condition))
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-gray-700">{{ $condition }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Маршруты квеста -->
            @if($quest->routes && $quest->routes->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Маршруты квеста ({{ $quest->routes->count() }})
                        </h2>
                        
                        @if(Auth::check())
                            @php
                                $userProgress = $quest->users->where('id', Auth::id())->first();
                                $progressData = $userProgress ? $userProgress->pivot : null;
                            @endphp
                            @if($progressData && $progressData->status == 'in_progress')
                                <div class="text-sm font-medium text-gray-600">
                                    Прогресс: {{ $progressData->progress_current }}/{{ $progressData->progress_target }}
                                </div>
                            @endif
                        @endif
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($quest->routes as $route)
                            @php
                                $isCompleted = false;
                                if (Auth::check()) {
                                    $userProgress = $quest->users->where('id', Auth::id())->first();
                                    if ($userProgress && $userProgress->pivot->status == 'in_progress') {
                                        $completedData = $userProgress->pivot->completed_data ?? [];
                                        if (is_array($completedData) && isset($completedData['completed_routes'])) {
                                            $isCompleted = in_array($route->id, $completedData['completed_routes']);
                                        }
                                    }
                                }
                            @endphp
                            
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 hover:shadow-md transition duration-300 {{ $isCompleted ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-start">
                                    <!-- Номер маршрута -->
                                    <div class="w-10 h-10 rounded-lg {{ $isCompleted ? 'bg-green-500' : 'bg-gradient-to-r from-orange-500 to-red-500' }} flex items-center justify-center text-white font-bold mr-4 flex-shrink-0">
                                        @if($isCompleted)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <h3 class="font-bold text-lg text-gray-800 mb-1">
                                                    <a href="{{ route('routes.show', $route) }}" class="hover:text-orange-600">
                                                        {{ $route->title }}
                                                    </a>
                                                </h3>
                                                <div class="flex items-center">
                                                    <span class="px-2 py-1 text-xs rounded {{ 
                                                        $route->difficulty == 'easy' ? 'bg-green-100 text-green-800' : 
                                                        ($route->difficulty == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
                                                    }} mr-2">
                                                        {{ $route->difficulty == 'easy' ? 'Легкий' : 
                                                           ($route->difficulty == 'medium' ? 'Средний' : 'Сложный') }}
                                                    </span>
                                                    <span class="text-sm text-gray-600">
                                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ $route->length_km ?? '?' }} км
                                                    </span>
                                                    <span class="text-sm text-gray-600 ml-3">
                                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ $route->duration_minutes ?? '?' }} мин
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Статус выполнения -->
                                            @if(Auth::check())
                                                @php
                                                    $userProgress = $quest->users->where('id', Auth::id())->first();
                                                    $progressData = $userProgress ? $userProgress->pivot : null;
                                                @endphp
                                                @if($progressData && $progressData->status == 'in_progress')
                                                    <div class="ml-4">
                                                        @if($isCompleted)
                                                            <span class="px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                                Выполнено
                                                            </span>
                                                        @else
                                                            <span class="px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                                                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                                </svg>
                                                                В процессе
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        
                                        @if($route->short_description || $route->description)
                                            <p class="text-gray-600 mb-3">{{ $route->short_description ?? Str::limit($route->description, 150) }}</p>
                                        @endif
                                        
                                        <!-- Теги маршрута -->
                                        @if($route->tags && $route->tags->count() > 0)
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                @foreach($route->tags->take(3) as $tag)
                                                    <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $tag->color ?? '#6B7280' }}20; color: {{ $tag->color ?? '#6B7280' }};">
                                                        #{{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <!-- Кнопки действий -->
                                        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
                                            <!-- Основная кнопка перехода -->
                                            <a href="{{ route('routes.show', $route) }}" 
                                               class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition duration-300 inline-flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                                </svg>
                                                Открыть маршрут
                                            </a>
                                            
                                            <!-- Кнопка навигации -->
                                            @if(Auth::check() && isset($progressData) && $progressData->status == 'in_progress' && !$isCompleted)
                                                <a href="{{ route('routes.navigate', $route) }}" 
                                                   class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition duration-300 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Начать навигацию
                                                </a>
                                                
                                                <!-- Кнопка "Я проехал" -->
                                                <button type="button" 
                                                        data-route-id="{{ $route->id }}"
                                                        data-quest-id="{{ $quest->id }}"
                                                        class="mark-route-completed bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition duration-300 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Отметить как пройденный
                                                </button>
                                            @elseif(Auth::check() && $isCompleted)
                                                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-medium inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Уже пройдено
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Форма для отметки выполнения -->
            @if(Auth::check())
                @php
                    $userProgress = $quest->users->where('id', Auth::id())->first();
                    $progressData = $userProgress ? $userProgress->pivot : null;
                @endphp
                @if($progressData && $progressData->status == 'in_progress')
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 hidden" id="proof-form-container">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Подтверждение выполнения</h3>
                        <form id="proof-form" method="POST" enctype="multipart/form-data" action="{{ route('quests.progress.update', $quest) }}">
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
                                                    <svg class="w-8 h-8 text-gray-600 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-medium">Фото с геометкой</span>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="proof_type" value="gpx" class="sr-only peer">
                                            <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 transition duration-300">
                                                <div class="flex flex-col items-center">
                                                    <svg class="w-8 h-8 text-gray-600 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-medium">GPS трек</span>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="proof_type" value="code" class="sr-only peer">
                                            <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 transition duration-300">
                                                <div class="flex flex-col items-center">
                                                    <svg class="w-8 h-8 text-gray-600 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
                                                    </svg>
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
                                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Отправить
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            @endif
            
            <!-- Правила квеста -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Правила квеста</h2>
                <div class="space-y-3 text-gray-700">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Для выполнения квеста необходимо проехать все указанные маршруты</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Каждый маршрут требует подтверждения (фото с геометкой, GPS трек или код)</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        @if($quest->end_date)
                            <span>Квест доступен до {{ $quest->end_date->format('d.m.Y H:i') }}</span>
                        @else
                            <span>Квест доступен бессрочно</span>
                        @endif
                    </div>
                    @if($quest->is_repeatable)
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                            <span>Этот квест можно выполнять повторно</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Правая колонка -->
        <div class="space-y-6">
            <!-- Прогресс квеста -->
            @if(Auth::check())
                @php
                    $userProgress = $quest->users->where('id', Auth::id())->first();
                    $progressData = $userProgress ? $userProgress->pivot : null;
                @endphp
                @if($progressData && $progressData->status == 'in_progress')
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl shadow-lg p-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-4">Ваш прогресс</h3>
                        
                        <div class="text-center mb-6">
                            <div class="text-4xl font-bold text-blue-600 mb-2">
                                {{ $progressData->progress_target > 0 ? 
                                   round(($progressData->progress_current / $progressData->progress_target) * 100) : 0 }}%
                            </div>
                            <div class="text-gray-600">{{ $progressData->progress_current }}/{{ $progressData->progress_target }} маршрутов</div>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 h-3 rounded-full transition-all duration-500" 
                                 style="width: {{ 
                                     $progressData->progress_target > 0 ? 
                                     ($progressData->progress_current / $progressData->progress_target) * 100 : 0 
                                 }}%"></div>
                        </div>
                        
                        <!-- Следующий шаг -->
                        @php
                            $nextRoute = null;
                            if ($progressData->status == 'in_progress') {
                                $completedData = $progressData->completed_data ?? [];
                                
                                if (is_array($completedData) && isset($completedData['completed_routes'])) {
                                    $nextRoute = $quest->routes->first(function($route) use ($completedData) {
                                        return !in_array($route->id, $completedData['completed_routes'] ?? []);
                                    });
                                } else {
                                    $nextRoute = $quest->routes->first();
                                }
                            }
                        @endphp
                        
                        @if($nextRoute)
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <div class="text-sm font-medium text-gray-600 mb-2">Следующий маршрут:</div>
                                <div class="font-bold text-gray-800 truncate mb-2">{{ $nextRoute->title }}</div>
                                <a href="{{ route('routes.show', $nextRoute) }}" 
                                   class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white text-center py-2 rounded-lg font-medium text-sm transition duration-300">
                                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Перейти к маршруту
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
            
            <!-- Статистика квеста -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4">Статистика квеста</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Участники</span>
                            <span class="text-sm font-bold text-gray-800">{{ $statistics['total_participants'] ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Завершили</span>
                            <span class="text-sm font-bold text-gray-800">{{ $statistics['success_rate'] ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $statistics['success_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">В процессе</span>
                            <span class="text-sm font-bold text-gray-800">{{ $statistics['active_participants'] ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ 
                                ($statistics['total_participants'] ?? 0) > 0 ? 
                                (($statistics['active_participants'] ?? 0) / ($statistics['total_participants'] ?? 1)) * 100 : 0 
                            }}%"></div>
                        </div>
                    </div>
                    
                    @if($statistics['avg_completion_time'] ?? false)
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-800">{{ $statistics['avg_completion_time'] }}</div>
                                <div class="text-sm text-gray-600">Среднее время выполнения</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
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
                                        <span class="px-2 py-1 text-xs rounded-full {{ 
                                            $similar->difficulty == 'easy' ? 'bg-green-100 text-green-800' : 
                                            ($similar->difficulty == 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($similar->difficulty == 'hard' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800')) 
                                        }}">
                                            {{ $similar->difficulty == 'easy' ? 'Легкий' : 
                                               ($similar->difficulty == 'medium' ? 'Средний' : 
                                               ($similar->difficulty == 'hard' ? 'Сложный' : 'Эксперт')) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="mr-3">{{ $similar->reward_exp }} EXP</span>
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>{{ $similar->users()->count() }}</span>
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
                    <a href="{{ route('quests.my') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-800">Мои квесты</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('quests.achievements') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm3 0a1 1 0 10-1-1v1h1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-800">Мои достижения</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    
                    <a href="{{ route('quests.badges') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745a3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812a3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-800">Мои значки</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение способов подтверждения
    const proofTypeRadios = document.querySelectorAll('input[name="proof_type"]');
    const proofFields = document.querySelectorAll('.proof-fields');
    
    proofTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            proofFields.forEach(field => field.classList.add('hidden'));
            const activeField = document.getElementById(this.value + '-fields');
            if (activeField) {
                activeField.classList.remove('hidden');
            }
        });
    });
    
    // Отметка маршрута как пройденного
    const markRouteButtons = document.querySelectorAll('.mark-route-completed');
    const proofFormContainer = document.getElementById('proof-form-container');
    const proofRouteId = document.getElementById('proof-route-id');
    const cancelProof = document.getElementById('cancel-proof');
    
    markRouteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const routeId = this.dataset.routeId;
            const questId = this.dataset.questId;
            
            proofRouteId.value = routeId;
            proofFormContainer.classList.remove('hidden');
            
            // Прокручиваем к форме
            proofFormContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // Отмена подтверждения
    if (cancelProof) {
        cancelProof.addEventListener('click', function() {
            proofFormContainer.classList.add('hidden');
        });
    }
    
    // Превью фото
    const photoInput = document.querySelector('input[name="photo"]');
    const photoPreview = document.getElementById('photo-preview');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.classList.remove('hidden');
                    photoPreview.querySelector('img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Отправка формы подтверждения
    const proofForm = document.getElementById('proof-form');
    if (proofForm) {
        proofForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Прогресс обновлён! Маршрут отмечен как пройденный.', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Ошибка при отправке', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сервера. Попробуйте позже.', 'error');
            });
        });
    }
    
    // Уведомления
    function showNotification(message, type = 'info') {
        const bgColor = type === 'success' ? 'bg-green-500' : 
                       type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 z-50 animate-slide-up';
        toast.innerHTML = `
            <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    ${type === 'success' ? 
                        '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>' :
                        '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>'}
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});
</script>

<style>
.animate-slide-up {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
@endpush