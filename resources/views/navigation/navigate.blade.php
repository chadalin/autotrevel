@extends('layouts.app')

@section('title', $route->title . ' - Навигация')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    * {
        box-sizing: border-box;
    }
    
    .navigation-container {
        position: relative;
        height: 100vh;
        width: 100%;
        overflow: hidden;
        background-color: #f8fafc;
    }
    
    #navigation-map {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }
    
    /* Верхняя панель навигации */
    .navigation-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: linear-gradient(to bottom, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
        backdrop-filter: blur(10px);
        padding: 12px 16px;
        border-bottom: 1px solid rgba(229, 231, 235, 0.8);
        transition: transform 0.3s ease;
    }
    
    .navigation-header.hidden {
        transform: translateY(-100%);
    }
    
    /* Компактная панель прогресса */
    .compact-progress {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 8px;
    }
    
    .compact-progress-bar {
        flex: 1;
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .compact-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    /* Боковая панель - новая улучшенная версия */
    .navigation-sidebar {
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        max-width: 420px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 25px rgba(0,0,0,0.15);
        z-index: 1001;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    
    .navigation-sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 0 24px 24px;
    }
    
    /* Флоатинг кнопки */
    .floating-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1002;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .floating-btn {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #374151;
    }
    
    .floating-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(0,0,0,0.2);
    }
    
    .floating-btn.active {
        background: #3b82f6;
        color: white;
    }
    
    .floating-btn i {
        font-size: 1.25rem;
    }
    
    /* Панель быстрых действий (мобильная) */
    .mobile-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1001;
        background: white;
        padding: 16px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        display: none;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    
    .mobile-action-btn {
        padding: 12px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    
    .mobile-action-btn i {
        font-size: 1.25rem;
    }
    
    /* Карточки для квестов и заданий */
    .quest-mini-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        border: 2px solid #fbbf24;
        box-shadow: 0 2px 8px rgba(251, 191, 36, 0.1);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .quest-mini-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);
    }
    
    .task-mini-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 6px;
        font-size: 0.875rem;
    }
    
    .task-mini-item.completed {
        background: #dcfce7;
        color: #166534;
    }
    
    /* Информационная панель (появляется при скролле) */
    .info-panel {
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%) translateY(-20px);
        background: white;
        padding: 16px 20px;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        max-width: 90%;
        width: 400px;
    }
    
    .info-panel.visible {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
    
    /* Чекпоинты в виде индикаторов на карте */
    .checkpoint-badge {
        position: absolute;
        z-index: 100;
        pointer-events: none;
    }
    
    /* Адаптивные стили */
    @media (max-width: 768px) {
        .navigation-header {
            padding: 12px;
        }
        
        .navigation-sidebar {
            max-width: 100%;
        }
        
        .sidebar-header,
        .sidebar-content {
            padding: 16px;
        }
        
        .floating-controls {
            bottom: 80px;
            right: 16px;
        }
        
        .mobile-actions {
            display: grid;
        }
        
        .info-panel {
            width: calc(100% - 32px);
            top: 70px;
        }
        
        .compact-progress {
            gap: 8px;
        }
    }
    
    @media (max-width: 480px) {
        .navigation-header {
            padding: 10px;
        }
        
        .mobile-action-btn {
            padding: 10px 8px;
            font-size: 0.75rem;
        }
        
        .mobile-action-btn i {
            font-size: 1rem;
        }
        
        .floating-btn {
            width: 48px;
            height: 48px;
        }
        
        .floating-btn i {
            font-size: 1rem;
        }
    }
    
    /* Анимации */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    /* Улучшенные стили для карточки текущего чекпоинта */
    .current-checkpoint-card {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 2px solid #3b82f6;
        border-radius: 16px;
        padding: 20px;
        margin: 16px 0;
        position: relative;
        overflow: hidden;
    }
    
    .current-checkpoint-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    }
    
    /* Стили для мини-статистики */
    .mini-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 16px;
    }
    
    .stat-item {
        text-align: center;
        padding: 8px;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    .stat-value {
        font-weight: 700;
        font-size: 1.25rem;
        color: #1f2937;
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 2px;
    }
    
    /* Плавающие уведомления */
    .notification-toast {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1003;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        max-width: 300px;
        transform: translateX(120%);
        transition: transform 0.3s ease;
    }
    
    .notification-toast.show {
        transform: translateX(0);
    }
    
    @media (max-width: 768px) {
        .mini-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .notification-toast {
            top: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }
</style>
@endpush

@section('content')
<div class="navigation-container">
    <!-- Карта -->
    <div id="navigation-map"></div>
    
    <!-- Верхняя панель навигации -->
    <div class="navigation-header" id="navigationHeader">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="toggle-sidebar" class="floating-btn" style="width: 44px; height: 44px;">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="font-bold text-gray-800 text-lg truncate max-w-[200px] md:max-w-none">
                        {{ $route->title }}
                    </h1>
                    <div class="text-sm text-gray-600">
                        {{ $completedCheckpoints }}/{{ $totalCheckpoints }} точек • 
                        {{ $session->duration_seconds ? gmdate('H:i:s', $session->duration_seconds) : '00:00:00' }}
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Быстрые действия -->
                <div class="hidden md:flex items-center gap-2">
                    @if($session->status === 'active')
                        <form action="{{ route('routes.navigation.pause', $session) }}" method="POST">
                            @csrf
                            <button type="submit" class="floating-btn" style="width: 44px; height: 44px;" 
                                    title="Пауза">
                                <i class="fas fa-pause"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('routes.navigation.resume', $session) }}" method="POST">
                            @csrf
                            <button type="submit" class="floating-btn" style="width: 44px; height: 44px;" 
                                    title="Продолжить">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('routes.navigation.complete', $session) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Завершить навигацию?')"
                                class="floating-btn" style="width: 44px; height: 44px;" 
                                title="Завершить">
                            <i class="fas fa-flag-checkered"></i>
                        </button>
                    </form>
                </div>
                
                <a href="{{ route('routes.show', $route) }}" 
                   class="floating-btn" style="width: 44px; height: 44px;" title="Закрыть">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
        
        <!-- Компактная панель прогресса -->
        <div class="compact-progress">
            <div class="compact-progress-bar">
                <div class="compact-progress-fill" style="width: {{ $progressPercentage }}%"></div>
            </div>
            <span class="font-bold text-gray-800 text-sm whitespace-nowrap">
                {{ $progressPercentage }}%
            </span>
        </div>
        
        <!-- Мини-статистика -->
        @php
            $remainingCheckpoints = $totalCheckpoints - $completedCheckpoints;
            $estimatedTime = $remainingCheckpoints * 10; // Пример: 10 минут на точку
        @endphp
        
        <div class="mini-stats">
            <div class="stat-item">
                <div class="stat-value">{{ $completedCheckpoints }}</div>
                <div class="stat-label">Пройдено</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $remainingCheckpoints }}</div>
                <div class="stat-label">Осталось</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $earnedXp ?? 0 }}</div>
                <div class="stat-label">XP</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ gmdate('H:i', $estimatedTime * 60) }}</div>
                <div class="stat-label">Осталось</div>
            </div>
        </div>
    </div>
    
    <!-- Информационная панель с быстрыми данными -->
    <div class="info-panel" id="infoPanel">
        @if($currentCheckpoint)
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bullseye text-red-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-800 truncate">{{ $currentCheckpoint->title }}</h3>
                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $currentCheckpoint->description ?? 'Текущая контрольная точка' }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <button onclick="arriveAtCheckpoint({{ $currentCheckpoint->id }})" 
                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm rounded-lg font-medium flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> Прибыл
                        </button>
                        <button onclick="showCheckpointInfo({{ $currentCheckpoint->id }})" 
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg font-medium flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Подробнее
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Боковая панель -->
    <div class="navigation-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Навигация</h2>
                    <p class="text-gray-600">{{ $route->title }}</p>
                </div>
                <button id="close-sidebar" class="floating-btn" style="width: 40px; height: 40px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Переключение вкладок -->
            <div class="flex border-b border-gray-200 mb-4">
                <button class="tab-btn active px-4 py-2 font-medium text-gray-800 border-b-2 border-blue-500" 
                        data-tab="checkpoints">Маршрут</button>
                <button class="tab-btn px-4 py-2 font-medium text-gray-600" 
                        data-tab="quests">Квесты</button>
                <button class="tab-btn px-4 py-2 font-medium text-gray-600" 
                        data-tab="stats">Статистика</button>
            </div>
        </div>
        
        <div class="sidebar-content">
            <!-- Вкладка: Маршрут -->
            <div class="tab-content active" id="tab-checkpoints">
                <!-- Текущий чекпоинт -->
                @if($currentCheckpoint)
                    <div class="current-checkpoint-card">
                        <div class="flex items-start mb-4">
                            <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-bullseye text-red-600 text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-800 text-lg mb-1">{{ $currentCheckpoint->title }}</h3>
                                <div class="flex items-center flex-wrap gap-2 mb-2">
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                        #{{ $currentCheckpoint->order }}
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ number_format($currentCheckpoint->latitude, 5) }}, {{ number_format($currentCheckpoint->longitude, 5) }}
                                    </span>
                                </div>
                                @if($currentCheckpoint->description)
                                    <p class="text-gray-700">{{ $currentCheckpoint->description }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button onclick="arriveAtCheckpoint({{ $currentCheckpoint->id }})" 
                                    class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white py-3 rounded-lg font-bold flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Прибыл на точку
                            </button>
                            <button onclick="skipCheckpoint({{ $currentCheckpoint->id }})" 
                                    class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium flex items-center justify-center">
                                <i class="fas fa-forward"></i>
                            </button>
                        </div>
                    </div>
                @endif
                
                <!-- Список чекпоинтов -->
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    Маршрут ({{ $checkpoints->count() }} точек)
                </h3>
                
                <div class="space-y-3">
                    @foreach($checkpoints as $checkpoint)
                        @php
                            $isActive = $currentCheckpoint && $checkpoint->id === $currentCheckpoint->id;
                            $isCompleted = $checkpoint->isCompleted() ?? false;
                        @endphp
                        
                        <div class="checkpoint-card rounded-lg p-4 border border-gray-200 hover:border-blue-300 transition-colors {{ $isActive ? 'bg-blue-50 border-blue-300' : '' }} {{ $isCompleted ? 'bg-green-50 border-green-300' : '' }}">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-4
                                            {{ $isActive ? 'bg-blue-100 text-blue-600' : 
                                               ($isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600') }}">
                                    @if($isCompleted)
                                        <i class="fas fa-check"></i>
                                    @elseif($isActive)
                                        <i class="fas fa-bullseye pulse"></i>
                                    @else
                                        <span class="font-bold">{{ $checkpoint->order }}</span>
                                    @endif
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-gray-800 truncate">{{ $checkpoint->title }}</h4>
                                        @if(!$isActive && !$isCompleted)
                                            <span class="text-xs text-gray-500 ml-2 whitespace-nowrap">
                                                {{ $checkpoint->distance_to_next ?? '?' }} км
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($checkpoint->description)
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $checkpoint->description }}</p>
                                    @endif
                                    
                                    <div class="flex items-center mt-2 gap-2">
                                        <span class="text-xs px-2 py-1 rounded {{ $isActive ? 'bg-blue-100 text-blue-800' : ($isCompleted ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                            <i class="fas fa-map-marker-alt text-xs mr-1"></i>
                                            {{ $checkpoint->type_label ?? 'Точка' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Вкладка: Квесты -->
            <div class="tab-content" id="tab-quests">
                @php
                    $quests = $activeQuests ?? collect();
                    $hasQuests = $quests->count() > 0;
                @endphp
                
                @if($hasQuests)
                    @foreach($quests as $quest)
                        <div class="quest-mini-card">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-800 text-lg mb-1">{{ $quest->title }}</h4>
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ $quest->short_description ?? $quest->description }}</p>
                                </div>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium whitespace-nowrap ml-2">
                                    {{ $quest->difficulty_label ?? 'Средний' }}
                                </span>
                            </div>
                            
                            <!-- Прогресс квеста -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Прогресс</span>
                                    <span>{{ $quest->progress_percentage ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" 
                                         style="width: {{ $quest->progress_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Быстрый просмотр заданий -->
                            @if(isset($quest->tasks) && $quest->tasks->count() > 0)
                                <div class="space-y-2">
                                    @foreach($quest->tasks->take(3) as $task)
                                        @php
                                            $isCompleted = $task->is_completed ?? false;
                                        @endphp
                                        <div class="task-mini-item {{ $isCompleted ? 'completed' : '' }}">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $isCompleted ? 'bg-green-200 text-green-700' : 'bg-blue-200 text-blue-700' }}">
                                                    <i class="fas {{ $isCompleted ? 'fa-check' : 'fa-tasks' }} text-xs"></i>
                                                </div>
                                                <span class="truncate">{{ $task->title }}</span>
                                                @if(!$isCompleted && ($task->location_id === null || ($currentCheckpoint && $task->location_id == $currentCheckpoint->id)))
                                                    <button onclick="completeTask({{ $task->id }})" 
                                                            class="ml-auto text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                                                        Выполнить
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($quest->tasks->count() > 3)
                                        <div class="text-center">
                                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                +{{ $quest->tasks->count() - 3 }} заданий
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Награда -->
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                                <div class="text-sm">
                                    <span class="text-gray-600">Награда:</span>
                                    @if(isset($quest->reward_exp))
                                        <span class="font-bold text-green-600 ml-2">+{{ $quest->reward_exp }} XP</span>
                                    @endif
                                    @if(isset($quest->reward_coins) && $quest->reward_coins > 0)
                                        <span class="font-bold text-yellow-600 ml-2">+{{ $quest->reward_coins }} монет</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-quest text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Нет активных квестов</h3>
                        <p class="text-gray-500 mb-6">Начните квест, чтобы выполнять задания во время маршрута</p>
                        <a href="{{ route('quests.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-medium hover:from-blue-600 hover:to-indigo-700">
                            <i class="fas fa-search mr-2"></i> Найти квесты
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Вкладка: Статистика -->
            <div class="tab-content" id="tab-stats">
                <div class="space-y-6">
                    <!-- Основная статистика -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="font-bold text-gray-800 mb-4 text-lg">Статистика сессии</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-gray-800 mb-1">{{ $session->distance_traveled ?? 0 }}</div>
                                <div class="text-sm text-gray-600">км пройдено</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-gray-800 mb-1">{{ $session->duration_formatted ?? '00:00' }}</div>
                                <div class="text-sm text-gray-600">время в пути</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-gray-800 mb-1">{{ $session->average_speed ?? 0 }}</div>
                                <div class="text-sm text-gray-600">средняя скорость</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg">
                                <div class="text-3xl font-bold text-gray-800 mb-1">{{ $earnedXp ?? 0 }}</div>
                                <div class="text-sm text-gray-600">XP заработано</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Достижения -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4">Достижения</h4>
                        <div class="space-y-3">
                            <div class="flex items-center p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                                <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
                                    <i class="fas fa-trophy text-yellow-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">Первопроходец</div>
                                    <div class="text-sm text-gray-600">Пройдите 3 контрольные точки</div>
                                </div>
                                @if($completedCheckpoints >= 3)
                                    <div class="ml-auto text-green-500">
                                        <i class="fas fa-check-circle text-2xl"></i>
                                    </div>
                                @else
                                    <div class="ml-auto text-sm text-gray-500">
                                        {{ $completedCheckpoints }}/3
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                    <i class="fas fa-route text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">Исследователь</div>
                                    <div class="text-sm text-gray-600">Пройти весь маршрут</div>
                                </div>
                                <div class="ml-auto text-sm text-gray-500">
                                    {{ $completedCheckpoints }}/{{ $totalCheckpoints }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Плавающие кнопки управления -->
    <div class="floating-controls">
        <button id="my-location" class="floating-btn" title="Мое местоположение">
            <i class="fas fa-location-arrow"></i>
        </button>
        <button id="center-route" class="floating-btn" title="Центрировать маршрут">
            <i class="fas fa-route"></i>
        </button>
        <button id="toggle-header" class="floating-btn" title="Скрыть панель">
            <i class="fas fa-eye-slash"></i>
        </button>
        <button id="take-photo" class="floating-btn" title="Сделать фото">
            <i class="fas fa-camera"></i>
        </button>
    </div>
    
    <!-- Мобильная панель действий -->
    <div class="mobile-actions">
        @if($session->status === 'active')
            <form action="{{ route('routes.navigation.pause', $session) }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="mobile-action-btn bg-yellow-500 text-white hover:bg-yellow-600">
                    <i class="fas fa-pause"></i>
                    <span>Пауза</span>
                </button>
            </form>
        @else
            <form action="{{ route('routes.navigation.resume', $session) }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="mobile-action-btn bg-green-500 text-white hover:bg-green-600">
                    <i class="fas fa-play"></i>
                    <span>Продолжить</span>
                </button>
            </form>
        @endif
        
        <button onclick="arriveAtCheckpoint({{ $currentCheckpoint->id ?? 0 }})" 
                class="mobile-action-btn bg-blue-500 text-white hover:bg-blue-600">
            <i class="fas fa-check-circle"></i>
            <span>Прибыл</span>
        </button>
        
        <form action="{{ route('routes.navigation.complete', $session) }}" method="POST" class="w-full">
            @csrf
            <button type="submit" onclick="return confirm('Завершить навигацию?')"
                    class="mobile-action-btn bg-red-500 text-white hover:bg-red-600">
                <i class="fas fa-flag-checkered"></i>
                <span>Завершить</span>
            </button>
        </form>
    </div>
    
    <!-- Плавающее уведомление -->
    <div class="notification-toast" id="notificationToast">
        <div class="flex items-start">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-gray-800 mb-1" id="toastTitle">Уведомление</h4>
                <p class="text-sm text-gray-600" id="toastMessage">Сообщение</p>
            </div>
            <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600 ml-2">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- Модальные окна -->
@include('navigation.modals')
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
let isHeaderVisible = true;
let lastScrollPosition = 0;

// Инициализация карты
function initNavigationMap() {
    if (!document.getElementById('navigation-map')) return;
    
    try {
        // Создаем карту
        const startLat = {{ $route->start_lat ?? 55.7558 }};
        const startLng = {{ $route->start_lng ?? 37.6173 }};
        navigationMap = L.map('navigation-map').setView([startLat, startLng], 13);
        
        // Добавляем тайлы
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
            minZoom: 3
        }).addTo(navigationMap);
        
        // Добавляем маршрут
        @if($route->coordinates && is_array($route->coordinates))
            const coordinates = @json($route->coordinates);
            if (coordinates && coordinates.length > 0) {
                routeLayer = L.polyline(coordinates, {
                    color: '#3b82f6',
                    weight: 5,
                    opacity: 0.8,
                    smoothFactor: 1,
                    dashArray: '10, 10'
                }).addTo(navigationMap);
                
                // Фокусируем на маршруте
                navigationMap.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
            }
        @endif
        
        // Добавляем чекпоинты
        @foreach($checkpoints as $checkpoint)
            @if($checkpoint->latitude && $checkpoint->longitude)
                const checkpoint{{ $checkpoint->id }} = createCheckpointMarker(
                    {{ $checkpoint->id }},
                    {{ $checkpoint->latitude }},
                    {{ $checkpoint->longitude }},
                    {{ $checkpoint->order }},
                    {{ $currentCheckpoint && $checkpoint->id === $currentCheckpoint->id ? 'true' : 'false' }},
                    {{ $checkpoint->isCompleted() ? 'true' : 'false' }},
                    '{{ $checkpoint->title }}',
                    '{{ $checkpoint->type_label ?? 'Точка' }}'
                );
                checkpointMarkers.push(checkpoint{{ $checkpoint->id }});
            @endif
        @endforeach
        
        // Инициализируем геолокацию
        initGeolocation();
        
        // Обновляем размер карты после загрузки
        setTimeout(() => {
            if (navigationMap) {
                navigationMap.invalidateSize();
            }
        }, 100);
        
    } catch (error) {
        console.error('Ошибка инициализации карты:', error);
        showNotification('Ошибка', 'Не удалось загрузить карту', 'error');
    }
}

// Создание маркера чекпоинта
function createCheckpointMarker(id, lat, lng, order, isActive, isCompleted, title, type) {
    let color, iconHtml;
    
    if (isActive) {
        color = '#ef4444'; // Красный для активной
        iconHtml = '<i class="fas fa-bullseye"></i>';
    } else if (isCompleted) {
        color = '#10b981'; // Зеленый для пройденной
        iconHtml = '<i class="fas fa-check"></i>';
    } else {
        color = '#6b7280'; // Серый для неактивной
        iconHtml = `<span style="font-size: 12px;">${order}</span>`;
    }
    
    const icon = L.divIcon({
        html: `
            <div style="
                width: 40px;
                height: 40px;
                background-color: ${color};
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                cursor: pointer;
            ">
                ${iconHtml}
            </div>
        `,
        className: 'checkpoint-marker',
        iconSize: [40, 40],
        iconAnchor: [20, 40]
    });
    
    const marker = L.marker([lat, lng], { icon }).addTo(navigationMap);
    
    marker.bindPopup(`
        <div class="p-3 min-w-[200px]">
            <div class="font-bold text-gray-800 mb-2">${title}</div>
            <div class="text-sm text-gray-600 mb-2">${type} • Точка #${order}</div>
            ${isActive ? '<div class="text-sm text-red-600 font-medium mb-2"><i class="fas fa-bullseye mr-1"></i>Текущая точка</div>' : ''}
            ${isCompleted ? '<div class="text-sm text-green-600 font-medium mb-2"><i class="fas fa-check mr-1"></i>Пройдена</div>' : ''}
            <button onclick="arriveAtCheckpoint(${id})" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-medium mt-2">
                <i class="fas fa-check-circle mr-2"></i>Отметить прибытие
            </button>
        </div>
    `);
    
    return marker;
}

// Инициализация геолокации
function initGeolocation() {
    if (!navigator.geolocation) {
        showNotification('Внимание', 'Геолокация не поддерживается вашим браузером', 'warning');
        return;
    }
    
    // Запрашиваем геолокацию
    navigator.geolocation.getCurrentPosition(
        showCurrentPosition,
        handleLocationError,
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
    
    // Начинаем отслеживание
    watchId = navigator.geolocation.watchPosition(
        updateCurrentPosition,
        handleLocationError,
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 1000 }
    );
}

// Показать текущую позицию
function showCurrentPosition(position) {
    currentPosition = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
        accuracy: position.coords.accuracy,
        heading: position.coords.heading,
        speed: position.coords.speed
    };
    
    currentPositionMarker = L.marker([currentPosition.lat, currentPosition.lng], {
        icon: L.divIcon({
            html: `
                <div style="
                    width: 24px;
                    height: 24px;
                    background-color: #3b82f6;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                "></div>
            `,
            className: 'current-position-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        })
    }).addTo(navigationMap);
    
    // Центрируем карту на текущей позиции
    navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
    
    // Показываем информационную панель
    setTimeout(() => {
        showInfoPanel();
    }, 1000);
}

// Обновить текущую позицию
function updateCurrentPosition(position) {
    const newPos = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
        accuracy: position.coords.accuracy,
        heading: position.coords.heading,
        speed: position.coords.speed
    };
    
    if (currentPositionMarker) {
        currentPositionMarker.setLatLng([newPos.lat, newPos.lng]);
    } else {
        showCurrentPosition(position);
    }
    
    currentPosition = newPos;
    
    // Обновляем расстояние до текущей точки
    updateDistanceToCheckpoint();
}

// Обновить расстояние до текущей контрольной точки
function updateDistanceToCheckpoint() {
    @if($currentCheckpoint)
        if (currentPosition && currentPosition.lat && currentPosition.lng) {
            const distance = calculateDistance(
                currentPosition.lat,
                currentPosition.lng,
                {{ $currentCheckpoint->latitude }},
                {{ $currentCheckpoint->longitude }}
            );
            
            // Если близко к точке, показываем уведомление
            if (distance < 0.1) { // 100 метров
                showNotification('Приближение', 'Вы близко к контрольной точке!', 'info');
            }
            
            if (distance < 0.05) { // 50 метров
                showNotification('Прибытие', 'Вы на месте! Отметьте прибытие.', 'success');
            }
        }
    @endif
}

// Вычислить расстояние между двумя точками
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Радиус Земли в км
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Обработка ошибок геолокации
function handleLocationError(error) {
    console.warn('Ошибка геолокации:', error.message);
    
    let message = 'Не удалось определить ваше местоположение. ';
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message += 'Разрешите доступ к геолокации в настройках браузера.';
            break;
        case error.POSITION_UNAVAILABLE:
            message += 'Информация о местоположении недоступна.';
            break;
        case error.TIMEOUT:
            message += 'Превышено время ожидания определения местоположения.';
            break;
    }
    
    showNotification('Геолокация', message, 'warning');
}

