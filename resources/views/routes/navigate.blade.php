@extends('layouts.app')

@section('title', 'Навигация - ' . $route->title)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    #navigation-map {
        height: 400px;
        border-radius: 0.75rem;
        z-index: 1;
    }
    
    .checkpoint-card {
        transition: all 0.3s ease;
        border-left: 4px solid;
        cursor: pointer;
    }
    
    .checkpoint-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .checkpoint-current {
        border-left-color: #3B82F6;
        background-color: #EFF6FF;
    }
    
    .checkpoint-completed {
        border-left-color: #10B981;
        background-color: #ECFDF5;
    }
    
    .checkpoint-pending {
        border-left-color: #9CA3AF;
        background-color: #F9FAFB;
    }
    
    .navigation-sidebar {
        height: calc(100vh - 4rem);
        overflow-y: auto;
    }
    
    .progress-bar {
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .photo-preview {
        max-height: 200px;
        object-fit: cover;
        border-radius: 0.5rem;
    }
    
    .quest-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .quest-badge-blue {
        background-color: #DBEAFE;
        color: #1E40AF;
    }
    
    .quest-badge-green {
        background-color: #D1FAE5;
        color: #065F46;
    }
    
    .quest-badge-purple {
        background-color: #EDE9FE;
        color: #5B21B6;
    }
    
    .distance-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.9);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .accuracy-circle {
        stroke-width: 2;
        stroke-opacity: 0.3;
        fill-opacity: 0.1;
    }
    
    .arrived-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .arrived-modal.active {
        display: flex;
    }
    
    @media (max-width: 768px) {
        .navigation-sidebar {
            height: auto;
            max-height: 50vh;
        }
        #navigation-map {
            height: 300px;
        }
    }
    
    /* Анимация пульсации для текущей точки */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .pulse-animation {
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Шапка навигации -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $route->title }}</h1>
                <p class="text-gray-600 mt-1">Навигация по маршруту</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('routes.show', $route) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Назад к маршруту
                </a>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Длительность</div>
                    <div class="font-medium">{{ $route->duration }} ч</div>
                </div>
            </div>
        </div>
        
        <!-- Информация о сессии -->
        <div class="mt-4 bg-gradient-to-r from-blue-50 to-white p-4 rounded-lg border border-blue-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-route text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <div class="font-medium">Сессия навигации #{{ $session->id }}</div>
                        <div class="text-sm text-gray-600">
                            Начата: {{ $session->started_at->format('d.m.Y H:i') }}
                            @if($session->paused_at)
                                <span class="ml-2 text-yellow-600">
                                    <i class="fas fa-pause"></i> Приостановлена
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-800">{{ $progressPercentage }}%</div>
                    <div class="text-sm text-gray-600">
                        {{ $completedCheckpoints }}/{{ $totalCheckpoints }} точек
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Карта (левая часть) -->
        <div class="lg:w-2/3">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Карта навигации</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Масштаб: <span id="zoom-level">12</span></span>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div id="navigation-map" class="w-full h-[500px]"></div>
                    
                    <!-- Панель управления картой -->
                    <div class="absolute top-4 right-4 z-50">
                        <div class="bg-white rounded-lg shadow-lg p-2 space-y-2">
                            <button id="locate-me" 
                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors"
                                    title="Мое местоположение">
                                <i class="fas fa-location-arrow"></i>
                            </button>
                            <button id="zoom-in" 
                                    class="w-10 h-10 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50"
                                    title="Приблизить">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button id="zoom-out" 
                                    class="w-10 h-10 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50"
                                    title="Отдалить">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button id="fullscreen-btn" 
                                    class="w-10 h-10 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50"
                                    title="Полный экран">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Индикатор расстояния -->
                    <div id="distance-indicator" class="distance-indicator hidden">
                        <i class="fas fa-ruler-combined mr-2"></i>
                        <span id="distance-value">0 м</span>
                    </div>
                </div>
                
                <!-- Текущая информация под картой -->
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800">
                                        @if($currentCheckpoint)
                                            {{ $currentCheckpoint->point->title }}
                                        @elseif($session->isCompleted())
                                            Маршрут завершен! 🎉
                                        @else
                                            Ожидание старта...
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        @if($currentCheckpoint)
                                            {{ $currentCheckpoint->point->description ?: 'Без описания' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col md:items-end">
                            <div class="flex items-center space-x-4">
                                @if($currentCheckpoint)
                                    <div class="text-center">
                                        <div class="text-sm text-gray-500">Дистанция до точки</div>
                                        <div id="live-distance" class="text-lg font-bold text-gray-800">—</div>
                                    </div>
                                @endif
                                
                                <div class="text-center">
                                    <div class="text-sm text-gray-500">Прогресс</div>
                                    <div class="text-lg font-bold text-gray-800">{{ $progressPercentage }}%</div>
                                </div>
                            </div>
                            
                            <!-- Прогресс-бар -->
                            <div class="w-full md:w-64 mt-2">
                                <div class="progress-bar bg-gray-200 mb-1">
                                    <div class="bg-gradient-to-r from-blue-500 via-blue-400 to-green-500 h-full transition-all duration-300" 
                                         style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 text-right">
                                    {{ $completedCheckpoints }}/{{ $totalCheckpoints }} точек
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопки действий -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($currentCheckpoint && !$currentCheckpoint->isCompleted())
                            <button id="arrive-btn" 
                                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-medium flex items-center transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-map-marker-alt mr-2"></i> Я прибыл на точку
                            </button>
                            
                            <button onclick="skipCheckpoint({{ $currentCheckpoint->id }})"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 flex items-center">
                                <i class="fas fa-forward mr-2"></i> Пропустить точку
                            </button>
                        @endif
                        
                        <!-- Управление сессией -->
                        <div class="flex flex-wrap gap-2 ml-auto">
                            @if($session->isActive())
                                <form action="{{ route('routes.navigation.pause', $session) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium flex items-center">
                                        <i class="fas fa-pause mr-2"></i> Приостановить
                                    </button>
                                </form>
                            @elseif($session->isPaused())
                                <form action="{{ route('routes.navigation.resume', $session) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium flex items-center">
                                        <i class="fas fa-play mr-2"></i> Продолжить
                                    </button>
                                </form>
                            @endif
                            
                            <form action="{{ route('routes.navigation.complete', $session) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Вы уверены, что хотите завершить маршрут? Весь прогресс будет сохранен.')"
                                        class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg font-medium flex items-center shadow-md hover:shadow-lg">
                                    <i class="fas fa-flag-checkered mr-2"></i> Завершить маршрут
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Квесты и достижения -->
  <!-- Квесты и достижения -->
@if($session->quests()->count() > 0)
<div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fas fa-trophy mr-2 text-yellow-500"></i>
            Активные квесты
        </h2>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($session->quests() as $quest)
            <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="quest-badge quest-badge-purple">
                        <i class="fas fa-flag mr-1"></i> Квест
                    </span>
                    <span class="text-sm text-gray-500">{{ $quest->points_count }}/{{ $quest->required_points }} точек</span>
                </div>
                <h3 class="font-bold text-gray-800">{{ $quest->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $quest->description }}</p>
                
                <!-- Прогресс квеста -->
                <div class="mt-3">
                    <div class="flex justify-between text-sm text-gray-500 mb-1">
                        <span>Прогресс</span>
                        <span>{{ round(($quest->points_count / max($quest->required_points, 1)) * 100) }}%</span>
                    </div>
                    <div class="progress-bar bg-gray-200">
                        <div class="bg-gradient-to-r from-purple-400 to-purple-500 h-full"
                             style="width: {{ ($quest->points_count / max($quest->required_points, 1)) * 100 }}%"></div>
                    </div>
                </div>
                
                <!-- Награда -->
                @if($quest->reward_xp > 0 || $quest->reward_badge)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="flex items-center space-x-2 text-sm">
                        <span class="text-gray-500">Награда:</span>
                        @if($quest->reward_xp > 0)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">
                                <i class="fas fa-star mr-1"></i> {{ $quest->reward_xp }} XP
                            </span>
                        @endif
                        @if($quest->reward_badge)
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                <i class="fas fa-medal mr-1"></i> {{ $quest->reward_badge }}
                            </span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
        
        <!-- Сайдбар с точками (правая часть) -->
        <div class="lg:w-1/3">
            <div class="sticky top-4">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center justify-between">
                            <span>Точки маршрута</span>
                            <span class="text-sm font-normal text-gray-500">{{ $totalCheckpoints }} точек</span>
                        </h2>
                    </div>
                    
                    <div class="navigation-sidebar max-h-[600px]">
                        @foreach($checkpoints as $index => $checkpoint)
                        <div id="checkpoint-{{ $checkpoint->id }}"
                             class="checkpoint-card p-4 border-b border-gray-100 
                                    {{ $checkpoint->id == optional($currentCheckpoint)->id ? 'checkpoint-current' : '' }}
                                    {{ $checkpoint->isCompleted() ? 'checkpoint-completed' : 'checkpoint-pending' }}"
                             onclick="focusCheckpoint({{ $checkpoint->id }})">
                            <div class="flex items-start">
                                <!-- Номер точки -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3 
                                            {{ $checkpoint->isCompleted() ? 'bg-green-100 text-green-600' : 
                                               ($checkpoint->id == optional($currentCheckpoint)->id ? 'bg-blue-100 text-blue-600 pulse-animation' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $index + 1 }}
                                </div>
                                
                                <!-- Контент точки -->
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-bold text-gray-800">{{ $checkpoint->point->title }}</h3>
                                        @if($checkpoint->isCompleted())
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        @elseif($checkpoint->id == optional($currentCheckpoint)->id)
                                            <i class="fas fa-location-arrow text-blue-500 pulse-animation"></i>
                                        @endif
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-2">{{ Str::limit($checkpoint->point->description, 100) }}</p>
                                    
                                    <!-- Информация о точке -->
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        @if($checkpoint->point->type)
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                                <i class="fas fa-tag mr-1"></i> {{ $checkpoint->point->type }}
                                            </span>
                                        @endif
                                        
                                        @if($checkpoint->point->estimated_time > 0)
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                                <i class="fas fa-clock mr-1"></i> {{ $checkpoint->point->estimated_time }} мин
                                            </span>
                                        @endif
                                        
                                        @if($checkpoint->point->difficulty)
                                            <span class="px-2 py-1 
                                                {{ $checkpoint->point->difficulty == 'easy' ? 'bg-green-100 text-green-800' :
                                                   ($checkpoint->point->difficulty == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }} rounded">
                                                {{ ucfirst($checkpoint->point->difficulty) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Дополнительные задания -->
                                    @if($checkpoint->point->quests->count() > 0)
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($checkpoint->point->quests->take(2) as $quest)
                                                <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded text-xs">
                                                    <i class="fas fa-flag mr-1"></i> {{ $quest->name }}
                                                </span>
                                            @endforeach
                                            @if($checkpoint->point->quests->count() > 2)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                                                    +{{ $checkpoint->point->quests->count() - 2 }} ещё
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Статус и время -->
                                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                        <div>
                                            @if($checkpoint->isCompleted())
                                                <i class="fas fa-check mr-1"></i>
                                                Пройдено: {{ $checkpoint->completed_at->format('H:i') }}
                                            @elseif($checkpoint->id == optional($currentCheckpoint)->id)
                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                                Текущая точка
                                            @else
                                                <i class="far fa-clock mr-1"></i>
                                                Ожидание
                                            @endif
                                        </div>
                                        @if($checkpoint->distance_to_previous)
                                            <div>
                                                <i class="fas fa-road mr-1"></i>
                                                {{ $checkpoint->distance_to_previous }} км
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Быстрые действия -->
                    <div class="p-4 border-t border-gray-200">
                        <div class="flex space-x-2">
                            <button onclick="focusCurrentCheckpoint()"
                                    class="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-crosshairs mr-2"></i> К текущей
                            </button>
                            <button onclick="showAllCheckpoints()"
                                    class="flex-1 px-4 py-2 bg-gray-50 text-gray-600 rounded-lg font-medium hover:bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-expand mr-2"></i> Обзор всех
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Статистика маршрута -->
                <div class="mt-4 bg-white rounded-xl shadow-lg p-4">
                    <h3 class="font-bold text-gray-800 mb-3">Статистика маршрута</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $route->distance }} км</div>
                            <div class="text-sm text-gray-600">Общая дистанция</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $route->duration }} ч</div>
                            <div class="text-sm text-gray-600">Примерное время</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $session->completed_points_count }}</div>
                            <div class="text-sm text-gray-600">Пройдено точек</div>
                        </div>
                        <div class="text-center p-3 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $session->earned_xp ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Заработано XP</div>
                        </div>
                    </div>
                    
                    <!-- Время в пути -->
                    @if($session->started_at)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Время в пути:</span>
                            <span class="font-medium" id="elapsed-time">
                                {{ $session->getElapsedTime() }}
                            </span>
                        </div>
                        @if($session->average_speed > 0)
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Средняя скорость:</span>
                            <span class="font-medium">{{ round($session->average_speed, 1) }} км/ч</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно "Прибытие на точку" -->
