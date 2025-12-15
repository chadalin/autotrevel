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

// Конфигурация карты
const mapConfig = {
    center: [55.7558, 37.6173], // Москва по умолчанию
    zoom: 12,
    maxZoom: 18,
    minZoom: 8
};

// Инициализация карты
function initMap() {
    console.log('Инициализация карты навигации...');
    
    map = L.map('navigation-map', {
        zoomControl: false
    }).setView(mapConfig.center, mapConfig.zoom);
    
    // Добавляем базовый слой карты
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: mapConfig.maxZoom
    }).addTo(map);
    
    // Добавляем контроль масштаба
    L.control.scale().addTo(map);
    
    // Загружаем маршрут и точки
    loadRouteAndCheckpoints();
    
    // Настройка элементов управления
    setupMapControls();
    
    // Пытаемся получить текущее местоположение
    getUserLocation();
}

// Настройка элементов управления картой
function setupMapControls() {
    // Кнопка "Мое местоположение"
    document.getElementById('locate-me').addEventListener('click', function() {
        getUserLocation(true); // true = принудительно обновить
    });
    
    // Кнопки масштабирования
    document.getElementById('zoom-in').addEventListener('click', function() {
        map.zoomIn();
        updateZoomLevel();
    });
    
    document.getElementById('zoom-out').addEventListener('click', function() {
        map.zoomOut();
        updateZoomLevel();
    });
    
    // Кнопка полного экрана
    document.getElementById('fullscreen-btn').addEventListener('click', function() {
        const elem = document.getElementById('navigation-map');
        if (!document.fullscreenElement) {
            elem.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    });
    
    // Обновление уровня масштаба
    map.on('zoomend', updateZoomLevel);
}

// Обновление отображения уровня масштаба
function updateZoomLevel() {
    document.getElementById('zoom-level').textContent = map.getZoom();
}

// Получение местоположения пользователя
// Получение местоположения пользователя
function getUserLocation(force = false) {
    console.log('Запрос геолокации...', { force, isTracking });
    
    if (!navigator.geolocation) {
        showNotification('Геолокация не поддерживается вашим браузером', 'error');
        showManualLocationInput();
        return;
    }
    
    if (isTracking && !force) {
        console.log('Уже отслеживается, пропускаем...');
        return;
    }
    
    // Проверяем разрешение
    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' })
            .then(function(permissionStatus) {
                console.log('Статус разрешения:', permissionStatus.state);
                
                if (permissionStatus.state === 'denied') {
                    showNotification('Доступ к геолокации запрещен. Разрешите доступ в настройках браузера или установите местоположение вручную.', 'error');
                    showManualLocationInput();
                    return;
                }
                
                if (permissionStatus.state === 'prompt') {
                    showNotification('Разрешите доступ к геолокации для работы навигатора', 'info');
                }
                
                // Запрашиваем местоположение
                requestGeolocation();
            })
            .catch(function(error) {
                console.warn('Ошибка проверки разрешений:', error);
                // Продолжаем с запросом местоположения
                requestGeolocation();
            });
    } else {
        // Для браузеров без поддержки Permissions API
        requestGeolocation();
    }
}

// Запрос геолокации
function requestGeolocation() {
    console.log('Запрос геолокации...');
    
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    };
    
    navigator.geolocation.getCurrentPosition(
        // Успех
        function(position) {
            console.log('Геолокация успешна:', position);
            handleGeolocationSuccess(position);
        },
        // Ошибка
        function(error) {
            console.error('Ошибка геолокации:', error);
            handleGeolocationError(error);
        },
        options
    );
}