// Показать информационную панель
function showInfoPanel() {
    const infoPanel = document.getElementById('infoPanel');
    if (infoPanel) {
        infoPanel.classList.add('visible');
        
        // Автоматически скрываем через 10 секунд
        setTimeout(() => {
            hideInfoPanel();
        }, 10000);
    }
}

// Скрыть информационную панель
function hideInfoPanel() {
    const infoPanel = document.getElementById('infoPanel');
    if (infoPanel) {
        infoPanel.classList.remove('visible');
    }
}

// Показать уведомление
function showNotification(title, message, type = 'info') {
    const toast = document.getElementById('notificationToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    
    if (toast && toastTitle && toastMessage) {
        // Устанавливаем цвет в зависимости от типа
        const icon = toast.querySelector('i');
        const iconContainer = toast.querySelector('.bg-blue-100');
        
        if (type === 'error') {
            iconContainer.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3 flex-shrink-0';
            icon.className = 'fas fa-exclamation-circle text-red-600';
        } else if (type === 'warning') {
            iconContainer.className = 'w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3 flex-shrink-0';
            icon.className = 'fas fa-exclamation-triangle text-yellow-600';
        } else if (type === 'success') {
            iconContainer.className = 'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3 flex-shrink-0';
            icon.className = 'fas fa-check-circle text-green-600';
        }
        
        toastTitle.textContent = title;
        toastMessage.textContent = message;
        toast.classList.add('show');
        
        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            hideToast();
        }, 5000);
    }
}

