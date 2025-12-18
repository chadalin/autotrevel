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
                    
                    <div class="mt-3 required-elements" style="display: none;">
                        <div class="text-sm font-medium text-gray-700 mb-1">На фото должны быть:</div>
                        <ul class="list-disc list-inside text-sm text-gray-600 required-elements-list">
                        </ul>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Выберите фото:</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('task-photo-input').click()">
                        <input type="file" id="task-photo-input" accept="image/*" capture="environment" 
                               class="hidden" onchange="previewTaskPhoto(this)">
                        <div id="photo-upload-area">
                            <i class="fas fa-camera text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-600 mb-1">Нажмите для выбора фото</p>
                            <p class="text-sm text-gray-500">или сделайте снимок (до 10MB)</p>
                        </div>
                    </div>
                    <div id="photo-preview-container" class="mt-3 hidden">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <i class="fas fa-image text-gray-400 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-800 text-sm" id="photo-filename"></div>
                                        <div class="text-xs text-gray-500" id="photo-filesize"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="removePhotoPreview()" 
                                        class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <img id="photo-preview-image" class="w-full h-48 object-cover rounded-lg">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Описание фото:</label>
                    <textarea id="photo-description" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        placeholder="Опишите, что на фото..."></textarea>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitPhotoTask()"
                            class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-bold">
                        <i class="fas fa-upload mr-2"></i>Загрузить фото
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
    
    try {
        const response = await fetch(url, mergedOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Fetch Error:', error);
        throw error;
    }
}

function showNotification(title, message, type = 'info') {
    const toast = document.getElementById('notificationToast');
    if (!toast) return;
    
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    
    if (toastTitle) toastTitle.textContent = title;
    if (toastMessage) toastMessage.textContent = message;
    
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}

function hideToast() {
    const toast = document.getElementById('notificationToast');
    if (toast) toast.classList.remove('show');
}

function showLoading(message = 'Загрузка...') {
    let loader = document.getElementById('loading-indicator');
    
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'loading-indicator';
        loader.className = 'fixed inset-0 z-[1400] bg-black bg-opacity-50 flex items-center justify-center';
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
    if (loader) loader.classList.add('hidden');
}

