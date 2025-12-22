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
    
    /* Боковая панель */
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
    
    /* Информационная панель */
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
    
    /* Стили для модальных окон заданий */
    .task-modal-content {
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .task-type-text textarea {
        font-family: inherit;
    }
    
    .task-type-code textarea {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        tab-size: 4;
    }
    
    .task-type-cipher .cipher-text {
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
    }
    
    .puzzle-piece {
        user-select: none;
        touch-action: none;
        cursor: move;
    }
    
    .puzzle-piece.dragging {
        opacity: 0.5;
        transform: scale(1.05);
    }
    
    #photo-preview-image {
        max-height: 300px;
        object-fit: contain;
    }
    
    .task-type-location #location-info {
        font-family: monospace;
    }
    
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
        
        .mini-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .notification-toast {
            top: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
        
        .task-modal-content {
            max-height: 90vh;
            margin: 0;
            border-radius: 0;
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
                                                    <button onclick="window.completeTask({{ $task->id }})" 
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

<!-- Шаблоны для заданий (скрыты в DOM) -->
<div id="task-templates" style="display: none;">
    <!-- Текстовое задание -->
    <template id="task-template-text">
        <div class="task-modal task-type-text">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-font text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Вопрос:</div>
                    <div class="text-gray-800" data-bind="content.question"></div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ваш ответ:</label>
                    <textarea id="task-text-answer" rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Введите ваш ответ..." 
                        data-maxlength="500"></textarea>
                    <div class="text-xs text-gray-500 mt-1 text-right">
                        <span id="text-char-count">0</span>/<span data-bind="content.max_length">500</span> символов
                    </div>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitTextTask()"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-bold">
                        <i class="fas fa-paper-plane mr-2"></i>Отправить ответ
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Задание с фото -->
 <!-- Задание с фото - Исправленный шаблон -->
<template id="task-template-image">
    <div class="task-modal task-type-image">
        <div class="task-header">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                    <i class="fas fa-camera text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                    <p class="text-sm text-gray-600" data-bind="description"></p>
                </div>
            </div>
        </div>
        
        <div class="task-content mb-6">
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="text-sm font-medium text-gray-700 mb-2">Задание:</div>
                <div class="text-gray-800" data-bind="content.description"></div>
            </div>
            
            <!-- Область загрузки фото -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Выберите или сделайте фото:</label>
                
                <!-- Вариант 1: Сделать фото -->
                <div class="mb-3">
                    <button id="take-photo-btn" 
                            class="w-full px-4 py-3 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white rounded-lg font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-camera"></i>
                        Сделать фото
                    </button>
                </div>
                
                <!-- Вариант 2: Выбрать из галереи -->
                <div class="mb-3">
                    <input type="file" 
                           id="photo-file-input" 
                           accept="image/*" 
                           capture="environment" 
                           class="hidden">
                    <button id="choose-photo-btn" 
                            class="w-full px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-medium flex items-center justify-center gap-2 border-2 border-dashed border-gray-300">
                        <i class="fas fa-images"></i>
                        Выбрать из галереи
                    </button>
                </div>
                
                <!-- Предпросмотр фото -->
                <div id="photo-preview-container" class="mt-4 hidden">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-800">Предпросмотр:</h4>
                            <button type="button" 
                                    onclick="removePhotoPreview()" 
                                    class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i> Удалить
                            </button>
                        </div>
                        <div class="relative">
                            <img id="photo-preview-image" 
                                 class="w-full h-64 object-contain rounded-lg bg-gray-100">
                            <div id="photo-loading" 
                                 class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
                                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-white"></div>
                            </div>
                        </div>
                        <div id="photo-info" class="mt-2 text-sm text-gray-600">
                            <div id="photo-filename"></div>
                            <div id="photo-filesize"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Описание фото -->
                <div class="mt-4">
                    <label for="photo-description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание фото (необязательно):
                    </label>
                    <textarea id="photo-description" 
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Опишите, что на фото..."></textarea>
                </div>
                
                <!-- Кнопка загрузки -->
                <div id="upload-button-container" class="mt-4 hidden">
                    <button onclick="uploadTaskPhoto()" 
                            id="upload-photo-btn"
                            class="w-full px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-lg font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-upload"></i>
                        Загрузить фото
                    </button>
                </div>
            </div>
            
            <!-- Подсказки -->
            <div class="mb-4 hints-section" style="display: none;">
                <button onclick="requestHint()" 
                        class="text-sm text-purple-600 hover:text-purple-800 font-medium flex items-center">
                    <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                    <span class="ml-2 text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">
                        <span data-bind="hints_available">0</span> доступно
                    </span>
                </button>
            </div>
        </div>
        
        <div class="task-footer">
            <div class="flex justify-end gap-2">
                <button onclick="closeTaskModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                    Отмена
                </button>
            </div>
        </div>
    </div>
</template>
    
    <!-- Викторина -->
    <template id="task-template-quiz">
        <div class="task-modal task-type-quiz">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-question-circle text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-lg font-medium text-gray-800 mb-3" data-bind="content.question"></div>
                    
                    <div id="quiz-options" class="space-y-2">
                        <!-- Options will be inserted here -->
                    </div>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitQuizTask()"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold">
                        <i class="fas fa-check mr-2"></i>Ответить
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Задание с кодом -->
    <template id="task-template-code">
        <div class="task-modal task-type-code">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                        <i class="fas fa-code text-orange-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Задание:</div>
                    <div class="text-gray-800 mb-3" data-bind="content.description"></div>
                    
                    <div class="bg-gray-900 text-gray-100 rounded-lg p-4 font-mono text-sm overflow-x-auto">
                        <pre data-bind="content.code"></pre>
                    </div>
                    
                    <div class="mt-3 expected-output" style="display: none;">
                        <div class="text-sm font-medium text-gray-700 mb-1">Ожидаемый результат:</div>
                        <div class="bg-gray-800 text-green-400 rounded-lg p-3 font-mono text-sm" data-bind="content.expected_output">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ваш код:</label>
                    <div class="flex items-center mb-2">
                        <span class="text-sm text-gray-600 mr-3">Язык:</span>
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm" data-bind="content.language">
                            javascript
                        </span>
                    </div>
                    <textarea id="code-answer" rows="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Напишите ваш код здесь..."></textarea>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-orange-600 hover:text-orange-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitCodeTask()"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-bold">
                        <i class="fas fa-play mr-2"></i>Запустить код
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Шифр -->
    <template id="task-template-cipher">
        <div class="task-modal task-type-cipher">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                        <i class="fas fa-key text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Зашифрованное сообщение:</div>
                    <div class="bg-gray-800 text-yellow-300 rounded-lg p-4 font-mono text-center text-lg mb-3" data-bind="content.cipher_text">
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-2">Тип шифра: 
                        <span class="font-medium cipher-type-label">Шифр Цезаря</span>
                    </div>
                    
                    <div class="mt-3 hint-container" style="display: none;">
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="text-sm font-medium text-blue-700 mb-1">Подсказка:</div>
                            <div class="text-sm text-blue-600" data-bind="content.hint"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Расшифрованный текст:</label>
                    <textarea id="cipher-answer" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="Введите расшифрованный текст..."></textarea>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-yellow-600 hover:text-yellow-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitCipherTask()"
                            class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-bold">
                        <i class="fas fa-unlock mr-2"></i>Расшифровать
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Головоломка -->
    <template id="task-template-puzzle">
        <div class="task-modal task-type-puzzle">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center mr-3">
                        <i class="fas fa-puzzle-piece text-pink-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-gray-800 mb-3" data-bind="content.puzzle"></div>
                    
                    <div id="puzzle-container" class="mt-4">
                        <div class="grid grid-cols-3 gap-2" id="puzzle-grid">
                            <!-- Puzzle pieces will be inserted here -->
                        </div>
                    </div>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-pink-600 hover:text-pink-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-pink-100 text-pink-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitPuzzleTask()"
                            class="px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg font-bold">
                        <i class="fas fa-check-double mr-2"></i>Проверить решение
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Локация -->
    <template id="task-template-location">
        <div class="task-modal task-type-location">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                        <i class="fas fa-map-marker-alt text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-gray-800 mb-3" data-bind="content.description"></div>
                    
                    <div class="flex items-center text-sm text-gray-600 mb-2">
                        <i class="fas fa-location-arrow mr-2"></i>
                        <span>Радиус поиска: <span data-bind="content.radius">100</span> метров</span>
                    </div>
                    
                    <div class="mt-3 coordinates-container" style="display: none;">
                        <button onclick="showLocationOnMap()"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                            <i class="fas fa-map mr-2"></i> Показать на карте
                        </button>
                    </div>
                    
                    <div class="mt-4 text-center qr-container" style="display: none;">
                        <div class="text-sm font-medium text-gray-700 mb-2">Или отсканируйте QR-код:</div>
                        <div class="bg-white p-4 rounded-lg inline-block">
                            <img src="" alt="QR Code" class="w-32 h-32 qr-image">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Ваше текущее местоположение:</div>
                    <div id="location-info" class="text-sm text-gray-600">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-sync-alt animate-spin mr-2"></i>
                            Определяем местоположение...
                        </div>
                    </div>
                    <button onclick="getCurrentLocation()"
                            class="mt-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium flex items-center">
                        <i class="fas fa-location-crosshairs mr-2"></i> Обновить местоположение
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitLocationTask()"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold"
                            disabled id="submit-location-btn">
                        <i class="fas fa-check-circle mr-2"></i>Я на месте!
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Шаблон ошибки -->
    <template id="task-template-error">
        <div class="task-modal task-type-error">
            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ошибка загрузки задания</h3>
                <p class="text-gray-600 mb-6" id="error-message">Не удалось загрузить задание. Проверьте соединение с интернетом.</p>
                <div class="space-y-3">
                    <button onclick="retryLoadTask()"
                            class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium">
                        <i class="fas fa-redo mr-2"></i> Повторить попытку
                    </button>
                    <button onclick="closeTaskModal()"
                            class="w-full px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Шаблон результата -->
    <template id="task-template-result">
        <div class="task-modal task-type-result">
            <div class="text-center py-8">
                <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Задание выполнено!</h3>
                <p class="text-gray-600 mb-2" id="result-title"></p>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-8 max-w-sm mx-auto">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Награда:</span>
                            <span class="font-bold text-green-600" id="result-points"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Получено XP:</span>
                            <span class="font-bold text-blue-600" id="result-xp"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Статус:</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check mr-1"></i> Выполнено
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="text-sm text-gray-500 mb-8" id="result-message"></div>
                
                <button onclick="closeTaskModalAndRefresh()"
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold hover:from-blue-600 hover:to-indigo-700">
                    Продолжить маршрут
                </button>
            </div>
        </div>
    </template>
</div>

<!-- Модальное окно для заданий -->
<div id="task-modal" class="hidden fixed inset-0 z-[1300] bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div id="task-content" class="p-6 overflow-y-auto">
            <!-- Контент заданий будет загружен здесь -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ============================================
// ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ
// ============================================

let navigationMap = null;
let routeLayer = null;
let checkpointMarkers = [];
let currentPositionMarker = null;
let currentPosition = null;
let watchId = null;
let isHeaderVisible = true;
let lastScrollPosition = 0;

// Переменные для заданий
let currentTask = null;
let currentTaskId = null;
let currentTaskLocation = null;
let locationWatchId = null;
let lastTaskId = null;

// ============================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
}

async function apiFetch(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    };
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    console.log('API Fetch:', url, mergedOptions);
    
    try {
        const response = await fetch(url, mergedOptions);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error Response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API Response:', data);
        return data;
    } catch (error) {
        console.error('API Fetch Error:', error);
        throw error;
    }
}