// Скрыть уведомление
function hideToast() {
    const toast = document.getElementById('notificationToast');
    if (toast) {
        toast.classList.remove('show');
    }
}

// Прибытие на чекпоинт
function arriveAtCheckpoint(checkpointId) {
    // Показываем модальное окно для подтверждения
    showArrivalModal(checkpointId);
}

// Показать модальное окно прибытия
function showArrivalModal(checkpointId) {
    // Здесь можно реализовать модальное окно
    // Для простоты сразу отправляем запрос
    if (confirm('Подтвердите прибытие на контрольную точку')) {
        fetch(`/api/checkpoints/${checkpointId}/arrive`, {
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
                showNotification('Успех', 'Точка успешно отмечена!', 'success');
                location.reload();
            } else {
                showNotification('Ошибка', data.message || 'Не удалось отметить прибытие', 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка', 'Ошибка при отправке запроса', 'error');
        });
    }
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
                showNotification('Успех', 'Точка пропущена', 'info');
                location.reload();
            } else {
                showNotification('Ошибка', data.message || 'Не удалось пропустить точку', 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка', 'Ошибка при отправке запроса', 'error');
        });
    }
}

// Выполнить задание
// Выполнить задание
function completeTask(taskId) {
    // Сначала получаем информацию о задании
    fetch(`/api/tasks/${taskId}/info`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показываем модальное окно с заданием
            showTaskModal(data.data.task);
        } else {
            showNotification('Ошибка', 'Не удалось загрузить задание', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка', 'Ошибка при загрузке задания', 'error');
    });
}