// Обработка успешного получения местоположения
function handleGeolocationSuccess(position) {
    const latlng = [position.coords.latitude, position.coords.longitude];
    
    console.log('Координаты получены:', latlng, 'точность:', position.coords.accuracy);
    
    // Создаем или обновляем маркер пользователя
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
                        animation: pulse 2s infinite;
                    ">
                        <i class="fas fa-user"></i>
                    </div>
                `,
                className: 'user-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            }),
            zIndexOffset: 1000
        }).addTo(map);
        
        userMarker.bindPopup('<b>Ваше местоположение</b>').openPopup();
        
        // Добавляем круг точности
        if (position.coords.accuracy) {
            accuracyCircle = L.circle(latlng, {
                radius: position.coords.accuracy,
                className: 'accuracy-circle',
                color: '#3B82F6',
                fillColor: '#3B82F6',
                weight: 1,
                fillOpacity: 0.1
            }).addTo(map);
        }
    } else {
        userMarker.setLatLng(latlng);
        if (accuracyCircle) {
            accuracyCircle.setLatLng(latlng).setRadius(position.coords.accuracy);
        }
    }
    
    // Центрируем карту на пользователе
    map.setView(latlng, 15);
    
    // Обновляем расстояние до текущей точки
    updateDistanceToCheckpoint(latlng);
    
    // Начинаем отслеживание
    if (!isTracking) {
        startLocationTracking();
    }
    
    showNotification('Местоположение определено', 'success');
    
    // Скрываем форму ручного ввода если есть
    const manualInput = document.getElementById('manual-location-input');
    if (manualInput) {
        manualInput.remove();
    }
}

// Обработка ошибки геолокации
function handleGeolocationError(error) {
    let message = 'Не удалось определить ваше местоположение';
    
    switch(error.code) {
        case error.PERMISSION_DENIED:
            console.log('PERMISSION_DENIED:', error);
            message = 'Доступ к геолокации запрещен. ';
            
            // Проверяем HTTPS
            if (window.location.protocol !== 'https:') {
                message += 'Работа геолокации требует HTTPS соединения. ';
            }
            
            message += 'Разрешите доступ в настройках браузера или установите местоположение вручную.';
            break;
            
        case error.POSITION_UNAVAILABLE:
            console.log('POSITION_UNAVAILABLE:', error);
            message = 'Информация о местоположении недоступна. Проверьте GPS или используйте ручной ввод.';
            break;
            
        case error.TIMEOUT:
            console.log('TIMEOUT:', error);
            message = 'Время ожидания получения местоположения истекло. Попробуйте снова или используйте ручной ввод.';
            break;
            
        default:
            console.log('Unknown error:', error);
            message = 'Произошла неизвестная ошибка при определении местоположения.';
    }
    
    showNotification(message, 'error');
    
    // Показываем кнопку для ручного ввода местоположения
    showManualLocationInput();
    
    // Если уже отслеживаем, останавливаем
    if (userWatchId) {
        navigator.geolocation.clearWatch(userWatchId);
        userWatchId = null;
        isTracking = false;
    }
}

// Улучшенная форма для ручного ввода местоположения
function showManualLocationInput() {
    // Если форма уже есть, не показываем снова
    if (document.getElementById('manual-location-input')) {
        return;
    }
    
    const manualLocationHTML = `
        <div id="manual-location-input" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4 animate-slide-up">
            <div class="flex items-center mb-3">
                <i class="fas fa-map-marker-alt text-yellow-600 mr-2"></i>
                <h3 class="font-bold text-yellow-800">Установить местоположение вручную</h3>
            </div>
            
            <div class="space-y-3">
                <div class="flex space-x-2">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-yellow-700 mb-1">Широта</label>
                        <input type="number" step="0.000001" id="manual-lat" 
                               placeholder="55.7558" 
                               class="w-full p-2 border border-yellow-300 rounded focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-yellow-700 mb-1">Долгота</label>
                        <input type="number" step="0.000001" id="manual-lng" 
                               placeholder="37.6173" 
                               class="w-full p-2 border border-yellow-300 rounded focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>
                
                <div class="text-sm text-yellow-600 mb-3">
                    <p>Примеры координат:</p>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <button type="button" onclick="setExample('moscow')" class="text-left hover:text-yellow-800">
                            <span class="font-medium">Москва:</span> 55.7558, 37.6173
                        </button>
                        <button type="button" onclick="setExample('spb')" class="text-left hover:text-yellow-800">
                            <span class="font-medium">СПб:</span> 59.9343, 30.3351
                        </button>
                        <button type="button" onclick="setExample('kazan')" class="text-left hover:text-yellow-800">
                            <span class="font-medium">Казань:</span> 55.7961, 49.1064
                        </button>
                        <button type="button" onclick="setExample('ekb')" class="text-left hover:text-yellow-800">
                            <span class="font-medium">Екатеринбург:</span> 56.8389, 60.6057
                        </button>
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <button onclick="setManualLocation()" 
                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-medium flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i> Установить
                    </button>
                    <button onclick="useCurrentLocation()" 
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-medium flex items-center justify-center">
                        <i class="fas fa-location-arrow mr-2"></i> Попробовать снова
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Добавляем под картой
    const mapContainer = document.querySelector('#navigation-map').parentElement;
    const div = document.createElement('div');
    div.innerHTML = manualLocationHTML;
    mapContainer.appendChild(div);
}