function getValueByPath(obj, path) {
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
        const startLat = {{ $route->start_coordinates['lat'] ?? 55.7558 }};
        const startLng = {{ $route->start_coordinates['lng'] ?? 37.6173 }};
        navigationMap = L.map('navigation-map').setView([startLat, startLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
            minZoom: 3
        }).addTo(navigationMap);
        
        @if($route->coordinates && !empty($route->coordinates))
            try {
                const coordinates = @json($route->coordinates);
                if (coordinates && coordinates.length > 0) {
                    routeLayer = L.polyline(coordinates, {
                        color: '#3b82f6',
                        weight: 5,
                        opacity: 0.8,
                        smoothFactor: 1,
                        dashArray: '10, 10'
                    }).addTo(navigationMap);
                    
                    navigationMap.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                }
            } catch (e) {
                console.error('Ошибка при добавлении маршрута:', e);
            }
        @endif
        
        @foreach($checkpoints as $checkpoint)
            @if($checkpoint->latitude && $checkpoint->longitude)
                try {
                    const marker = createCheckpointMarker(
                        {{ $checkpoint->id }},
                        {{ $checkpoint->latitude }},
                        {{ $checkpoint->longitude }},
                        {{ $checkpoint->order }},
                        {{ $currentCheckpoint && $checkpoint->id === $currentCheckpoint->id ? 'true' : 'false' }},
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
        
        initGeolocation();
        
        setTimeout(() => {
            if (navigationMap) {
                navigationMap.invalidateSize();
                console.log('Размер карты обновлен');
            }
        }, 100);
        
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
    
    navigator.geolocation.getCurrentPosition(
        showCurrentPosition,
        handleLocationError,
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
    
    watchId = navigator.geolocation.watchPosition(
        updateCurrentPosition,
        handleLocationError,
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 1000 }
    );
}

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
    
    if (navigationMap) {
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
    @if($currentCheckpoint)
        if (currentPosition && currentPosition.lat && currentPosition.lng) {
            const distance = calculateDistance(
                currentPosition.lat,
                currentPosition.lng,
                {{ $currentCheckpoint->latitude }},
                {{ $currentCheckpoint->longitude }}
            );
            
            if (distance < 0.1) {
                showNotification('Приближение', 'Вы близко к контрольной точке!', 'info');
            }
            
            if (distance < 0.05) {
                showNotification('Прибытие', 'Вы на месте! Отметьте прибытие.', 'success');
            }
        }
    @endif
}

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

function arriveAtCheckpoint(checkpointId) {
    if (confirm('Подтвердите прибытие на контрольную точку')) {
        showLoading('Отмечаем прибытие...');
        
        apiFetch(`/api/checkpoints/${checkpointId}/arrive`, {
            method: 'POST',
            body: JSON.stringify({ comment: 'Прибыл на точку' })
        })
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
        
        apiFetch(`/api/checkpoints/${checkpointId}/skip`, {
            method: 'POST'
        })
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
        
        const response = await apiFetch(`{{ url('/api/tasks') }}/${taskId}`);
        
        if (response.success && response.data && response.data.task) {
            displayTask(response.data);
        } else {
            displayTaskError(response.message || 'Не удалось загрузить задание');
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
        displayTaskError('Шаблон задания не найден');
        return;
    }
    
    const clone = template.content.cloneNode(true);
    
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
    
    if (templateId === 'task-template-quiz' && currentTask.content && currentTask.content.options) {
        const quizOptions = clone.querySelector('#quiz-options');
        if (quizOptions) {
            const multipleChoice = currentTask.content.multiple_choice || false;
            quizOptions.innerHTML = '';
            
            currentTask.content.options.forEach((option, index) => {
                const label = document.createElement('label');
                label.className = 'flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors mb-2';
                label.innerHTML = `
                    <input type="${multipleChoice ? 'checkbox' : 'radio'}" 
                           name="quiz-option" 
                           value="${option}" 
                           class="mr-3">
                    <span class="text-gray-700">${option}</span>
                `;
                quizOptions.appendChild(label);
            });
        }
    }
    
    if (templateId === 'task-template-puzzle' && currentTask.content && currentTask.content.pieces) {
        const puzzleGrid = clone.querySelector('#puzzle-grid');
        if (puzzleGrid) {
            puzzleGrid.innerHTML = '';
            
            currentTask.content.pieces.forEach((piece, index) => {
                const pieceDiv = document.createElement('div');
                pieceDiv.className = 'puzzle-piece bg-white border border-gray-300 rounded-lg p-3 text-center cursor-move';
                pieceDiv.setAttribute('data-piece', index);
                pieceDiv.textContent = piece;
                puzzleGrid.appendChild(pieceDiv);
            });
        }
    }
    
    if (templateId === 'task-template-image' && currentTask.content && currentTask.content.required_elements) {
        const requiredElements = clone.querySelector('.required-elements');
        const requiredElementsList = clone.querySelector('.required-elements-list');
        
        if (requiredElements && requiredElementsList && currentTask.content.required_elements.length > 0) {
            requiredElements.style.display = 'block';
            currentTask.content.required_elements.forEach(element => {
                const li = document.createElement('li');
                li.textContent = element;
                requiredElementsList.appendChild(li);
            });
        }
    }
    
    if (templateId === 'task-template-code' && currentTask.content && currentTask.content.expected_output) {
        const expectedOutput = clone.querySelector('.expected-output');
        if (expectedOutput) {
            expectedOutput.style.display = 'block';
        }
    }
    
    if (templateId === 'task-template-cipher' && currentTask.content && currentTask.content.hint) {
        const hintContainer = clone.querySelector('.hint-container');
        if (hintContainer) {
            hintContainer.style.display = 'block';
        }
    }
    
    if (templateId === 'task-template-location' && currentTask.content) {
        if (currentTask.content.coordinates) {
            const coordinatesContainer = clone.querySelector('.coordinates-container');
            if (coordinatesContainer) {
                coordinatesContainer.style.display = 'block';
            }
        }
        
        if (currentTask.content.qr_code) {
            const qrContainer = clone.querySelector('.qr-container');
            if (qrContainer) {
                qrContainer.style.display = 'block';
            }
        }
    }
    
    if (currentTask.hints_available && currentTask.hints_available > 0) {
        const hintsSection = clone.querySelector('.hints-section');
        if (hintsSection) {
            hintsSection.style.display = 'block';
        }
    }
    
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        taskContent.innerHTML = '';
        taskContent.appendChild(clone);
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        
        initTaskSpecificFeatures();
    }
}

function displayTaskError(message = 'Не удалось загрузить задание') {
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        const errorTemplate = document.getElementById('task-template-error');
        if (!errorTemplate) {
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
        } else {
            const clone = errorTemplate.content.cloneNode(true);
            const errorMessage = clone.getElementById('error-message');
            if (errorMessage) {
                errorMessage.textContent = message;
            }
            taskContent.innerHTML = '';
            taskContent.appendChild(clone);
        }
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
}

function displayTaskResult(result) {
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        const resultTemplate = document.getElementById('task-template-result');
        if (!resultTemplate) {
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
        } else {
            const clone = resultTemplate.content.cloneNode(true);
            
            const resultTitle = clone.getElementById('result-title');
            const resultPoints = clone.getElementById('result-points');
            const resultXp = clone.getElementById('result-xp');
            const resultMessage = clone.getElementById('result-message');
            
            if (resultTitle) resultTitle.textContent = result.task?.title || 'Задание';
            if (resultPoints) resultPoints.textContent = `+${result.task?.points || 0} очков`;
            if (resultXp) resultXp.textContent = `+${result.task?.xp_earned || 0} XP`;
            if (resultMessage) resultMessage.textContent = result.message || 'Задание успешно выполнено!';
            
            taskContent.innerHTML = '';
            taskContent.appendChild(clone);
        }
        
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
    const fileInput = document.getElementById('task-photo-input');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            previewTaskPhoto(this);
        });
    }
}