<div id="arrived-modal" class="arrived-modal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Вы прибыли на точку!</h3>
                <button onclick="closeArrivedModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="arrived-content" class="space-y-4">
                <!-- Контент будет загружен динамически -->
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeArrivedModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Отмена
                </button>
                <button id="confirm-arrival"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Подтвердить
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Кнопка разрешения геолокации -->
<div id="geolocation-permission-banner" class="fixed bottom-20 right-4 z-50 hidden">
    <div class="bg-blue-500 text-white rounded-lg shadow-lg p-4 max-w-sm animate-slide-up">
        <div class="flex items-center mb-2">
            <i class="fas fa-map-marker-alt mr-2"></i>
            <h3 class="font-bold">Разрешите геолокацию</h3>
        </div>
        <p class="text-sm mb-3">Для работы навигатора необходимо разрешить доступ к вашему местоположению.</p>
        <div class="flex space-x-2">
            <button onclick="requestGeolocationPermission()" 
                    class="flex-1 bg-white text-blue-500 px-4 py-2 rounded font-medium hover:bg-blue-50">
                Разрешить
            </button>
            <button onclick="hideGeolocationBanner()" 
                    class="flex-1 bg-blue-600 text-white px-4 py-2 rounded font-medium hover:bg-blue-700">
                Позже
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-control-geocoder/dist/Control.Geocoder.min.js"></script>
<script>
// Глобальные переменные
let map;
let userMarker;
let accuracyCircle;
let routeLayer;
let checkpointMarkers = [];
let userWatchId;
let currentCheckpointId = {{ optional($currentCheckpoint)->id ?? 'null' }};
let arrivedCheckpointId = null;
let isTracking = false;
let visitedPoints = [];