// Показать модальное окно с заданием
function showTaskModal(task) {
    let modalContent = '';
    
    switch(task.type) {
        case 'text':
            modalContent = `
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-font text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">${task.title}</h3>
                    <p class="text-gray-600 mb-6">${task.description}</p>
                    
                    <div class="mb-6">
                        <label for="task-answer" class="block text-sm font-medium text-gray-700 mb-2">
                            Ваш ответ
                        </label>
                        <textarea id="task-answer" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Введите ваш ответ..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button onclick="closeTaskModal()"
                                class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                            Отмена
                        </button>
                        <button onclick="submitTaskAnswer(${task.id})"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold hover:from-blue-600 hover:to-indigo-700">
                            <i class="fas fa-check mr-2"></i> Отправить ответ
                        </button>
                    </div>
                </div>
            `;
            break;
            
        case 'image':
            modalContent = `
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-camera text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">${task.title}</h3>
                    <p class="text-gray-600 mb-6">${task.description}</p>
                    
                    <div class="mb-6">
                        <label for="task-photo" class="block text-sm font-medium text-gray-700 mb-2">
                            Загрузите фотографию
                        </label>
                        <input type="file" id="task-photo" accept="image/*" capture="environment"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0 file:text-sm file:font-medium
                                      file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button onclick="closeTaskModal()"
                                class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                            Отмена
                        </button>
                        <button onclick="submitTaskPhoto(${task.id})"
                                class="px-5 py-2.5 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-lg font-bold hover:from-purple-600 hover:to-pink-700">
                            <i class="fas fa-upload mr-2"></i> Загрузить фото
                        </button>
                    </div>
                </div>
            `;
            break;
            
        case 'quiz':
            modalContent = `
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-question-circle text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">${task.title}</h3>
                    <p class="text-gray-600 mb-6">${task.description}</p>
                    
                    <div class="space-y-3 mb-6" id="quiz-options">
                        <!-- Опции будут загружены динамически -->
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button onclick="closeTaskModal()"
                                class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                            Отмена
                        </button>
                        <button onclick="submitQuizAnswer(${task.id})"
                                class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold hover:from-green-600 hover:to-emerald-700">
                            <i class="fas fa-check mr-2"></i> Ответить
                        </button>
                    </div>
                </div>
            `;
            break;
            
        default:
            modalContent = `
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-tasks text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">${task.title}</h3>
                    <p class="text-gray-600 mb-6">${task.description}</p>
                    
                    <div class="mb-6">
                        <label for="generic-answer" class="block text-sm font-medium text-gray-700 mb-2">
                            Ваш ответ
                        </label>
                        <input type="text" id="generic-answer"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="Введите ответ...">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button onclick="closeTaskModal()"
                                class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                            Отмена
                        </button>
                        <button onclick="submitGenericAnswer(${task.id})"
                                class="px-5 py-2.5 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-lg font-bold hover:from-yellow-600 hover:to-orange-700">
                            <i class="fas fa-check mr-2"></i> Ответить
                        </button>
                    </div>
                </div>
            `;
    }
    
    document.getElementById('task-content').innerHTML = modalContent;
    document.getElementById('task-modal').classList.remove('hidden');
}