// Установить пример координат
function setExample(city) {
    const examples = {
        'moscow': { lat: 55.7558, lng: 37.6173 },
        'spb': { lat: 59.9343, lng: 30.3351 },
        'kazan': { lat: 55.7961, lng: 49.1064 },
        'ekb': { lat: 56.8389, lng: 60.6057 }
    };
    
    if (examples[city]) {
        document.getElementById('manual-lat').value = examples[city].lat;
        document.getElementById('manual-lng').value = examples[city].lng;
    }
}

// Попробовать снова получить текущее местоположение
function useCurrentLocation() {
    // Скрываем форму
    const manualInput = document.getElementById('manual-location-input');
    if (manualInput) {
        manualInput.remove();
    }
    
    // Пробуем снова
    getUserLocation(true);
}

// Начать отслеживание местоположения с улучшенной обработкой ошибок
function startLocationTracking() {
    if (userWatchId || !navigator.geolocation) {
        return;
    }
    
    console.log('Начало отслеживания местоположения...');
    
    const options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 1000
    };
    
    userWatchId = navigator.geolocation.watchPosition(
        function(position) {
            const latlng = [position.coords.latitude, position.coords.longitude];
            
            console.log('Обновление местоположения:', latlng);
            
            if (userMarker) {
                userMarker.setLatLng(latlng);
                if (accuracyCircle) {
                    accuracyCircle.setLatLng(latlng).setRadius(position.coords.accuracy);
                }
            }
            
            updateDistanceToCheckpoint(latlng);
            
            // Автоматическое приближение при первом получении
            if (!window.userLocationInitialized) {
                map.setView(latlng, 15);
                window.userLocationInitialized = true;
            }
        },
        function(error) {
            console.error('Ошибка отслеживания:', error);
            
            // Не показываем уведомление для каждой ошибки
            if (error.code === error.PERMISSION_DENIED) {
                console.log('Отслеживание остановлено: доступ запрещен');
                if (userWatchId) {
                    navigator.geolocation.clearWatch(userWatchId);
                    userWatchId = null;
                    isTracking = false;
                }
            }
        },
        options
    );
    
    isTracking = true;
    console.log('Отслеживание начато, watchId:', userWatchId);
}

// Показать форму для ручного ввода местоположения
function showManualLocationInput() {
    const manualLocationHTML = `
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
            <p class="text-yellow-800 mb-3">Определите местоположение вручную:</p>
            <div class="flex space-x-2">
                <input type="text" id="manual-lat" placeholder="Широта" class="flex-1 p-2 border rounded">
                <input type="text" id="manual-lng" placeholder="Долгота" class="flex-1 p-2 border rounded">
                <button onclick="setManualLocation()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Установить
                </button>
            </div>
            <p class="text-sm text-yellow-600 mt-2">Пример: 55.7558 37.6173 (Москва)</p>
        </div>
    `;
    
    // Добавляем под картой
    const mapContainer = document.querySelector('#navigation-map').parentElement;
    if (!document.getElementById('manual-location-input')) {
        const div = document.createElement('div');
        div.id = 'manual-location-input';
        div.innerHTML = manualLocationHTML;
        mapContainer.appendChild(div);
    }
}