function showNotification(title, message, type = 'info') {
    console.log('Show notification:', {title, message, type});
    
    const toast = document.getElementById('notificationToast');
    if (!toast) {
        // Создаем временное уведомление если основное не найдено
        const tempToast = document.createElement('div');
        tempToast.className = `fixed top-4 right-4 z-[1400] px-4 py-3 rounded-lg shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        }`;
        tempToast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span>${title}: ${message}</span>
            </div>
        `;
        document.body.appendChild(tempToast);
        
        setTimeout(() => {
            tempToast.remove();
        }, 5000);
        return;
    }
    
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    
    if (toastTitle) toastTitle.textContent = title;
    if (toastMessage) toastMessage.textContent = message;
    
    // Обновляем стиль уведомления
    const typeColors = {
        'success': 'border-l-4 border-green-500',
        'error': 'border-l-4 border-red-500', 
        'warning': 'border-l-4 border-yellow-500',
        'info': 'border-l-4 border-blue-500'
    };
    
    // Сначала удаляем все border классы
    toast.className = toast.className
        .replace(/border-l-4\s+border-[a-z]+-500/g, '')
        .trim();
    
    // Добавляем правильный класс
    const borderClass = typeColors[type] || typeColors.info;
    toast.classList.add(...borderClass.split(' '));
    
    toast.classList.remove('hidden');
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }, 5000);
}

function hideToast() {
    const toast = document.getElementById('notificationToast');
    if (toast) {
        toast.classList.remove('show');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }
}

function showLoading(message = 'Загрузка...') {
    let loader = document.getElementById('loading-indicator');
    
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'loading-indicator';
        loader.className = 'fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center';
        loader.innerHTML = `
            <div class="bg-white rounded-xl p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                <p class="text-gray-700 font-medium">${message}</p>
            </div>
        `;
        document.body.appendChild(loader);
    } else {
        loader.classList.remove('hidden');
        const messageEl = loader.querySelector('p');
        if (messageEl) messageEl.textContent = message;
    }
}

function hideLoading() {
    const loader = document.getElementById('loading-indicator');
    if (loader) {
        loader.classList.add('hidden');
        setTimeout(() => {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        }, 300);
    }
}

function getValueByPath(obj, path) {
    if (!obj || !path) return undefined;
    return path.split('.').reduce((current, key) => {
        return current ? current[key] : undefined;
    }, obj);
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function showInfoPanel() {
    const infoPanel = document.getElementById('infoPanel');
    if (infoPanel) {
        infoPanel.classList.add('visible');
        setTimeout(() => infoPanel.classList.remove('visible'), 10000);
    }
}

// ============================================
// ФУНКЦИИ ДЛЯ КАРТЫ И НАВИГАЦИИ
// ============================================

function initNavigationMap() {
    if (!document.getElementById('navigation-map')) {
        console.error('Элемент карты не найден');
        return;
    }
    
    try {
        console.log('Инициализация карты...');
        
        // Используем координаты маршрута или центр России
        let startLat = 55.7558;
        let startLng = 37.6173;
        let routeCoordinates = [];
        
        // Пытаемся получить координаты маршрута
        @if(isset($route->coordinates) && !empty($route->coordinates))
            try {
                routeCoordinates = @json($route->coordinates);
                console.log('Координаты маршрута:', routeCoordinates);
                
                if (routeCoordinates && routeCoordinates.length > 0) {
                    // Берем среднюю точку маршрута
                    const sumLat = routeCoordinates.reduce((sum, coord) => sum + coord[0], 0);
                    const sumLng = routeCoordinates.reduce((sum, coord) => sum + coord[1], 0);
                    startLat = sumLat / routeCoordinates.length;
                    startLng = sumLng / routeCoordinates.length;
                }
            } catch (e) {
                console.error('Ошибка парсинга координат маршрута:', e);
            }
        @endif
        
        console.log('Центр карты:', startLat, startLng);
        
        navigationMap = L.map('navigation-map', {
            zoomControl: false
        }).setView([startLat, startLng], 13);
        
        // Добавляем базовый слой карты
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
            minZoom: 3
        }).addTo(navigationMap);
        
        // Добавляем контроль масштаба
        L.control.scale().addTo(navigationMap);
        
        // Добавляем маршрут если есть координаты
        if (routeCoordinates && routeCoordinates.length > 1) {
            try {
                routeLayer = L.polyline(routeCoordinates, {
                    color: '#3b82f6',
                    weight: 5,
                    opacity: 0.8,
                    smoothFactor: 1,
                    dashArray: '10, 10'
                }).addTo(navigationMap);
                
                // Устанавливаем обзор на весь маршрут
                const bounds = routeLayer.getBounds();
                if (bounds.isValid()) {
                    navigationMap.fitBounds(bounds, { 
                        padding: [50, 50],
                        maxZoom: 13
                    });
                }
                
                console.log('Маршрут добавлен, точек:', routeCoordinates.length);
            } catch (e) {
                console.error('Ошибка при добавлении маршрута:', e);
                showNotification('Карта', 'Не удалось отобразить маршрут', 'warning');
            }
        } else {
            console.warn('Нет координат маршрута для отображения');
        }
        
        // Добавляем точки маршрута
        @if(isset($checkpoints) && count($checkpoints) > 0)
            @foreach($checkpoints as $checkpoint)
                @if($checkpoint->latitude && $checkpoint->longitude)
                    try {
                        const marker = createCheckpointMarker(
                            {{ $checkpoint->id }},
                            {{ $checkpoint->latitude }},
                            {{ $checkpoint->longitude }},
                            {{ $checkpoint->order }},
                            {{ isset($currentCheckpoint) && $currentCheckpoint && $checkpoint->id === $currentCheckpoint->id ? 'true' : 'false' }},
                            {{ $checkpoint->isCompleted() ? 'true' : 'false' }},
                            '{{ addslashes($checkpoint->title) }}',
                            '{{ addslashes($checkpoint->type_label ?? 'Точка') }}'
                        );
                        checkpointMarkers.push(marker);
                    } catch (e) {
                        console.error('Ошибка создания маркера:', e);
                    }
                @endif
            @endforeach
        @endif
        
        initGeolocation();
        
        // Обновляем размер карты после загрузки
        setTimeout(() => {
            if (navigationMap) {
                navigationMap.invalidateSize();
                console.log('Размер карты обновлен');
            }
        }, 300);
        
    } catch (error) {
        console.error('Ошибка инициализации карты:', error);
        showNotification('Ошибка', 'Не удалось загрузить карту', 'error');
    }
}

function createCheckpointMarker(id, lat, lng, order, isActive, isCompleted, title, type) {
    let color, iconHtml;
    
    if (isActive) {
        color = '#ef4444';
        iconHtml = '<i class="fas fa-bullseye"></i>';
    } else if (isCompleted) {
        color = '#10b981';
        iconHtml = '<i class="fas fa-check"></i>';
    } else {
        color = '#6b7280';
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

function initGeolocation() {
    if (!navigator.geolocation) {
        showNotification('Внимание', 'Геолокация не поддерживается вашим браузером', 'warning');
        return;
    }
    
    // Проверяем разрешение на геолокацию
    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({name: 'geolocation'})
            .then(function(permissionStatus) {
                console.log('Geolocation permission status:', permissionStatus.state);
                
                if (permissionStatus.state === 'denied') {
                    showNotification('Геолокация', 'Доступ к геолокации запрещен. Разрешите доступ в настройках браузера.', 'error');
                    showManualLocationSelector();
                    return;
                }
                
                if (permissionStatus.state === 'prompt') {
                    showNotification('Геолокация', 'Разрешите доступ к вашему местоположению для работы навигатора', 'info');
                }
                
                // Запрашиваем текущее местоположение
                getCurrentPositionWithTimeout();
            })
            .catch(function(error) {
                console.warn('Permission query error:', error);
                // Если не поддерживается permissions API, пробуем получить позицию напрямую
                getCurrentPositionWithTimeout();
            });
    } else {
        // Для браузеров без поддержки Permissions API
        getCurrentPositionWithTimeout();
    }
}

function getCurrentPositionWithTimeout() {
    navigator.geolocation.getCurrentPosition(
        showCurrentPosition,
        function(error) {
            // Более детальная обработка ошибок
            let message = '';
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Доступ к геолокации запрещен. Разрешите доступ в настройках браузера.';
                    showManualLocationSelector();
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Информация о местоположении недоступна. Проверьте GPS и интернет соединение.';
                    break;
                case error.TIMEOUT:
                    message = 'Время ожидания определения местоположения истекло. Попробуйте снова.';
                    setTimeout(getCurrentPositionWithTimeout, 3000);
                    break;
                default:
                    message = 'Неизвестная ошибка геолокации.';
            }
            
            if (message) {
                showNotification('Геолокация', message, 'warning');
            }
        },
        { 
            enableHighAccuracy: true, 
            timeout: 10000, 
            maximumAge: 60000 
        }
    );
    
    // Запускаем слежение за позицией
    watchId = navigator.geolocation.watchPosition(
        updateCurrentPosition,
        function(error) {
            console.warn('Watch position error:', error.message);
            // Не показываем уведомление для каждой ошибки слежения
        },
        { 
            enableHighAccuracy: true, 
            timeout: 5000, 
            maximumAge: 1000 
        }
    );
}

function showCurrentPosition(position) {
    console.log('Position received:', position);
    
    currentPosition = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
        accuracy: position.coords.accuracy,
        heading: position.coords.heading,
        speed: position.coords.speed
    };
    
    if (navigationMap) {
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
            }),
            zIndexOffset: 1000
        }).addTo(navigationMap);
        
        currentPositionMarker.bindPopup('<b>Ваше местоположение</b>').openPopup();
        
        // Добавляем круг точности
        if (position.coords.accuracy) {
            L.circle([currentPosition.lat, currentPosition.lng], {
                radius: position.coords.accuracy,
                className: 'accuracy-circle',
                color: '#3b82f6',
                fillColor: '#3b82f6',
                weight: 1,
                fillOpacity: 0.1
            }).addTo(navigationMap);
        }
        
        // Центрируем карту на пользователе
        navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
    }
    
    setTimeout(showInfoPanel, 1000);
}

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
    updateDistanceToCheckpoint();
}

function updateDistanceToCheckpoint() {
    @if(isset($currentCheckpoint) && $currentCheckpoint && $currentCheckpoint->latitude && $currentCheckpoint->longitude)
        if (currentPosition && currentPosition.lat && currentPosition.lng) {
            const distance = calculateDistance(
                currentPosition.lat,
                currentPosition.lng,
                {{ $currentCheckpoint->latitude }},
                {{ $currentCheckpoint->longitude }}
            );
            
            console.log('Расстояние до точки:', distance.toFixed(3), 'км');
            
            if (distance < 0.1) {
                showNotification('Приближение', 'Вы близко к контрольной точке!', 'info');
            }
            
            if (distance < 0.05) {
                showNotification('Прибытие', 'Вы на месте! Отметьте прибытие.', 'success');
            }
        }
    @endif
}

function showManualLocationSelector() {
    // Проверяем, нет ли уже селектора
    if (document.querySelector('.manual-location-selector')) return;
    
    // Создаем кнопку для ручного выбора местоположения
    const manualSelector = document.createElement('div');
    manualSelector.className = 'manual-location-selector fixed bottom-20 right-4 z-[1003] bg-white rounded-lg shadow-lg p-4 max-w-sm animate-slide-up';
    manualSelector.innerHTML = `
        <div class="mb-3">
            <h4 class="font-bold text-gray-800">Установить местоположение вручную</h4>
            <p class="text-sm text-gray-600 mt-1">Используйте карту для выбора точки</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="useMapForLocation()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                Выбрать на карте
            </button>
            <button onclick="this.parentElement.remove()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                Закрыть
            </button>
        </div>
    `;
    
    document.body.appendChild(manualSelector);
}

function useMapForLocation() {
    if (!navigationMap) return;
    
    showNotification('Карта', 'Нажмите на карту для выбора местоположения', 'info');
    
    // Убираем предыдущий обработчик если есть
    if (window.mapLocationClickHandler) {
        navigationMap.off('click', window.mapLocationClickHandler);
    }
    
    // Добавляем обработчик клика по карте
    window.mapLocationClickHandler = function(e) {
        currentPosition = {
            lat: e.latlng.lat,
            lng: e.latlng.lng,
            accuracy: 50 // Примерная точность ручного выбора
        };
        
        if (currentPositionMarker) {
            currentPositionMarker.setLatLng([currentPosition.lat, currentPosition.lng]);
        } else {
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
        }
        
        navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
        showNotification('Успех', 'Местоположение установлено', 'success');
        
        // Убираем обработчик после выбора
        navigationMap.off('click', window.mapLocationClickHandler);
        delete window.mapLocationClickHandler;
        
        // Убираем панель выбора
        const manualSelector = document.querySelector('.manual-location-selector');
        if (manualSelector) manualSelector.remove();
    };
    
    navigationMap.on('click', window.mapLocationClickHandler);
}

function arriveAtCheckpoint(checkpointId) {
    if (confirm('Подтвердите прибытие на контрольную точку')) {
        showLoading('Отмечаем прибытие...');
        
        fetch(`/api/checkpoints/${checkpointId}/arrive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ comment: 'Прибыл на точку' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Успех', 'Точка успешно отмечена!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Ошибка', data.message || 'Не удалось отметить прибытие', 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка', 'Ошибка при отправке запроса', 'error');
        })
        .finally(() => hideLoading());
    }
}