function previewTaskPhoto(input) {
    const previewContainer = document.getElementById('photo-preview-container');
    const previewImage = document.getElementById('photo-preview-image');
    const filename = document.getElementById('photo-filename');
    const filesize = document.getElementById('photo-filesize');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
            
            if (filename) filename.textContent = file.name;
            if (filesize) filesize.textContent = formatFileSize(file.size);
        };
        
        reader.readAsDataURL(file);
    }
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
    const fileInput = document.getElementById('task-photo-input');
    
    if (previewContainer) previewContainer.classList.add('hidden');
    if (fileInput) fileInput.value = '';
}

function initQuizTask() {
    const options = document.querySelectorAll('#quiz-options label');
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            if (e.target.type === 'radio' || e.target.type === 'checkbox') {
                return;
            }
            
            const input = this.querySelector('input');
            if (input.type === 'radio') {
                options.forEach(opt => {
                    opt.classList.remove('bg-blue-50', 'border-blue-300');
                });
                this.classList.add('bg-blue-50', 'border-blue-300');
                input.checked = true;
            } else {
                this.classList.toggle('bg-blue-50');
                this.classList.toggle('border-blue-300');
                input.checked = !input.checked;
            }
        });
    });
}

function initCodeTask() {
    const textarea = document.getElementById('code-answer');
    if (textarea) {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    }
}

function initPuzzleTask() {
    const pieces = document.querySelectorAll('.puzzle-piece');
    let draggedPiece = null;
    
    pieces.forEach(piece => {
        piece.setAttribute('draggable', true);
        
        piece.addEventListener('dragstart', function(e) {
            draggedPiece = this;
            setTimeout(() => {
                this.classList.add('dragging');
            }, 0);
        });
        
        piece.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedPiece = null;
        });
        
        piece.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        piece.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedPiece && draggedPiece !== this) {
                const temp = this.innerHTML;
                this.innerHTML = draggedPiece.innerHTML;
                draggedPiece.innerHTML = temp;
                
                const tempData = this.getAttribute('data-piece');
                this.setAttribute('data-piece', draggedPiece.getAttribute('data-piece'));
                draggedPiece.setAttribute('data-piece', tempData);
            }
        });
    });
}

function initLocationTask() {
    getCurrentLocation();
}