// Отладочные данные - для тестирования
const DEBUG_MODE = true;

// Конфигурация карты
const mapConfig = {
    center: [55.7558, 37.6173],
    zoom: 12,
    maxZoom: 18,
    minZoom: 8
};

// Функция для отладки
function debugLog(message, data = null) {
    if (DEBUG_MODE) {
        if (data) {
            console.log(`[DEBUG] ${message}:`, data);
        } else {
            console.log(`[DEBUG] ${message}`);
        }
    }
}

// Инициализация карты
function initMap() {
    debugLog('Инициализация карты навигации');
    
    try {
        // Проверяем наличие элемента карты
        const mapElement = document.getElementById('navigation-map');
        if (!mapElement) {
            console.error('❌ Элемент карты не найден');
            showNotification('Элемент карты не найден', 'error');
            return;
        }
        
        debugLog('Создание карты');
        map = L.map('navigation-map', {
            zoomControl: false,
            attributionControl: false
        }).setView(mapConfig.center, mapConfig.zoom);
        
        // Добавляем базовый слой карты
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: mapConfig.maxZoom
        }).addTo(map);
        
        // Добавляем контроль масштаба
        L.control.scale({ imperial: false }).addTo(map);
        
        // Загружаем данные
        loadRouteAndCheckpoints();
        
        // Настройка элементов управления
        setupMapControls();
        
        // Инициализируем посещенные точки
        initVisitedPoints();
        
        // Пытаемся получить местоположение
        getUserLocation();
        
        debugLog('Карта успешно инициализирована');
        
    } catch (error) {
        console.error('❌ Ошибка инициализации карты:', error);
        showNotification('Ошибка инициализации карты: ' + error.message, 'error');
    }
}