// Установить местоположение вручную
function setManualLocation() {
    const lat = parseFloat(document.getElementById('manual-lat').value);
    const lng = parseFloat(document.getElementById('manual-lng').value);
    
    if (isNaN(lat) || isNaN(lng)) {
        showNotification('Введите корректные координаты', 'error');
        return;
    }
    
    const latlng = [lat, lng];
    
    // Создаем или обновляем маркер
    if (!userMarker) {
        userMarker = L.marker(latlng, {
            icon: L.divIcon({
                html: '<div class="w-8 h-8 bg-blue-600 rounded-full border-2 border-white shadow-lg"></div>',
                className: 'user-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            }),
            zIndexOffset: 1000
        }).addTo(map);
    } else {
        userMarker.setLatLng(latlng);
    }
    
    map.setView(latlng, 15);
    updateDistanceToCheckpoint(latlng);
    
    showNotification('Местоположение установлено вручную', 'success');
    
    // Скрываем форму
    const manualInput = document.getElementById('manual-location-input');
    if (manualInput) {
        manualInput.remove();
    }
}

// Начать отслеживание местоположения
function startLocationTracking() {
    if (userWatchId || !navigator.geolocation) {
        return;
    }
    
    userWatchId = navigator.geolocation.watchPosition(
        function(position) {
            const latlng = [position.coords.latitude, position.coords.longitude];
            
            if (userMarker) {
                userMarker.setLatLng(latlng);
                if (accuracyCircle) {
                    accuracyCircle.setLatLng(latlng).setRadius(position.coords.accuracy);
                }
            }
            
            updateDistanceToCheckpoint(latlng);
            
            // Автоматическое приближение при первом получении
            if (!window.userLocationInitialized) {
                map.setView(latlng, 15);
                window.userLocationInitialized = true;
            }
        },
        function(error) {
            console.error('Ошибка отслеживания:', error);
        },
        {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 1000
        }
    );
    
    isTracking = true;
}

// Загрузка маршрута и контрольных точек
// Загрузка маршрута и контрольных точек
function loadRouteAndCheckpoints() {
    // Получаем данные о точках из PHP - более безопасный способ
    const pointsData = JSON.parse(`{!! json_encode($route->points->map(function($point) {
        return [
            'id' => $point->id,
            'title' => addslashes($point->title),
            'description' => addslashes($point->description ?? ''),
            'type' => $point->type,
            'lat' => (float) $point->lat,
            'lng' => (float) $point->lng,
            'order' => (int) $point->order,
            'type_icon' => $point->type_icon,
            'type_label' => $point->type_label,
            'type_color' => $point->type_color
        ];
    })) !!}`);
    
    const checkpointsData = JSON.parse(`{!! json_encode($checkpoints) !!}`);
    
    console.log('Загрузка точек:', pointsData.length, 'точки');
    console.log('Контрольные точки:', checkpointsData.length);
    
    if (pointsData.length === 0) {
        showNotification('У маршрута нет точек интереса. Добавьте точки в маршрут.', 'warning');
        return;
    }
    
    // Создаем полилинию маршрута
    const routeCoordinates = pointsData
        .sort((a, b) => a.order - b.order)
        .map(point => [point.lat, point.lng]);
    
    if (routeCoordinates.length > 1) {
        routeLayer = L.polyline(routeCoordinates, {
            color: '#f97316',
            weight: 4,
            opacity: 0.7,
            smoothFactor: 1
        }).addTo(map);
        
        // Устанавливаем обзор на весь маршрут
        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
    }
    
    // Добавляем точки маршрута
    pointsData.forEach((point, index) => {
        // Находим соответствующий checkpoint
        const checkpoint = checkpointsData.find(cp => cp.point_id == point.id);
        const status = checkpoint ? checkpoint.status : 'pending';
        const isCurrent = checkpoint && checkpoint.id == currentCheckpointId;
        
        const icon = getCheckpointIcon(point.type, status, isCurrent);
        
        const marker = L.marker([point.lat, point.lng], { icon })
            .addTo(map)
            .bindPopup(`
                <div class="p-2 min-w-64">
                    <div class="flex items-start mb-2">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                             style="background-color: ${point.type_color}20; color: ${point.type_color};">
                            <i class="${point.type_icon}"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">${point.title}</h4>
                            <div class="text-sm text-gray-600 mt-1">${point.type_label}</div>
                            <div class="text-xs text-gray-500 mt-1">Точка #${index + 1}</div>
                        </div>
                    </div>
                    ${point.description ? `<p class="text-gray-700 text-sm mt-2">${point.description}</p>` : ''}
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex justify-between text-xs">
                            <span>Статус:</span>
                            <span class="font-medium ${
                                status === 'completed' ? 'text-green-600' :
                                status === 'active' ? 'text-blue-600' :
                                'text-gray-500'
                            }">
                                ${
                                    status === 'completed' ? '✓ Пройдена' :
                                    status === 'active' ? '→ Текущая' :
                                    status === 'skipped' ? '⏭ Пропущена' :
                                    '⏳ Ожидание'
                                }
                            </span>
                        </div>
                        ${checkpoint && checkpoint.arrived_at ? `
                            <div class="flex justify-between text-xs mt-1">
                                <span>Посещена:</span>
                                <span>${new Date(checkpoint.arrived_at).toLocaleTimeString()}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `);
        
        checkpointMarkers.push({
            id: point.id,
            marker: marker,
            latlng: [point.lat, point.lng],
            checkpointId: checkpoint ? checkpoint.id : null,
            status: status
        });
    });
    
    // Фокус на текущую точку если есть
    if (currentCheckpointId) {
        const currentCheckpoint = checkpointsData.find(cp => cp.id == currentCheckpointId);
        if (currentCheckpoint) {
            const point = pointsData.find(p => p.id == currentCheckpoint.point_id);
            if (point) {
                map.setView([point.lat, point.lng], 15);
                // Открываем popup текущей точки
                const marker = checkpointMarkers.find(m => m.id == point.id);
                if (marker) {
                    marker.marker.openPopup();
                }
            }
        }
    }
    
    // Обновляем счетчик точек
    updatePointsCounter();
}

// Получение иконки для точки
function getCheckpointIcon(type, status, isCurrent = false) {
    let iconColor, iconClass, iconBg;
    
    // Определяем цвет по типу точки
    const typeColors = {
        'viewpoint': '#F59E0B',
        'cafe': '#EF4444', 
        'hotel': '#3B82F6',
        'attraction': '#6366F1',
        'gas_station': '#6B7280',
        'camping': '#10B981',
        'photo_spot': '#8B5CF6',
        'nature': '#059669',
        'historical': '#DC2626',
        'other': '#6B7280'
    };
    
    const typeIcons = {
        'viewpoint': 'fas fa-binoculars',
        'cafe': 'fas fa-coffee',
        'hotel': 'fas fa-bed',
        'attraction': 'fas fa-landmark',
        'gas_station': 'fas fa-gas-pump',
        'camping': 'fas fa-campground',
        'photo_spot': 'fas fa-camera',
        'nature': 'fas fa-tree',
        'historical': 'fas fa-landmark',
        'other': 'fas fa-map-marker-alt'
    };
    
    iconColor = typeColors[type] || '#6B7280';
    iconClass = typeIcons[type] || 'fas fa-map-marker-alt';
    
    // Меняем цвет по статусу
    if (isCurrent) {
        iconColor = '#3B82F6'; // Синий для текущей
        iconBg = '#EFF6FF';
    } else if (status === 'completed') {
        iconColor = '#10B981'; // Зеленый для пройденной
        iconBg = '#ECFDF5';
    } else if (status === 'skipped') {
        iconColor = '#9CA3AF'; // Серый для пропущенной
        iconBg = '#F9FAFB';
    } else {
        iconBg = '#FFFFFF';
    }
    
    // Размер иконки в зависимости от статуса
    const size = isCurrent ? 48 : 40;
    const borderWidth = isCurrent ? 4 : 3;
    
    return L.divIcon({
        html: `
            <div style="
                width: ${size}px;
                height: ${size}px;
                background-color: ${iconBg};
                border-radius: 50%;
                border: ${borderWidth}px solid ${iconColor};
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                color: ${iconColor};
                font-size: ${isCurrent ? '18px' : '16px'};
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

// Обновление расстояния до текущей контрольной точки
function updateDistanceToCheckpoint(userLatLng) {
    if (!currentCheckpointId) {
        document.getElementById('live-distance').textContent = '—';
        return;
    }
    
    // Находим текущий checkpoint
    const currentCheckpoint = @json($currentCheckpoint);
    if (!currentCheckpoint || !currentCheckpoint.point) {
        document.getElementById('live-distance').textContent = '—';
        return;
    }
    
    const checkpointLatLng = [currentCheckpoint.point.lat, currentCheckpoint.point.lng];
    const distance = calculateDistance(
        userLatLng[0], userLatLng[1],
        checkpointLatLng[0], checkpointLatLng[1]
    );
    
    // Форматируем расстояние
    let formattedDistance;
    if (distance < 1000) {
        formattedDistance = Math.round(distance) + ' м';
    } else if (distance < 10000) {
        formattedDistance = (distance / 1000).toFixed(1) + ' км';
    } else {
        formattedDistance = Math.round(distance / 1000) + ' км';
    }
    
    document.getElementById('live-distance').textContent = formattedDistance;
    
    // Показываем/скрываем индикатор расстояния
    const distanceIndicator = document.getElementById('distance-indicator');
    const distanceValue = document.getElementById('distance-value');
    
    if (distanceIndicator && distanceValue) {
        distanceValue.textContent = formattedDistance;
        
        // Показываем индикатор если расстояние больше 50 метров
        if (distance > 50) {
            distanceIndicator.classList.remove('hidden');
            
            // Меняем цвет в зависимости от расстояния
            if (distance < 100) {
                distanceIndicator.style.backgroundColor = 'rgba(34, 197, 94, 0.9)'; // Зеленый
            } else if (distance < 500) {
                distanceIndicator.style.backgroundColor = 'rgba(234, 179, 8, 0.9)'; // Желтый
            } else {
                distanceIndicator.style.backgroundColor = 'rgba(239, 68, 68, 0.9)'; // Красный
            }
        } else {
            distanceIndicator.classList.add('hidden');
        }
    }
}

// Обновление счетчика точек
function updatePointsCounter() {
    const totalPoints = @json($route->points->count());
    const completedPoints = @json($completedCheckpoints);
    
    document.getElementById('total-points-count').textContent = totalPoints;
    document.getElementById('completed-points-count').textContent = completedPoints;
}

// Вычисление расстояния между двумя точками (в метрах)
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Радиус Земли в метрах
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;
    
    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    
    return R * c;
}

// Показать уведомление
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg flex items-center animate-slide-up ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    
    notification.innerHTML = `
        <i class="fas ${
            type === 'success' ? 'fa-check-circle' :
            type === 'error' ? 'fa-exclamation-circle' :
            type === 'warning' ? 'fa-exclamation-triangle' :
            'fa-info-circle'
        } mr-3"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматическое скрытие
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Закрытие по клику
    notification.addEventListener('click', () => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    });
}

// Фокус на точку
function focusCheckpoint(pointId) {
    const marker = checkpointMarkers.find(m => m.id == pointId);
    if (marker) {
        map.setView(marker.latlng, 16);
        marker.marker.openPopup();
    }
}

// Фокус на текущую точку
function focusCurrentCheckpoint() {
    if (currentCheckpointId) {
        const currentCheckpoint = @json($currentCheckpoint);
        if (currentCheckpoint && currentCheckpoint.point) {
            map.setView([currentCheckpoint.point.lat, currentCheckpoint.point.lng], 16);
            
            const marker = checkpointMarkers.find(m => m.id == currentCheckpoint.point.id);
            if (marker) {
                marker.marker.openPopup();
            }
        }
    } else {
        // Показываем весь маршрут
        if (routeLayer) {
            map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
        }
    }
}

// Показать все точки
function showAllCheckpoints() {
    if (routeLayer) {
        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
    } else if (checkpointMarkers.length > 0) {
        const group = new L.featureGroup(checkpointMarkers.map(m => m.marker));
        map.fitBounds(group.getBounds(), { padding: [50, 50] });
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализируем карту...');
    
    // Даем время на загрузку Leaflet
    setTimeout(() => {
        try {
            initMap();
        } catch (error) {
            console.error('Ошибка инициализации карты:', error);
            showNotification('Ошибка загрузки карты', 'error');
        }
    }, 500);
    
    // Обработка прибытия на точку
    document.getElementById('arrive-btn')?.addEventListener('click', function() {
        if (!currentCheckpointId) return;
        
        if (confirm('Вы прибыли на текущую точку маршрута?')) {
            fetch(`/navigation/checkpoint/${currentCheckpointId}/arrive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Точка успешно отмечена как пройденная!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Ошибка', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Произошла ошибка', 'error');
            });
        }
    });
    
    // Обработка пропуска точки
    window.skipCheckpoint = function(checkpointId) {
        if (confirm('Пропустить эту точку? Прогресс квестов может быть затронут.')) {
            fetch(`/navigation/checkpoint/${checkpointId}/skip`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Точка пропущена', 'warning');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Ошибка', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Произошла ошибка', 'error');
            });
        }
    };
    
    // Обработка клавиш
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Закрытие попапов
            map.closePopup();
        }
    });
    
    // Очистка при закрытии страницы
    window.addEventListener('beforeunload', function() {
        if (userWatchId) {
            navigator.geolocation.clearWatch(userWatchId);
        }
    });
});
</script>
@endpush
@endsection