// Отправить текстовый ответ
function submitTaskAnswer(taskId) {
    const answer = document.getElementById('task-answer').value.trim();
    
    if (!answer) {
        showNotification('Внимание', 'Введите ответ', 'warning');
        return;
    }
    
    submitTaskCompletion(taskId, { answer: answer });
}

// Отправить фото
function submitTaskPhoto(taskId) {
    const fileInput = document.getElementById('task-photo');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showNotification('Внимание', 'Выберите фото', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('photo', fileInput.files[0]);
    formData.append('answer', 'photo_submission');
    
    submitTaskCompletion(taskId, formData, true);
}

// Отправить ответ на викторину
function submitQuizAnswer(taskId) {
    const selectedOption = document.querySelector('input[name="quiz-option"]:checked');
    
    if (!selectedOption) {
        showNotification('Внимание', 'Выберите вариант ответа', 'warning');
        return;
    }
    
    submitTaskCompletion(taskId, { answer: selectedOption.value });
}

// Отправить общий ответ
function submitGenericAnswer(taskId) {
    const answer = document.getElementById('generic-answer').value.trim();
    
    if (!answer) {
        showNotification('Внимание', 'Введите ответ', 'warning');
        return;
    }
    
    submitTaskCompletion(taskId, { answer: answer });
}

// Отправить выполнение задания
function submitTaskCompletion(taskId, data, isFormData = false) {
    const url = `/api/tasks/${taskId}/complete`;
    const options = {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    };
    
    if (isFormData) {
        options.body = data;
    } else {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
    }
    
    fetch(url, options)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeTaskModal();
                showTaskResult(result.data.task);
                showNotification('Успех', result.message, 'success');
                
                // Обновляем прогресс через 2 секунды
                setTimeout(() => {
                    updateQuestProgress();
                }, 2000);
            } else {
                showNotification('Ошибка', result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка', 'Ошибка при отправке ответа', 'error');
        });
}