// Инициализация посещенных точек
function initVisitedPoints() {
    @if(isset($visitedCheckpoints) && $visitedCheckpoints->count() > 0)
        visitedPoints = [
            @foreach($visitedCheckpoints as $checkpoint)
                {{ $checkpoint->point_id }},
            @endforeach
        ];
    @endif
    debugLog('Посещенные точки', visitedPoints);
}

// Загрузка маршрута и точек - УПРОЩЕННАЯ ВЕРСИЯ
function loadRouteAndCheckpoints() {
    debugLog('Загрузка маршрута и точек');
    
    try {
        // Пробуем получить данные разными способами
        let pointsData = [];
        let checkpointsData = [];
        
        try {
            // Способ 1: через PHP Blade
            pointsData = JSON.parse('{!! json_encode($route->points ?? []) !!}');
            checkpointsData = JSON.parse('{!! json_encode($checkpoints ?? []) !!}');
        } catch (e) {
            debugLog('Ошибка парсинга данных', e);
            
            // Способ 2: через прямую передачу
            pointsData = window.routePoints || [];
            checkpointsData = window.routeCheckpoints || [];
            
            // Способ 3: тестовые данные (если нет реальных)
            if (pointsData.length === 0) {
                pointsData = getTestPoints();
            }
        }
        
        debugLog('Данные точек', pointsData);
        debugLog('Данные чекпоинтов', checkpointsData);
        
        if (!Array.isArray(pointsData) || pointsData.length === 0) {
            console.warn('⚠️ Нет данных о точках маршрута');
            showNotification('У маршрута нет точек интереса', 'warning');
            
            // Показываем тестовую карту
            showTestMap();
            return;
        }
        
        // Сортируем точки по порядку
        const sortedPoints = pointsData.sort((a, b) => (a.order || 0) - (b.order || 0));
        
        // Создаем полилинию маршрута
        createRouteLine(sortedPoints);
        
        // Добавляем точки маршрута
        addCheckpointMarkers(sortedPoints, checkpointsData);
        
        // Обновляем счетчик
        updatePointsCounter();
        
    } catch (error) {
        console.error('❌ Ошибка загрузки маршрута:', error);
        showNotification('Ошибка загрузки маршрута', 'error');
        
        // Показываем тестовую карту как запасной вариант
        showTestMap();
    }
}