function skipCheckpoint(checkpointId) {
    if (confirm('Вы уверены, что хотите пропустить эту точку?')) {
        showLoading('Пропускаем точку...');
        
        fetch(`/api/checkpoints/${checkpointId}/skip`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Успех', 'Точка пропущена', 'info');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Ошибка', data.message || 'Не удалось пропустить точку', 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка', 'Ошибка при отправке запроса', 'error');
        })
        .finally(() => hideLoading());
    }
}

// ============================================
// ФУНКЦИИ ДЛЯ ЗАДАНИЙ
// ============================================

async function completeTask(taskId) {
    console.log('Открываем задание ID:', taskId);
    lastTaskId = taskId;
    currentTaskId = taskId;
    
    try {
        showLoading('Загружаем задание...');
        
        const response = await fetch(`/api/tasks/${taskId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.data && data.data.task) {
            displayTask(data.data);
        } else {
            displayTaskError(data.message || 'Не удалось загрузить задание');
        }
    } catch (error) {
        console.error('Ошибка загрузки задания:', error);
        displayTaskError('Ошибка загрузки задания');
    } finally {
        hideLoading();
    }
}

function displayTask(taskData) {
    if (!taskData || !taskData.task) {
        displayTaskError('Данные задания не получены');
        return;
    }
    
    currentTask = taskData.task;
    console.log('Текущее задание:', currentTask);
    
    let templateId;
    switch(currentTask.type) {
        case 'text': templateId = 'task-template-text'; break;
        case 'image': templateId = 'task-template-image'; break;
        case 'quiz': templateId = 'task-template-quiz'; break;
        case 'code': templateId = 'task-template-code'; break;
        case 'cipher': templateId = 'task-template-cipher'; break;
        case 'puzzle': templateId = 'task-template-puzzle'; break;
        case 'location': templateId = 'task-template-location'; break;
        default: templateId = 'task-template-text';
    }
    
    const template = document.getElementById(templateId);
    if (!template) {
        console.error('Шаблон не найден:', templateId);
        displayTaskError('Шаблон задания не найден');
        return;
    }
    
    const clone = template.content.cloneNode(true);
    
    // Биндинг данных через data-bind атрибуты
    const elements = clone.querySelectorAll('[data-bind]');
    elements.forEach(element => {
        const bindPath = element.getAttribute('data-bind');
        const value = getValueByPath(currentTask, bindPath);
        
        if (value !== undefined && value !== null) {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                element.value = value;
            } else if (element.tagName === 'IMG') {
                element.src = value;
            } else {
                element.textContent = value;
            }
        }
    });
    
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        taskContent.innerHTML = '';
        taskContent.appendChild(clone);
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Блокируем скролл страницы
            
            // Устанавливаем фокус на первое поле ввода если есть
            setTimeout(() => {
                const firstInput = modal.querySelector('input, textarea, button');
                if (firstInput) firstInput.focus();
            }, 100);
        }
        
        initTaskSpecificFeatures();
    }
}

function displayTaskError(message = 'Не удалось загрузить задание') {
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        taskContent.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ошибка загрузки задания</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <div class="space-y-3">
                    <button onclick="retryLoadTask()"
                            class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium">
                        <i class="fas fa-redo mr-2"></i> Повторить попытку
                    </button>
                    <button onclick="closeTaskModal()"
                            class="w-full px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Закрыть
                    </button>
                </div>
            </div>
        `;
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
}

function displayTaskResult(result) {
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        taskContent.innerHTML = `
            <div class="text-center py-8">
                <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Задание выполнено!</h3>
                <p class="text-gray-600 mb-2">${result.task?.title || 'Задание'}</p>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-8 max-w-sm mx-auto">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Награда:</span>
                            <span class="font-bold text-green-600">+${result.task?.points || 0} очков</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Получено XP:</span>
                            <span class="font-bold text-blue-600">+${result.task?.xp_earned || 0} XP</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Статус:</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check mr-1"></i> Выполнено
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="text-sm text-gray-500 mb-8">${result.message || 'Задание успешно выполнено!'}</div>
                
                <button onclick="closeTaskModalAndRefresh()"
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold hover:from-blue-600 hover:to-indigo-700">
                    Продолжить маршрут
                </button>
            </div>
        `;
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
}

function initTaskSpecificFeatures() {
    if (!currentTask) return;
    
    switch(currentTask.type) {
        case 'text': initTextTask(); break;
        case 'image': initImageTask(); break;
        case 'quiz': initQuizTask(); break;
        case 'code': initCodeTask(); break;
        case 'cipher': initCipherTask(); break;
        case 'puzzle': initPuzzleTask(); break;
        case 'location': initLocationTask(); break;
    }
}

function initTextTask() {
    const textarea = document.getElementById('task-text-answer');
    const charCount = document.getElementById('text-char-count');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        charCount.textContent = textarea.value.length;
    }
}

function initImageTask() {
    // Кнопка "Сделать фото"
    const takePhotoBtn = document.getElementById('take-photo-btn');
    const fileInput = document.getElementById('photo-file-input');
    
    if (takePhotoBtn && fileInput) {
        takePhotoBtn.addEventListener('click', function() {
            fileInput.setAttribute('capture', 'environment');
            fileInput.click();
        });
    }
    
    // Кнопка "Выбрать из галереи"
    const choosePhotoBtn = document.getElementById('choose-photo-btn');
    if (choosePhotoBtn && fileInput) {
        choosePhotoBtn.addEventListener('click', function() {
            fileInput.removeAttribute('capture');
            fileInput.click();
        });
    }
    
    // Обработка выбора файла
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                handlePhotoFileSelect(e.target.files[0]);
            }
        });
    }
}