// Обновить прогресс квестов
function updateQuestProgress() {
    // Можно реализовать обновление через AJAX
    // Для простоты перезагружаем страницу
    location.reload();
}

// Управление боковой панелью
document.getElementById('toggle-sidebar').addEventListener('click', () => {
    document.getElementById('sidebar').classList.add('active');
});

document.getElementById('close-sidebar').addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('active');
});

// Переключение вкладок
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.dataset.tab;
        
        // Убираем активный класс у всех кнопок и вкладок
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Добавляем активный класс текущей кнопке и вкладке
        btn.classList.add('active');
        document.getElementById(`tab-${tabId}`).classList.add('active');
    });
});

// Кнопка "Мое местоположение"
document.getElementById('my-location').addEventListener('click', () => {
    if (currentPosition && navigationMap) {
        navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
        showNotification('Геолокация', 'Карта центрирована на вашем местоположении', 'info');
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            navigationMap.setView([position.coords.latitude, position.coords.longitude], 15);
            showNotification('Геолокация', 'Карта центрирована на вашем местоположении', 'info');
        });
    }
});

// Кнопка "Центрировать маршрут"
document.getElementById('center-route').addEventListener('click', () => {
    if (routeLayer && navigationMap) {
        navigationMap.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
        showNotification('Карта', 'Карта центрирована на маршруте', 'info');
    }
});