// Создание линии маршрута
function createRouteLine(points) {
    debugLog('Создание линии маршрута из точек', points.length);
    
    const routeCoordinates = points.map(point => {
        // Проверяем структуру данных
        if (point.lat && point.lng) {
            return [parseFloat(point.lat), parseFloat(point.lng)];
        } else if (point.latitude && point.longitude) {
            return [parseFloat(point.latitude), parseFloat(point.longitude)];
        } else if (Array.isArray(point) && point.length >= 2) {
            return [parseFloat(point[0]), parseFloat(point[1])];
        } else {
            console.warn('Неизвестный формат точки:', point);
            return null;
        }
    }).filter(coord => coord !== null);
    
    debugLog('Координаты маршрута', routeCoordinates);
    
    if (routeCoordinates.length > 1) {
        try {
            routeLayer = L.polyline(routeCoordinates, {
                color: '#f97316',
                weight: 6,
                opacity: 0.8,
                smoothFactor: 1,
                lineCap: 'round'
            }).addTo(map);
            
            // Устанавливаем обзор на весь маршрут
            const bounds = routeLayer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            debugLog('Линия маршрута создана');
            
        } catch (error) {
            console.error('❌ Ошибка создания линии маршрута:', error);
        }
    } else {
        console.warn('⚠️ Недостаточно точек для создания маршрута');
    }
}

// Добавление маркеров точек
function addCheckpointMarkers(points, checkpoints) {
    debugLog('Добавление маркеров точек', points.length);
    
    checkpointMarkers = [];
    
    points.forEach((point, index) => {
        try {
            // Извлекаем координаты
            let lat, lng;
            
            if (point.lat && point.lng) {
                lat = parseFloat(point.lat);
                lng = parseFloat(point.lng);
            } else if (point.latitude && point.longitude) {
                lat = parseFloat(point.latitude);
                lng = parseFloat(point.longitude);
            } else if (Array.isArray(point) && point.length >= 2) {
                lat = parseFloat(point[0]);
                lng = parseFloat(point[1]);
            } else {
                console.warn('Неизвестный формат координат:', point);
                return;
            }
            
            if (isNaN(lat) || isNaN(lng)) {
                console.warn('⚠️ Невалидные координаты:', point);
                return;
            }
            
            // Находим соответствующий checkpoint
            const checkpoint = Array.isArray(checkpoints) 
                ? checkpoints.find(cp => cp.point_id == point.id || cp.id == point.id)
                : null;
            
            const status = checkpoint ? checkpoint.status : 'pending';
            const isCurrent = checkpoint && checkpoint.id == currentCheckpointId;
            const isVisited = visitedPoints.includes(point.id) || status === 'completed';
            
            // Создаем иконку
            const icon = createCheckpointIcon(point, status, isCurrent, isVisited);
            
            // Создаем маркер
            const marker = L.marker([lat, lng], { icon }).addTo(map);
            
            // Создаем всплывающее окно
            const popupContent = createCheckpointPopup(point, checkpoint, index, status, isCurrent);
            marker.bindPopup(popupContent);
            
            // Сохраняем маркер
            checkpointMarkers.push({
                id: point.id || index,
                marker: marker,
                latlng: [lat, lng],
                checkpointId: checkpoint ? checkpoint.id : null,
                status: status,
                isVisited: isVisited
            });
            
        } catch (error) {
            console.error(`❌ Ошибка создания маркера для точки ${index}:`, error);
        }
    });
    
    debugLog('Создано маркеров', checkpointMarkers.length);
}