function handlePhotoFileSelect(file) {
    if (!file) return;
    
    // Проверка типа файла
    if (!file.type.match('image.*')) {
        showNotification('Ошибка', 'Пожалуйста, выберите изображение (JPEG, PNG, GIF)', 'error');
        return;
    }
    
    // Проверка размера файла
    if (file.size > 10 * 1024 * 1024) { // 10MB
        showNotification('Ошибка', 'Файл слишком большой (максимум 10MB)', 'error');
        return;
    }
    
    // Показываем индикатор загрузки
    const loading = document.getElementById('photo-loading');
    if (loading) loading.classList.remove('hidden');
    
    const reader = new FileReader();
    
    reader.onload = function(e) {
        // Обновляем предпросмотр
        const previewImage = document.getElementById('photo-preview-image');
        const previewContainer = document.getElementById('photo-preview-container');
        const filename = document.getElementById('photo-filename');
        const filesize = document.getElementById('photo-filesize');
        const uploadButton = document.getElementById('upload-button-container');
        
        if (previewImage) previewImage.src = e.target.result;
        if (previewContainer) previewContainer.classList.remove('hidden');
        if (filename) filename.textContent = `Имя: ${file.name}`;
        if (filesize) filesize.textContent = `Размер: ${formatFileSize(file.size)}`;
        if (uploadButton) uploadButton.classList.remove('hidden');
        
        // Скрываем индикатор загрузки
        if (loading) loading.classList.add('hidden');
    };
    
    reader.onerror = function() {
        showNotification('Ошибка', 'Не удалось загрузить файл', 'error');
        if (loading) loading.classList.add('hidden');
    };
    
    reader.readAsDataURL(file);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function removePhotoPreview() {
    const previewContainer = document.getElementById('photo-preview-container');
    const fileInput = document.getElementById('photo-file-input');
    const uploadButton = document.getElementById('upload-button-container');
    const description = document.getElementById('photo-description');
    
    if (previewContainer) previewContainer.classList.add('hidden');
    if (fileInput) fileInput.value = '';
    if (uploadButton) uploadButton.classList.add('hidden');
    if (description) description.value = '';
}

function initQuizTask() {
    // Инициализация викторины будет реализована позже
    console.log('Initializing quiz task');
}

function initCodeTask() {
    // Инициализация задания с кодом
    console.log('Initializing code task');
}

function initPuzzleTask() {
    // Инициализация головоломки
    console.log('Initializing puzzle task');
}

function initLocationTask() {
    getCurrentLocationForTask();
}

function getCurrentLocationForTask() {
    if (!navigator.geolocation) {
        const locationInfo = document.getElementById('location-info');
        if (locationInfo) {
            locationInfo.innerHTML = `
                <div class="text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Геолокация не поддерживается вашим браузером
                </div>
            `;
        }
        showNotification('Ошибка', 'Геолокация не поддерживается вашим браузером', 'error');
        return;
    }
    
    const locationInfo = document.getElementById('location-info');
    const submitBtn = document.getElementById('submit-location-btn');
    
    if (locationInfo) {
        locationInfo.innerHTML = '<div class="flex items-center mb-2"><i class="fas fa-sync-alt animate-spin mr-2"></i>Определяем местоположение...</div>';
    }
    
    if (submitBtn) {
        submitBtn.disabled = true;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentTaskLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                accuracy: position.coords.accuracy
            };
            
            if (locationInfo) {
                locationInfo.innerHTML = `
                    <div class="space-y-1">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Местоположение определено</span>
                        </div>
                        <div class="text-xs font-mono">
                            Широта: ${currentTaskLocation.lat.toFixed(6)}
                        </div>
                        <div class="text-xs font-mono">
                            Долгота: ${currentTaskLocation.lng.toFixed(6)}
                        </div>
                        <div class="text-xs text-gray-500">
                            Точность: ±${Math.round(currentTaskLocation.accuracy)}м
                        </div>
                    </div>
                `;
            }
            
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        },
        function(error) {
            console.error('Ошибка получения локации:', error);
            
            if (locationInfo) {
                locationInfo.innerHTML = `
                    <div class="text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Не удалось определить местоположение
                    </div>
                `;
            }
            
            showNotification('Ошибка', 'Не удалось определить ваше местоположение', 'error');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// ============================================
// ФУНКЦИИ ОТПРАВКИ ЗАДАНИЙ
// ============================================

async function submitTextTask() {
    const answer = document.getElementById('task-text-answer')?.value.trim();
    
    if (!answer) {
        showNotification('Внимание', 'Введите ответ', 'warning');
        return;
    }
    
    await submitTaskCompletion({ answer: answer });
}

async function uploadTaskPhoto() {
    const fileInput = document.getElementById('photo-file-input');
    const description = document.getElementById('photo-description')?.value || '';
    const uploadBtn = document.getElementById('upload-photo-btn');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showNotification('Внимание', 'Выберите фото', 'warning');
        return;
    }
    
    const file = fileInput.files[0];
    
    // Проверка размера
    if (file.size > 10 * 1024 * 1024) {
        showNotification('Ошибка', 'Файл слишком большой (макс. 10MB)', 'error');
        return;
    }
    
    // Блокируем кнопку
    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загружается...';
    }
    
    showLoading('Загружаем фото...');
    
    try {
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('comment', description);
        formData.append('answer', 'photo_uploaded');
        
        const token = getCsrfToken();
        
        const response = await fetch(`/api/tasks/${currentTaskId}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Успех', 'Фото успешно загружено!', 'success');
            displayTaskResult(data.data || data);
        } else {
            showNotification('Ошибка', data.message || 'Ошибка загрузки', 'error');
        }
    } catch (error) {
        console.error('Ошибка загрузки фото:', error);
        showNotification('Ошибка', 'Ошибка при загрузке фото', 'error');
    } finally {
        hideLoading();
        
        // Восстанавливаем кнопку
        if (uploadBtn) {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Загрузить фото';
        }
    }
}

async function submitQuizTask() {
    showNotification('Информация', 'Функция викторины в разработке', 'info');
}

async function submitCodeTask() {
    showNotification('Информация', 'Функция задания с кодом в разработке', 'info');
}

async function submitCipherTask() {
    showNotification('Информация', 'Функция шифра в разработке', 'info');
}

async function submitPuzzleTask() {
    showNotification('Информация', 'Функция головоломки в разработке', 'info');
}

async function submitLocationTask() {
    if (!currentTaskLocation) {
        showNotification('Внимание', 'Сначала определите ваше местоположение', 'warning');
        getCurrentLocationForTask();
        return;
    }
    
    await submitTaskCompletion({
        latitude: currentTaskLocation.lat,
        longitude: currentTaskLocation.lng
    });
}

async function submitTaskCompletion(data) {
    showLoading('Проверяем ответ...');
    
    try {
        const result = await fetch(`/api/tasks/${currentTaskId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const response = await result.json();
        
        if (response.success) {
            displayTaskResult(response.data || response);
        } else {
            showNotification('Ошибка', response.message || 'Ошибка при выполнении задания', 'error');
        }
    } catch (error) {
        console.error('Ошибка выполнения задания:', error);
        showNotification('Ошибка', 'Ошибка при выполнении задания', 'error');
    } finally {
        hideLoading();
    }
}

async function requestHint() {
    showLoading('Получаем подсказку...');
    
    try {
        const response = await fetch(`/api/tasks/${currentTaskId}/hint`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Подсказка', data.data?.hint || 'Подсказка получена', 'info');
        } else {
            showNotification('Ошибка', data.message || 'Не удалось получить подсказку', 'error');
        }
    } catch (error) {
        console.error('Ошибка получения подсказки:', error);
        showNotification('Ошибка', 'Ошибка получения подсказки', 'error');
    } finally {
        hideLoading();
    }
}

function retryLoadTask() {
    if (lastTaskId) {
        completeTask(lastTaskId);
    }
}

function closeTaskModalAndRefresh() {
    closeTaskModal();
    setTimeout(() => {
        location.reload();
    }, 500);
}

function closeTaskModal() {
    const modal = document.getElementById('task-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Разблокируем скролл страницы
    }
    currentTask = null;
    currentTaskId = null;
    
    if (locationWatchId) {
        navigator.geolocation.clearWatch(locationWatchId);
        locationWatchId = null;
    }
}

// ============================================
// ОБРАБОТЧИКИ СОБЫТИЙ И ИНИЦИАЛИЗАЦИЯ
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализируем навигацию...');
    
    // Инициализация карты
    setTimeout(() => {
        initNavigationMap();
    }, 100);
    
    // Боковая панель
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleSidebar && sidebar) {
        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
    }
    
    if (closeSidebar && sidebar) {
        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
    }

    // Переключение вкладок
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Убираем активный класс у всех кнопок
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            // Убираем активный класс у всех контентов
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.remove('active');
            });
            
            // Добавляем активный класс текущей кнопке
            this.classList.add('active');
            
            // Показываем соответствующий контент
            const tabContent = document.getElementById(`tab-${tabId}`);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });

    // Кнопка "Мое местоположение"
    const myLocationBtn = document.getElementById('my-location');
    if (myLocationBtn) {
        myLocationBtn.addEventListener('click', () => {
            if (currentPosition && navigationMap) {
                navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
                showNotification('Геолокация', 'Карта центрирована на вашем местоположении', 'info');
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    if (navigationMap) {
                        const latlng = [position.coords.latitude, position.coords.longitude];
                        navigationMap.setView(latlng, 15);
                        showNotification('Геолокация', 'Карта центрирована на вашем местоположении', 'info');
                    }
                }, null, { enableHighAccuracy: true, timeout: 5000 });
            }
        });
    }

    // Кнопка "Центрировать маршрут"
    const centerRouteBtn = document.getElementById('center-route');
    if (centerRouteBtn && navigationMap) {
        centerRouteBtn.addEventListener('click', () => {
            if (routeLayer) {
                navigationMap.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                showNotification('Карта', 'Карта центрирована на маршруте', 'info');
            } else if (checkpointMarkers.length > 0) {
                // Если нет маршрута, центрируем по точкам
                const group = new L.featureGroup(checkpointMarkers.map(m => m.marker || m));
                navigationMap.fitBounds(group.getBounds(), { padding: [50, 50] });
                showNotification('Карта', 'Карта центрирована на точках маршрута', 'info');
            }
        });
    }

    // Кнопка "Скрыть/показать панель"
    const toggleHeaderBtn = document.getElementById('toggle-header');
    const navigationHeader = document.getElementById('navigationHeader');
    
    if (toggleHeaderBtn && navigationHeader) {
        toggleHeaderBtn.addEventListener('click', () => {
            if (isHeaderVisible) {
                navigationHeader.classList.add('hidden');
                toggleHeaderBtn.innerHTML = '<i class="fas fa-eye"></i>';
                toggleHeaderBtn.title = 'Показать панель';
            } else {
                navigationHeader.classList.remove('hidden');
                toggleHeaderBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                toggleHeaderBtn.title = 'Скрыть панель';
            }
            
            isHeaderVisible = !isHeaderVisible;
            
            setTimeout(() => {
                if (navigationMap) {
                    navigationMap.invalidateSize();
                }
            }, 300);
        });
    }

    // Обработчики для кнопок заданий
    document.addEventListener('click', function(e) {
        // Обработка кнопок заданий
        const taskBtn = e.target.closest('[onclick*="completeTask"]');
        if (taskBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            const onclick = taskBtn.getAttribute('onclick');
            const match = onclick.match(/completeTask\((\d+)\)/);
            if (match) {
                const taskId = parseInt(match[1]);
                console.log('Нажата кнопка задания ID:', taskId);
                completeTask(taskId);
            }
        }
        
        // Закрытие модального окна при клике вне его
        const modal = document.getElementById('task-modal');
        if (modal && !modal.classList.contains('hidden') && e.target === modal) {
            closeTaskModal();
        }
    });
    
    // Обработка Escape для закрытия модальных окон
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('task-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeTaskModal();
            }
        }
    });
    
    // Показываем панель на мобильных устройствах
    if (window.innerWidth < 768 && sidebar) {
        setTimeout(() => {
            sidebar.classList.add('active');
        }, 1500);
    }
    
    // Показываем информационную панель через 2 секунды
    setTimeout(showInfoPanel, 2000);
    
    console.log('Инициализация навигации завершена');
});

// Очистка при разгрузке страницы
window.addEventListener('beforeunload', () => {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
    
    if (locationWatchId) {
        navigator.geolocation.clearWatch(locationWatchId);
    }
});

// Глобальные функции для доступа из HTML
window.completeTask = completeTask;
window.arriveAtCheckpoint = arriveAtCheckpoint;
window.skipCheckpoint = skipCheckpoint;
window.closeTaskModal = closeTaskModal;
window.retryLoadTask = retryLoadTask;
window.closeTaskModalAndRefresh = closeTaskModalAndRefresh;
window.removePhotoPreview = removePhotoPreview;
window.uploadTaskPhoto = uploadTaskPhoto;
window.requestHint = requestHint;
window.getCurrentLocation = getCurrentLocationForTask;
window.showNotification = showNotification;

</script>
@endpush