// Кнопка "Скрыть/показать панель"
document.getElementById('toggle-header').addEventListener('click', () => {
    const header = document.getElementById('navigationHeader');
    const btn = document.getElementById('toggle-header');
    
    if (isHeaderVisible) {
        header.classList.add('hidden');
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        btn.title = 'Показать панель';
        showNotification('Панель', 'Верхняя панель скрыта', 'info');
    } else {
        header.classList.remove('hidden');
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        btn.title = 'Скрыть панель';
        showNotification('Панель', 'Верхняя панель показана', 'info');
    }
    
    isHeaderVisible = !isHeaderVisible;
    
    // Обновляем размер карты
    setTimeout(() => {
        if (navigationMap) {
            navigationMap.invalidateSize();
        }
    }, 300);
});

// Кнопка "Сделать фото"
document.getElementById('take-photo').addEventListener('click', () => {
    showNotification('Фото', 'Функция съемки фото будет доступна в следующем обновлении', 'info');
});

// Автоскрытие верхней панели при скролле
let scrollTimeout;
window.addEventListener('scroll', () => {
    const header = document.getElementById('navigationHeader');
    if (!header) return;
    
    const currentScroll = window.pageYOffset;
    
    clearTimeout(scrollTimeout);
    
    if (currentScroll > lastScrollPosition && currentScroll > 100) {
        // Скроллим вниз
        header.classList.add('hidden');
    } else {
        // Скроллим вверх
        header.classList.remove('hidden');
    }
    
    lastScrollPosition = currentScroll;
    
    // Автоматически показываем панель через 3 секунды без скролла
    scrollTimeout = setTimeout(() => {
        header.classList.remove('hidden');
    }, 3000);
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    initNavigationMap();
    
    // На мобильных устройствах открываем боковую панель
    if (window.innerWidth < 768) {
        setTimeout(() => {
            document.getElementById('sidebar').classList.add('active');
            showNotification('Навигация', 'Используйте нижнюю панель для быстрых действий', 'info');
        }, 1500);
    }
    
    // Обновляем статистику каждые 30 секунд
    setInterval(() => {
        updateSessionStats();
    }, 30000);
    
    // Показываем информационную панель через 2 секунды
    setTimeout(showInfoPanel, 2000);
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
            // Можно обновить данные на странице без перезагрузки
            console.log('Статистика обновлена:', data.stats);
        }
    });
}

// Очистка при разгрузке страницы
window.addEventListener('beforeunload', () => {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
    
    // Сохраняем состояние сессии
    if (navigationMap) {
        const center = navigationMap.getCenter();
        const zoom = navigationMap.getZoom();
        
        // Можно сохранить в localStorage для восстановления позиции
        localStorage.setItem('lastMapPosition', JSON.stringify({
            lat: center.lat,
            lng: center.lng,
            zoom: zoom
        }));
    }
});
</script>
@endpush