function getCurrentLocation() {
    if (!navigator.geolocation) {
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
            
            if (locationWatchId) {
                navigator.geolocation.clearWatch(locationWatchId);
            }
            
            locationWatchId = navigator.geolocation.watchPosition(
                function(pos) {
                    currentTaskLocation = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    };
                },
                function(error) {
                    console.warn('Ошибка геолокации:', error);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
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

function showLocationOnMap() {
    if (currentTask && currentTask.content && currentTask.content.coordinates && navigationMap) {
        const lat = currentTask.content.coordinates.lat;
        const lng = currentTask.content.coordinates.lng;
        
        navigationMap.setView([lat, lng], 16);
        
        L.marker([lat, lng], {
            icon: L.divIcon({
                html: '<div style="width: 32px; height: 32px; background-color: red; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
                className: 'target-location-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            })
        }).addTo(navigationMap).bindPopup('Целевая точка задания');
        
        showNotification('Карта', 'Целевая точка показана на карте', 'info');
    }
}

function submitTextTask() {
    const answer = document.getElementById('task-text-answer')?.value.trim();
    
    if (!answer) {
        showNotification('Внимание', 'Введите ответ', 'warning');
        return;
    }
    
    submitTaskCompletion({ answer: answer });
}

function submitPhotoTask() {
    const fileInput = document.getElementById('task-photo-input');
    const description = document.getElementById('photo-description')?.value || '';
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showNotification('Внимание', 'Выберите фото', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('photo', fileInput.files[0]);
    formData.append('comment', description);
    formData.append('answer', 'photo_submission');
    
    showLoading('Загружаем фото...');
    
    const token = getCsrfToken();
    
    fetch(`{{ url('/api/tasks') }}${currentTaskId}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayTaskResult(data.data);
        } else {
            showNotification('Ошибка', data.message || 'Ошибка загрузки', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка загрузки фото:', error);
        showNotification('Ошибка', 'Ошибка загрузки фото', 'error');
    })
    .finally(() => hideLoading());
}

function submitQuizTask() {
    let answer;
    const multipleChoice = document.querySelector('input[name="quiz-option"]')?.type === 'checkbox';
    
    if (multipleChoice) {
        const selected = Array.from(document.querySelectorAll('input[name="quiz-option"]:checked'))
            .map(input => input.value);
        answer = selected.join(', ');
    } else {
        const selected = document.querySelector('input[name="quiz-option"]:checked');
        if (!selected) {
            showNotification('Внимание', 'Выберите вариант ответа', 'warning');
            return;
        }
        answer = selected.value;
    }
    
    submitTaskCompletion({ answer: answer });
}

function submitCodeTask() {
    const code = document.getElementById('code-answer')?.value.trim();
    
    if (!code) {
        showNotification('Внимание', 'Введите код', 'warning');
        return;
    }
    
    submitTaskCompletion({ code: code });
}

function submitCipherTask() {
    const decodedText = document.getElementById('cipher-answer')?.value.trim();
    
    if (!decodedText) {
        showNotification('Внимание', 'Введите расшифрованный текст', 'warning');
        return;
    }
    
    submitTaskCompletion({ decoded_text: decodedText });
}

function submitPuzzleTask() {
    const pieces = Array.from(document.querySelectorAll('.puzzle-piece'))
        .map(piece => piece.getAttribute('data-piece'));
    
    submitTaskCompletion({ solution: pieces });
}

function submitLocationTask() {
    if (!currentTaskLocation) {
        showNotification('Внимание', 'Сначала определите ваше местоположение', 'warning');
        getCurrentLocation();
        return;
    }
    
    submitTaskCompletion({
        latitude: currentTaskLocation.lat,
        longitude: currentTaskLocation.lng
    });
    
    if (locationWatchId) {
        navigator.geolocation.clearWatch(locationWatchId);
        locationWatchId = null;
    }
}

async function submitTaskCompletion(data) {
    showLoading('Проверяем ответ...');
    
    try {
        const result = await apiFetch(`/api/tasks/${currentTaskId}/complete`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            displayTaskResult(result.data);
        } else {
            showNotification('Ошибка', result.message || 'Ошибка при выполнении задания', 'error');
        }
    } catch (error) {
        console.error('Ошибка выполнения задания:', error);
        showNotification('Ошибка', 'Ошибка при выполнении задания', 'error');
    } finally {
        hideLoading();
    }
}

function requestHint() {
    showLoading('Получаем подсказку...');
    
    apiFetch(`/api/tasks/${currentTaskId}/hint`)
    .then(data => {
        if (data.success) {
            showNotification('Подсказка', data.data.hint, 'info');
        } else {
            showNotification('Ошибка', data.message || 'Не удалось получить подсказку', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка получения подсказки:', error);
        showNotification('Ошибка', 'Ошибка получения подсказки', 'error');
    })
    .finally(() => hideLoading());
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
    }
    currentTask = null;
    currentTaskId = null;
    
    if (locationWatchId) {
        navigator.geolocation.clearWatch(locationWatchId);
        locationWatchId = null;
    }
}

// ============================================
// ОБРАБОТЧИКИ СОБЫТИЙ
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализируем...');
    
    // Боковая панель
    document.getElementById('toggle-sidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.add('active');
    });

    document.getElementById('close-sidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.remove('active');
    });

    // Переключение вкладок
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            document.getElementById(`tab-${tabId}`)?.classList.add('active');
        });
    });

    // Кнопка "Мое местоположение"
    document.getElementById('my-location')?.addEventListener('click', () => {
        if (currentPosition && navigationMap) {
            navigationMap.setView([currentPosition.lat, currentPosition.lng], 15);
            showNotification('Геолокация', 'Карта центрирована на вашем местоположении', 'info');
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                if (navigationMap) {
                    navigationMap.setView([position.coords.latitude, position.coords.longitude], 15);
                    showNotification('Геолокация', 'Карта центрирована на вашем местоположении', 'info');
                }
            });
        }
    });

    // Кнопка "Центрировать маршрут"
    document.getElementById('center-route')?.addEventListener('click', () => {
        if (routeLayer && navigationMap) {
            navigationMap.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
            showNotification('Карта', 'Карта центрирована на маршруте', 'info');
        }
    });

    // Кнопка "Скрыть/показать панель"
    document.getElementById('toggle-header')?.addEventListener('click', () => {
        const header = document.getElementById('navigationHeader');
        const btn = document.getElementById('toggle-header');
        
        if (!header || !btn) return;
        
        if (isHeaderVisible) {
            header.classList.add('hidden');
            btn.innerHTML = '<i class="fas fa-eye"></i>';
            btn.title = 'Показать панель';
        } else {
            header.classList.remove('hidden');
            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
            btn.title = 'Скрыть панель';
        }
        
        isHeaderVisible = !isHeaderVisible;
        
        setTimeout(() => {
            if (navigationMap) {
                navigationMap.invalidateSize();
            }
        }, 300);
    });

    // Кнопка "Сделать фото"
    document.getElementById('take-photo')?.addEventListener('click', () => {
        showNotification('Фото', 'Используйте задание с фото для загрузки снимков', 'info');
    });

    // Обработчики для кнопок заданий
    document.addEventListener('click', function(e) {
        const taskBtn = e.target.closest('[onclick*="completeTask"]');
        if (taskBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            const onclick = taskBtn.getAttribute('onclick');
            const match = onclick?.match(/completeTask\((\d+)\)/);
            if (match) {
                const taskId = parseInt(match[1]);
                console.log('Нажата кнопка задания ID:', taskId);
                completeTask(taskId);
            }
        }
        
        // Закрытие модального окна при клике вне его
        const modal = document.getElementById('task-modal');
        if (modal && !modal.classList.contains('hidden') && 
            e.target === modal) {
            closeTaskModal();
        }
    });
    
    // Инициализация карты
    setTimeout(() => {
        initNavigationMap();
    }, 100);
    
    // Показываем панель на мобильных устройствах
    if (window.innerWidth < 768) {
        setTimeout(() => {
            document.getElementById('sidebar')?.classList.add('active');
        }, 1500);
    }
    
    // Показываем информационную панель через 2 секунды
    setTimeout(showInfoPanel, 2000);
    
    // Автоскрытие верхней панели при скролле
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        const header = document.getElementById('navigationHeader');
        if (!header) return;
        
        const currentScroll = window.pageYOffset;
        
        clearTimeout(scrollTimeout);
        
        if (currentScroll > lastScrollPosition && currentScroll > 100) {
            header.classList.add('hidden');
        } else {
            header.classList.remove('hidden');
        }
        
        lastScrollPosition = currentScroll;
        
        scrollTimeout = setTimeout(() => {
            header.classList.remove('hidden');
        }, 3000);
    });
    
    console.log('Инициализация завершена');
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
</script>
@endpush