// Создание иконки для точки
function createCheckpointIcon(point, status, isCurrent, isVisited) {
    const colors = {
        'viewpoint': '#F59E0B',
        'cafe': '#EF4444', 
        'hotel': '#3B82F6',
        'attraction': '#6366F1',
        'gas_station': '#6B7280',
        'camping': '#10B981',
        'photo_spot': '#8B5CF6',
        'nature': '#059669',
        'historical': '#DC2626',
        'default': '#6B7280'
    };
    
    const icons = {
        'viewpoint': 'fas fa-binoculars',
        'cafe': 'fas fa-coffee',
        'hotel': 'fas fa-bed',
        'attraction': 'fas fa-landmark',
        'gas_station': 'fas fa-gas-pump',
        'camping': 'fas fa-campground',
        'photo_spot': 'fas fa-camera',
        'nature': 'fas fa-tree',
        'historical': 'fas fa-landmark',
        'default': 'fas fa-map-marker-alt'
    };
    
    const type = point.type || 'default';
    let color = colors[type] || colors.default;
    const iconClass = icons[type] || icons.default;
    
    // Меняем цвет по статусу
    if (isCurrent) {
        color = '#3B82F6';
    } else if (isVisited || status === 'completed') {
        color = '#10B981';
    } else if (status === 'skipped') {
        color = '#9CA3AF';
    }
    
    const size = isCurrent ? 48 : (isVisited ? 32 : 40);
    const opacity = isVisited ? 0.5 : 1;
    
    return L.divIcon({
        html: `
            <div style="
                width: ${size}px;
                height: ${size}px;
                background-color: white;
                border-radius: 50%;
                border: 3px solid ${color};
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                color: ${color};
                font-size: ${isCurrent ? '18px' : '16px'};
                opacity: ${opacity};
                position: relative;
            ">
                <i class="${iconClass}"></i>
                ${isCurrent ? `
                    <div style="
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        width: 20px;
                        height: 20px;
                        background-color: #3B82F6;
                        border-radius: 50%;
                        border: 2px solid white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 10px;
                    ">
                        <i class="fas fa-location-arrow"></i>
                    </div>
                ` : ''}
            </div>
        `,
        className: 'checkpoint-icon',
        iconSize: [size, size],
        iconAnchor: [size/2, size]
    });
}

