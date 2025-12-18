@extends('layouts.app')

@section('title', $route->title . ' - Навигация')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    .navigation-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background-color: #f8fafc;
    }
    
    #navigation-map {
        flex: 1;
        min-height: 500px;
        width: 100%;
        z-index: 1;
    }
    
    .navigation-sidebar {
        position: fixed;
        top: 0;
        right: 0;
        width: 400px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 15px rgba(0,0,0,0.1);
        z-index: 1000;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    
    .navigation-sidebar.active {
        transform: translateX(0);
    }
    
    .floating-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1001;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .checkpoint-marker {
        background: transparent !important;
        border: none !important;
    }
    
    .progress-bar-container {
        width: 100%;
        background-color: #e5e7eb;
        border-radius: 9999px;
        height: 8px;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 9999px;
        transition: width 0.3s ease;
    }
    
    .checkpoint-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    
    .checkpoint-card.active {
        border-left-color: #3b82f6;
        background-color: #eff6ff;
    }
    
    .checkpoint-card.completed {
        border-left-color: #10b981;
        background-color: #ecfdf5;
    }
    
    .quest-card {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 2px solid #fbbf24;
    }
    
    .task-card {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
    }
    
    .task-card.completed {
        background-color: #dcfce7;
        border-color: #86efac;
    }
    
    @media (max-width: 768px) {
        .navigation-sidebar {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="navigation-container">
    <!-- Карта -->
    <div id="navigation-map">
        <div class="absolute top-4 left-4 z-10 flex gap-2">
            <button id="toggle-sidebar" class="bg-white p-3 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                <i class="fas fa-bars text-gray-700"></i>
            </button>
            <a href="{{ route('routes.show', $route) }}" 
               class="bg-white p-3 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                <i class="fas fa-times text-gray-700"></i>
            </a>
        </div>
        
        <!-- Индикатор прогресса -->
        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10 bg-white rounded-lg shadow-lg p-4 min-w-[300px]">
            <div class="flex items-center justify-between mb-2">
                <div class="font-bold text-lg text-gray-800">{{ $route->title }}</div>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    {{ $progressPercentage }}%
                </span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $progressPercentage }}%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mt-1">
                <span>{{ $completedCheckpoints }} из {{ $totalCheckpoints }} точек</span>
                <span>{{ $session->duration_seconds ? gmdate('H:i:s', $session->duration_seconds) : '00:00:00' }}</span>
            </div>
        </div>
    </div>
    
    <!-- Боковая панель -->
    <div class="navigation-sidebar" id="sidebar">
        <div class="p-6">
            <!-- Заголовок -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Навигация</h2>
                    <p class="text-gray-600">{{ $route->title }}</p>
                </div>
                <button id="close-sidebar" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
            
            <!-- Информация о сессии -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Статус</div>
                        <div class="flex items-center">
                            <div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>
                            <span class="font-medium text-gray-800">
                                {{ $session->status === 'active' ? 'Активна' : 'На паузе' }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600 mb-1">Расстояние</div>
                        <div class="font-bold text-gray-800">{{ $route->length_km }} км</div>
                    </div>
                </div>
                
                <div class="flex justify-between">
                    @if($session->status === 'active')
                        <form action="{{ route('routes.navigation.pause', $session) }}" method="POST" class="flex-1 mr-2">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg font-medium">
                                <i class="fas fa-pause mr-2"></i>Пауза
                            </button>
                        </form>
                    @else
                        <form action="{{ route('routes.navigation.resume', $session) }}" method="POST" class="flex-1 mr-2">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-medium">
                                <i class="fas fa-play mr-2"></i>Продолжить
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('routes.navigation.complete', $session) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Завершить навигацию?')"
                                class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg font-medium">
                            <i class="fas fa-flag-checkered mr-2"></i>Завершить
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Текущий чекпоинт -->
            @if($currentCheckpoint)
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                        Текущая точка
                    </h3>
                    <div class="bg-white rounded-xl shadow border border-gray-200 p-5">
                        <div class="flex items-start mb-4">
                            <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center mr-4">
                                <i class="fas fa-bullseye text-red-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 text-lg">{{ $currentCheckpoint->title }}</h4>
                                <div class="flex items-center mt-1">
                                    <span class="text-sm text-gray-600 mr-3">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ number_format($currentCheckpoint->latitude, 5) }}, {{ number_format($currentCheckpoint->longitude, 5) }}
                                    </span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                        #{{ $currentCheckpoint->order }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($currentCheckpoint->description)
                            <p class="text-gray-700 mb-4">{{ $currentCheckpoint->description }}</p>
                        @endif
                        
                        <div class="flex gap-2">
                            <button onclick="arriveAtCheckpoint({{ $currentCheckpoint->id }})" 
                                    class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white py-3 rounded-lg font-bold">
                                <i class="fas fa-check-circle mr-2"></i>Прибыл
                            </button>
                            <button onclick="skipCheckpoint({{ $currentCheckpoint->id }})" 
                                    class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium">
                                <i class="fas fa-forward mr-2"></i>Пропустить
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Активные квесты -->
            @if($activeQuests && $activeQuests->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tasks text-purple-500 mr-2"></i>
                        Активные квесты
                    </h3>
                    
                    @foreach($activeQuests as $quest)
                        <div class="quest-card rounded-xl p-4 mb-3">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $quest->title }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $quest->short_description }}</p>
                                </div>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                    {{ $quest->difficulty_label }}
                                </span>
                            </div>
                            
                            <!-- Прогресс квеста -->
                            <div class="mb-3">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Прогресс квеста</span>
                                    <span>{{ $quest->userProgress->progress_percentage ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" 
                                         style="width: {{ $quest->userProgress->progress_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Задания квеста -->
                            @if($quest->tasks && $quest->tasks->count() > 0)
                                <div class="space-y-2">
                                    @foreach($quest->tasks as $task)
                                        @php
                                            $taskProgress = $task->userProgress ?? null;
                                            $isCompleted = $taskProgress && $taskProgress->status === 'completed';
                                        @endphp
                                        <div class="task-card rounded-lg p-3 {{ $isCompleted ? 'completed' : '' }}">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 
                                                                {{ $isCompleted ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                                                        <i class="{{ $task->type_icon }} text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium text-gray-800">{{ $task->title }}</div>
                                                        <div class="text-xs text-gray-600">{{ $task->type_label }}</div>
                                                    </div>
                                                </div>
                                                
                                                @if($isCompleted)
                                                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                                @elseif($task->canBeCompleted($currentCheckpoint))
                                                    <button onclick="completeTask({{ $task->id }})" 
                                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                                        Выполнить
                                                    </button>
                                                @endif
                                            </div>
                                            
                                            @if($task->description)
                                                <p class="text-sm text-gray-700 mt-2">{{ $task->description }}</p>
                                            @endif
                                            
                                            @if($task->points > 0)
                                                <div class="text-xs text-yellow-600 mt-2">
                                                    <i class="fas fa-star mr-1"></i>+{{ $task->points }} очков
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Награда квеста -->
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-yellow-200">
                                <div class="text-sm">
                                    <span class="text-gray-600">Награда:</span>
                                    <span class="font-bold text-green-600 ml-2">+{{ $quest->reward_exp }} XP</span>
                                    @if($quest->reward_coins > 0)
                                        <span class="font-bold text-yellow-600 ml-2">+{{ $quest->reward_coins }} монет</span>
                                    @endif
                                </div>
                                @if($quest->badge)
                                    <div class="text-sm">
                                        <i class="fas fa-medal text-purple-500 mr-1"></i>
                                        <span class="text-gray-600">Значок:</span>
                                        <span class="font-bold text-purple-600 ml-1">{{ $quest->badge->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- Список чекпоинтов -->
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-route text-blue-500 mr-2"></i>
                    Маршрут ({{ $checkpoints->count() }} точек)
                </h3>
                
                <div class="space-y-3">
                    @foreach($checkpoints as $checkpoint)
                        @php
                            $isActive = $currentCheckpoint && $checkpoint->id === $currentCheckpoint->id;
                            $isCompleted = $checkpoint->isCompleted();
                        @endphp
                        
                        <div class="checkpoint-card rounded-lg p-4 {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-4
                                            {{ $isActive ? 'bg-blue-100 text-blue-600' : 
                                               ($isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600') }}">
                                    @if($isCompleted)
                                        <i class="fas fa-check"></i>
                                    @elseif($isActive)
                                        <i class="fas fa-bullseye"></i>
                                    @else
                                        <span class="font-bold">{{ $checkpoint->order }}</span>
                                    @endif
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-gray-800 truncate">{{ $checkpoint->title }}</h4>
                                        <span class="text-sm text-gray-500 ml-2">
                                            @if($checkpoint->distance_to_next)
                                                {{ number_format($checkpoint->distance_to_next, 1) }} км
                                            @endif
                                        </span>
                                    </div>
                                    
                                    @if($checkpoint->description)
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $checkpoint->description }}</p>
                                    @endif
                                    
                                    <div class="flex items-center mt-2">
                                        <span class="text-xs px-2 py-1 rounded mr-2 
                                                    {{ $isActive ? 'bg-blue-100 text-blue-800' : 
                                                       ($isCompleted ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                            <i class="fas fa-map-marker-alt text-xs mr-1"></i>
                                            {{ $checkpoint->type_label }}
                                        </span>
                                        
                                        @if($checkpoint->hasQuests())
                                            <span class="text-xs px-2 py-1 bg-purple-100 text-purple-800 rounded">
                                                <i class="fas fa-tasks text-xs mr-1"></i>
                                                {{ $checkpoint->quests_count }} квестов
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Статистика -->
            <div class="bg-gray-50 rounded-xl p-5">
                <h4 class="font-bold text-gray-800 mb-3">Статистика сессии</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $session->distance_traveled ?? 0 }}</div>
                        <div class="text-sm text-gray-600">км пройдено</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $session->duration_formatted ?? '00:00' }}</div>
                        <div class="text-sm text-gray-600">время</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $session->average_speed ?? 0 }}</div>
                        <div class="text-sm text-gray-600">км/ч</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $earnedXp ?? 0 }}</div>
                        <div class="text-sm text-gray-600">XP заработано</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Плавающие контролы -->
    <div class="floating-controls">
        <button id="my-location" class="bg-white p-4 rounded-full shadow-lg hover:shadow-xl transition-shadow">
            <i class="fas fa-location-arrow text-blue-600 text-xl"></i>
        </button>
        <button id="center-route" class="bg-white p-4 rounded-full shadow-lg hover:shadow-xl transition-shadow">
            <i class="fas fa-route text-green-600 text-xl"></i>
        </button>
        <button id="take-photo" class="bg-white p-4 rounded-full shadow-lg hover:shadow-xl transition-shadow">
            <i class="fas fa-camera text-purple-600 text-xl"></i>
        </button>
    </div>
</div>

<!-- Модальное окно для прибытия на точку -->
<div id="arrival-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1002] flex items-center justify-center">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Вы прибыли на точку?</h3>
        <p class="text-gray-600 mb-6">Подтвердите, что вы достигли текущей контрольной точки.</p>
        
        <form id="arrival-form">
            @csrf
            <input type="hidden" id="checkpoint-id" name="checkpoint_id">
            
            <div class="mb-4">
                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Комментарий (опционально)</label>
                <textarea id="comment" name="comment" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                          placeholder="Поделитесь впечатлениями..."></textarea>
            </div>
            
            <div class="mb-6">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Добавить фото</label>
                <input type="file" id="photo" name="photo" accept="image/*" 
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0 file:text-sm file:font-medium
                              file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeArrivalModal()"
                        class="px-4 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                    Отмена
                </button>
                <button type="submit"
                        class="px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold hover:from-green-600 hover:to-emerald-700">
                    <i class="fas fa-check-circle mr-2"></i>Подтвердить прибытие
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для выполнения задания -->
<div id="task-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1002] flex items-center justify-center">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div id="task-content">
            <!-- Контент будет загружен динамически -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
// Глобальные переменные
let navigationMap = null;
let routeLayer = null;
let checkpointMarkers = [];
let currentPositionMarker = null;
let currentPosition = null;
let watchId = null;

// Инициализация карты
function initNavigationMap() {
    if (!document.getElementById('navigation-map')) return;
    
    try {
        // Создаем карту
        navigationMap = L.map('navigation-map').setView([{{ $route->start_lat ?? 55.7558 }}, {{ $route->start_lng ?? 37.6173 }}], 12);
        
        // Добавляем тайлы
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(navigationMap);
        
        // Добавляем маршрут
        @if($route->coordinates)
            const coordinates = @json($route->coordinates);
            if (coordinates && coordinates.length > 0) {
                routeLayer = L.polyline(coordinates, {
                    color: '#3b82f6',
                    weight: 4,
                    opacity: 0.8,
                    smoothFactor: 1
                }).addTo(navigationMap);
                
                // Фокусируем на маршруте
                navigationMap.fitBounds(routeLayer.getBounds());
            }
        @endif
        
        // Добавляем чекпоинты
        @foreach($checkpoints as $checkpoint)
            const checkpoint{{ $checkpoint->id }} = L.marker([{{ $checkpoint->latitude }}, {{ $checkpoint->longitude }}], {
                icon: L.divIcon({
                    html: `
                        <div style="
                            width: 40px;
                            height: 40px;
                            background-color: {{ $checkpoint->id === $currentCheckpoint->id ? '#ef4444' : 
                                               ($checkpoint->isCompleted() ? '#10b981' : '#6b7280') }};
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-weight: bold;
                            font-size: 14px;
                        ">
                            ${ {{ $checkpoint->isCompleted() ? '<i class="fas fa-check"></i>' : 
                                 ($checkpoint->id === $currentCheckpoint->id ? '<i class="fas fa-bullseye"></i>' : 
                                  $checkpoint->order) }} }
                        </div>
                    `,
                    className: 'checkpoint-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                })
            }).addTo(navigationMap);
            
            checkpoint{{ $checkpoint->id }}.bindPopup(`
                <div class="p-2">
                    <div class="font-bold text-gray-800 mb-1">{{ $checkpoint->title }}</div>
                    <div class="text-sm text-gray-600">{{ $checkpoint->type_label }}</div>
                    @if($checkpoint->description)
                        <div class="text-sm text-gray-700 mt-2">{{ $checkpoint->description }}</div>
                    @endif
                </div>
            `);
            
            checkpointMarkers.push(checkpoint{{ $checkpoint->id }});
        @endforeach
        
        // Запрашиваем геолокацию
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showCurrentPosition, handleLocationError, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
            
            // Начинаем отслеживание позиции
            watchId = navigator.geolocation.watchPosition(updateCurrentPosition, handleLocationError, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
        }
        
    } catch (error) {
        console.error('Ошибка инициализации карты:', error);
    }
}

// Показать текущую позицию
function showCurrentPosition(position) {
    currentPosition = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
    };
    
    currentPositionMarker = L.marker([currentPosition.lat, currentPosition.lng], {
        icon: L.divIcon({
            html: '<div style="width: 24px; height: 24px; background-color: #3b82f6; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            className: 'current-position-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        })
    }).addTo(navigationMap);
    
    currentPositionMarker.bindPopup('Ваше текущее местоположение');
}

// Обновить текущую позицию
function updateCurrentPosition(position) {
    const newPos = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
    };
    
    if (currentPositionMarker) {
        currentPositionMarker.setLatLng([newPos.lat, newPos.lng]);
    } else {
        showCurrentPosition(position);
    }
    
    currentPosition = newPos;
}

// Обработка ошибок геолокации
function handleLocationError(error) {
    console.warn('Ошибка геолокации:', error.message);
    // Можно показать уведомление пользователю
}

// Прибытие на чекпоинт
function arriveAtCheckpoint(checkpointId) {
    document.getElementById('checkpoint-id').value = checkpointId;
    document.getElementById('arrival-modal').classList.remove('hidden');
}

// Пропустить чекпоинт
function skipCheckpoint(checkpointId) {
    if (confirm('Вы уверены, что хотите пропустить эту точку?')) {
        fetch(`/api/checkpoints/${checkpointId}/skip`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка: ' + (data.message || 'Не удалось пропустить точку'));
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Ошибка при отправке запроса');
        });
    }
}

// Выполнить задание
function completeTask(taskId) {
    fetch(`/api/tasks/${taskId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показать модальное окно с результатом
            showTaskResult(data.task);
        } else {
            alert('Ошибка: ' + (data.message || 'Не удалось выполнить задание'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при отправке запроса');
    });
}

// Показать результат выполнения задания
function showTaskResult(task) {
    const modalContent = `
        <div>
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Задание выполнено!</h3>
                <p class="text-gray-600">${task.title}</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-600">Получено очков:</span>
                    <span class="font-bold text-green-600">+${task.points}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Статус:</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Выполнено</span>
                </div>
            </div>
            
            <button onclick="closeTaskModal()" 
                    class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 rounded-lg font-bold">
                Продолжить
            </button>
        </div>
    `;
    
    document.getElementById('task-content').innerHTML = modalContent;
    document.getElementById('task-modal').classList.remove('hidden');
}

// Закрыть модальное окно прибытия
function closeArrivalModal() {
    document.getElementById('arrival-modal').classList.add('hidden');
    document.getElementById('arrival-form').reset();
}

// Закрыть модальное окно задания
function closeTaskModal() {
    document.getElementById('task-modal').classList.add('hidden');
    location.reload();
}

// Отправка формы прибытия
document.getElementById('arrival-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const checkpointId = document.getElementById('checkpoint-id').value;
    
    fetch(`/api/checkpoints/${checkpointId}/arrive`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeArrivalModal();
            location.reload();
        } else {
            alert('Ошибка: ' + (data.message || 'Не удалось отметить прибытие'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при отправке запроса');
    });
});

// Управление боковой панелью
document.getElementById('toggle-sidebar').addEventListener('click', () => {
    document.getElementById('sidebar').classList.add('active');
});

document.getElementById('close-sidebar').addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('active');
});

// Кнопка "Мое местоположение"
document.getElementById('my-location').addEventListener('click', () => {
    if (currentPosition && navigationMap) {
        navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            navigationMap.setView([position.coords.latitude, position.coords.longitude], 15);
        });
    }
});

// Кнопка "Центрировать маршрут"
document.getElementById('center-route').addEventListener('click', () => {
    if (routeLayer && navigationMap) {
        navigationMap.fitBounds(routeLayer.getBounds());
    }
});

// Кнопка "Сделать фото"
document.getElementById('take-photo').addEventListener('click', () => {
    // Здесь можно реализовать логику для съемки фото
    alert('Функция съемки фото будет реализована позже');
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    initNavigationMap();
    
    // Открываем боковую панель на мобильных устройствах
    if (window.innerWidth < 768) {
        setTimeout(() => {
            document.getElementById('sidebar').classList.add('active');
        }, 1000);
    }
    
    // Обновляем прогресс каждые 30 секунд
    setInterval(() => {
        updateSessionStats();
    }, 30000);
});

// Обновление статистики сессии
function updateSessionStats() {
    fetch(`/api/sessions/{{ $session->id }}/stats`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем UI с новыми данными
            // Можно реализовать обновление конкретных элементов
        }
    });
}

// Очистка при разгрузке страницы
window.addEventListener('beforeunload', () => {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
});
</script>
@endpush