// Создание всплывающего окна
function createCheckpointPopup(point, checkpoint, index, status, isCurrent) {
    const title = point.title || `Точка ${index + 1}`;
    const description = point.description || '';
    const type = point.type || 'other';
    
    let statusText, statusColor;
    switch(status) {
        case 'completed':
            statusText = '✓ Пройдена';
            statusColor = 'text-green-600';
            break;
        case 'active':
            statusText = '→ Текущая';
            statusColor = 'text-blue-600';
            break;
        case 'skipped':
            statusText = '⏭ Пропущена';
            statusColor = 'text-gray-500';
            break;
        default:
            statusText = '⏳ Ожидание';
            statusColor = 'text-gray-500';
    }
    
    return `
        <div class="p-3 min-w-64">
            <div class="flex items-start mb-2">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">${title}</h4>
                    <div class="text-sm text-gray-600 mt-1">Точка #${index + 1}</div>
                </div>
            </div>
            ${description ? `<p class="text-gray-700 text-sm mt-2">${description}</p>` : ''}
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex justify-between items-center text-xs">
                    <span>Статус:</span>
                    <span class="font-medium ${statusColor}">${statusText}</span>
                </div>
                ${checkpoint && checkpoint.arrived_at ? `
                    <div class="flex justify-between text-xs mt-1">
                        <span>Посещена:</span>
                        <span>${new Date(checkpoint.arrived_at).toLocaleTimeString()}</span>
                    </div>
                ` : ''}
                ${isCurrent ? `
                    <div class="mt-2">
                        <button onclick="focusCurrentCheckpoint()" 
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white text-xs py-1 rounded">
                            Сфокусироваться
                        </button>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Тестовые данные
function getTestPoints() {
    debugLog('Используем тестовые данные');
    
    return [
        {
            id: 1,
            title: 'Старт маршрута',
            description: 'Начальная точка маршрута',
            type: 'viewpoint',
            lat: 55.7558,
            lng: 37.6173,
            order: 1
        },
        {
            id: 2,
            title: 'Красная площадь',
            description: 'Главная площадь Москвы',
            type: 'attraction',
            lat: 55.7539,
            lng: 37.6208,
            order: 2
        },
        {
            id: 3,
            title: 'Кафе на Тверской',
            description: 'Уютное кафе в центре',
            type: 'cafe',
            lat: 55.7600,
            lng: 37.6100,
            order: 3
        }
    ];
}

// Тестовая карта
function showTestMap() {
    debugLog('Показываем тестовую карту');
    
    try {
        // Создаем тестовую линию
        const testCoords = [
            [55.7558, 37.6173],
            [55.7539, 37.6208],
            [55.7600, 37.6100]
        ];
        
        routeLayer = L.polyline(testCoords, {
            color: '#f97316',
            weight: 6,
            opacity: 0.8
        }).addTo(map);
        
        // Добавляем тестовые маркеры
        testCoords.forEach((coord, index) => {
            const icon = L.divIcon({
                html: `
                    <div style="
                        width: 40px;
                        height: 40px;
                        background-color: white;
                        border-radius: 50%;
                        border: 3px solid #3B82F6;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #3B82F6;
                    ">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                `,
                className: 'test-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });
            
            L.marker(coord, { icon })
                .addTo(map)
                .bindPopup(`Тестовая точка ${index + 1}`);
        });
        
        // Устанавливаем обзор
        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
        
        showNotification('Используются тестовые данные', 'warning');
        
    } catch (error) {
        console.error('❌ Ошибка создания тестовой карты:', error);
    }
}

// Настройка элементов управления
function setupMapControls() {
    debugLog('Настройка элементов управления');
    
    const controls = ['locate-me', 'zoom-in', 'zoom-out', 'fullscreen-btn', 'focus-current'];
    
    controls.forEach(controlId => {
        const element = document.getElementById(controlId);
        if (element) {
            element.addEventListener('click', function() {
                handleControlClick(controlId);
            });
        }
    });
    
    // Обновление уровня масштаба
    map.on('zoomend', function() {
        const zoomLevel = document.getElementById('zoom-level');
        if (zoomLevel) {
            zoomLevel.textContent = map.getZoom();
        }
    });
}

// Обработка кликов по контролам
function handleControlClick(controlId) {
    debugLog('Клик по контролу', controlId);
    
    switch(controlId) {
        case 'locate-me':
            getUserLocation(true);
            break;
            
        case 'zoom-in':
            map.zoomIn();
            break;
            
        case 'zoom-out':
            map.zoomOut();
            break;
            
        case 'fullscreen-btn':
            toggleFullscreen();
            break;
            
        case 'focus-current':
            focusCurrentCheckpoint();
            break;
    }
}

// Переключение полноэкранного режима
function toggleFullscreen() {
    const elem = document.getElementById('navigation-map');
    if (!document.fullscreenElement) {
        elem.requestFullscreen?.();
    } else {
        document.exitFullscreen?.();
    }
}

// Фокус на текущую точку
function focusCurrentCheckpoint() {
    if (currentCheckpointId) {
        const currentCheckpoint = @json($currentCheckpoint ?? null);
        if (currentCheckpoint && currentCheckpoint.point) {
            const latlng = [currentCheckpoint.point.lat, currentCheckpoint.point.lng];
            map.setView(latlng, 16);
            
            const marker = checkpointMarkers.find(m => 
                m.id == currentCheckpoint.point.id || 
                m.latlng[0] === latlng[0] && m.latlng[1] === latlng[1]
            );
            if (marker) {
                marker.marker.openPopup();
            }
        }
    } else if (checkpointMarkers.length > 0) {
        map.setView(checkpointMarkers[0].latlng, 16);
        checkpointMarkers[0].marker.openPopup();
    }
}

// Получение местоположения
function getUserLocation(force = false) {
    debugLog('Запрос геолокации', { force, isTracking });
    
    if (!navigator.geolocation) {
        showNotification('Геолокация не поддерживается', 'error');
        return;
    }
    
    if (isTracking && !force) {
        debugLog('Уже отслеживается');
        return;
    }
    
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    };
    
    navigator.geolocation.getCurrentPosition(
        handleGeolocationSuccess,
        handleGeolocationError,
        options
    );
}

// Успешное получение геолокации
function handleGeolocationSuccess(position) {
    debugLog('Геолокация успешна', position);
    
    const latlng = [position.coords.latitude, position.coords.longitude];
    
    if (!userMarker) {
        userMarker = L.marker(latlng, {
            icon: L.divIcon({
                html: `
                    <div style="
                        width: 40px;
                        height: 40px;
                        background-color: #3B82F6;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                    ">
                        <i class="fas fa-user"></i>
                    </div>
                `,
                className: 'user-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            })
        }).addTo(map);
    } else {
        userMarker.setLatLng(latlng);
    }
    
    map.setView(latlng, 15);
    showNotification('Местоположение определено', 'success');
    
    // Начинаем отслеживание
    if (!isTracking) {
        startLocationTracking();
    }
}

// Ошибка геолокации
function handleGeolocationError(error) {
    debugLog('Ошибка геолокации', error);
    
    let message = 'Не удалось определить местоположение';
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message = 'Доступ к геолокации запрещен';
            break;
        case error.POSITION_UNAVAILABLE:
            message = 'Информация о местоположении недоступна';
            break;
        case error.TIMEOUT:
            message = 'Время ожидания истекло';
            break;
    }
    
    showNotification(message, 'error');
}

// Начало отслеживания
function startLocationTracking() {
    if (userWatchId || !navigator.geolocation) return;
    
    debugLog('Начало отслеживания');
    
    const options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 1000
    };
    
    userWatchId = navigator.geolocation.watchPosition(
        function(position) {
            const latlng = [position.coords.latitude, position.coords.longitude];
            
            if (userMarker) {
                userMarker.setLatLng(latlng);
            }
            
            if (!window.userLocationInitialized) {
                map.setView(latlng, 15);
                window.userLocationInitialized = true;
            }
        },
        function(error) {
            debugLog('Ошибка отслеживания', error);
        },
        options
    );
    
    isTracking = true;
}

// Обновление счетчика точек
function updatePointsCounter() {
    try {
        const totalPoints = @json($route->points->count() ?? 0);
        const completedPoints = @json($completedCheckpoints ?? 0);
        
        const totalElement = document.getElementById('total-points-count');
        const completedElement = document.getElementById('completed-points-count');
        
        if (totalElement) totalElement.textContent = totalPoints;
        if (completedElement) completedElement.textContent = completedPoints;
        
        debugLog('Счетчик точек', { total: totalPoints, completed: completedPoints });
        
    } catch (error) {
        console.error('❌ Ошибка обновления счетчика:', error);
    }
}

// Показать уведомление
function showNotification(message, type = 'info') {
    debugLog('Показ уведомления', { message, type });
    
    // Создаем простейшее уведомление
    alert(`${type.toUpperCase()}: ${message}`);
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    debugLog('DOM загружен');
    
    // Проверяем наличие Leaflet
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet не загружен!');
        showNotification('Библиотека карт не загружена', 'error');
        return;
    }
    
    // Инициализируем карту с небольшой задержкой
    setTimeout(() => {
        try {
            initMap();
        } catch (error) {
            console.error('❌ Критическая ошибка:', error);
            showNotification('Критическая ошибка: ' + error.message, 'error');
        }
    }, 300);
    
    // Обработка кнопки прибытия
    const arriveBtn = document.getElementById('arrive-btn');
    if (arriveBtn) {
        arriveBtn.addEventListener('click', function() {
            if (currentCheckpointId) {
                showNotification('Точка отмечена как посещенная', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        });
    }
});
</script>

<style>
#navigation-map {
    width: 100%;
    height: 600px;
    border-radius: 0.5rem;
    z-index: 1;
}

.map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.map-control-btn {
    width: 44px;
    height: 44px;
    background: white;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.map-control-btn:hover {
    background: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.map-control-btn i {
    color: #4b5563;
    font-size: 18px;
}

.user-marker {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.checkpoint-icon {
    transition: opacity 0.3s ease;
}

.leaflet-popup-content {
    margin: 12px !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px !important;
    box-shadow: 0 3px 14px rgba(0,0,0,0.2) !important;
}

.leaflet-control-attribution {
    font-size: 11px !important;
}
</style>
@endpush