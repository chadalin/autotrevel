@extends('layouts.app')

@section('title', $route->title . ' - AutoRuta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    #route-map {
        height: 500px;
        width: 100%;
        border-radius: 0.5rem;
        background-color: #f8fafc;
        position: relative;
    }
    
    .leaflet-container {
        font-family: 'Open Sans', sans-serif !important;
        font-size: 14px;
        z-index: 1;
    }
    
    .leaflet-popup-content {
        margin: 12px !important;
        line-height: 1.4;
        min-width: 200px;
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 8px !important;
        box-shadow: 0 3px 14px rgba(0,0,0,0.2) !important;
    }
    
    .rating-stars {
        display: inline-flex;
        direction: row;
    }
    
    .point-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }
    
    /* Загрузка карты */
    .map-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
        border-radius: 0.5rem;
        z-index: 1000;
    }
    
    .map-error {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fef2f2;
        border-radius: 0.5rem;
        z-index: 1000;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Хлебные крошки -->
    <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600">
                        <i class="fas fa-home mr-2"></i>Главная
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('routes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2">
                            Маршруты
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $route->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
    
    <!-- Заголовок и действия -->
    <div class="flex flex-col md:flex-row md:items-start justify-between mb-8">
        <div class="mb-6 md:mb-0">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">{{ $route->title }}</h1>
            
            <div class="flex items-center space-x-4 mb-4">
                <!-- Автор -->
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center text-white font-bold text-lg mr-2">
                        {{ substr($route->user->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-medium text-gray-800">{{ $route->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $route->created_at->translatedFormat('d F Y') }}</div>
                    </div>
                </div>
                
                <!-- Статистика -->
                <div class="hidden md:flex items-center space-x-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $route->views_count }}</div>
                        <div class="text-sm text-gray-600">просмотров</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $route->favorites_count }}</div>
                        <div class="text-sm text-gray-600">в избранном</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $route->completions_count }}</div>
                        <div class="text-sm text-gray-600">проехали</div>
                    </div>
                </div>
            </div>
            
            <!-- Теги -->
            <div class="flex flex-wrap gap-2">
                @foreach($route->tags as $tag)
                    <span class="px-3 py-1 rounded-full text-sm font-medium" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>
        </div>
        
        <!-- Кнопки действий -->
        <div class="flex flex-wrap gap-3">
            @auth
                <!-- Сохранить в избранное -->
                <button id="save-route-btn" class="flex items-center px-4 py-2 rounded-lg font-medium transition duration-300 {{ $isSaved ? 'bg-red-100 text-red-800 hover:bg-red-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                    <i class="fas {{ $isSaved ? 'fa-heart' : 'fa-heart' }} mr-2"></i>
                    <span id="save-text">{{ $isSaved ? 'В избранном' : 'В избранное' }}</span>
                    <span id="favorites-count" class="ml-2">{{ $route->favorites_count }}</span>
                </button>
            @endauth
            
            <!-- Экспорт -->
            <a href="{{ route('routes.export.gpx', $route) }}" class="flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg font-medium hover:bg-blue-200 transition duration-300">
                <i class="fas fa-download mr-2"></i> GPX
            </a>
            
            <!-- Поделиться -->
            <button id="share-btn" class="flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg font-medium hover:bg-green-200 transition duration-300">
                <i class="fas fa-share-alt mr-2"></i> Поделиться
            </button>
            
            @can('update', $route)
                <!-- Редактировать -->
                <a href="{{ route('routes.edit', $route) }}" class="flex items-center px-4 py-2 bg-orange-100 text-orange-800 rounded-lg font-medium hover:bg-orange-200 transition duration-300">
                    <i class="fas fa-edit mr-2"></i> Редактировать
                </a>
            @endcan
        </div>
    </div>

    <!-- Основной контент -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Левая колонка -->
        <div class="lg:col-span-2">
            <!-- Обложка -->
            @if($route->cover_image)
                <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
                    <img src="{{ Storage::url($route->cover_image) }}" alt="{{ $route->title }}" class="w-full h-96 object-cover">
                </div>
            @endif
            
            <!-- Карта маршрута -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Маршрут на карте</h2>
                <div id="route-map">
                    <!-- Индикатор загрузки -->
                    <div class="map-loading">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-orange-500 mb-4"></div>
                            <p class="text-gray-600">Загрузка карты...</p>
                        </div>
                    </div>
                    
                    <!-- Сообщение об ошибке (скрыто по умолчанию) -->
                    <div class="map-error hidden">
                        <div class="text-center p-8">
                            <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Не удалось загрузить карту</h3>
                            <p class="text-gray-600 mb-4">Проверьте подключение к интернету и попробуйте снова</p>
                            <button onclick="initializeMap()" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-2 rounded-lg font-medium hover:from-orange-600 hover:to-red-700 transition duration-300">
                                <i class="fas fa-redo mr-2"></i>Попробовать снова
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Описание -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Описание маршрута</h2>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($route->description)) !!}
                </div>
            </div>
            
            <!-- Точки интереса -->
            @if($route->points->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        Точки интереса ({{ $route->points->count() }})
                    </h2>
                    
                    <div class="space-y-6">
                        @foreach($route->points as $point)
                            <div class="border border-gray-200 rounded-lg p-5 hover:border-orange-300 transition duration-300">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" 
                                             style="background-color: {{ $point->type == 'viewpoint' ? '#FEF3C7' : 
                                                                       ($point->type == 'cafe' ? '#FEE2E2' : 
                                                                       ($point->type == 'hotel' ? '#DBEAFE' : 
                                                                       ($point->type == 'attraction' ? '#E0E7FF' : '#F3F4F6'))) }}">
                                            <i class="{{ $point->type_icon }} text-lg" 
                                               style="color: {{ $point->type == 'viewpoint' ? '#F59E0B' : 
                                                              ($point->type == 'cafe' ? '#EF4444' : 
                                                              ($point->type == 'hotel' ? '#3B82F6' : 
                                                              ($point->type == 'attraction' ? '#6366F1' : '#6B7280'))) }}"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-800">{{ $point->title }}</h3>
                                            <div class="flex items-center mt-1">
                                                <span class="point-type-badge mr-3" 
                                                      style="background-color: {{ $point->type == 'viewpoint' ? '#FEF3C7' : 
                                                                                ($point->type == 'cafe' ? '#FEE2E2' : 
                                                                                ($point->type == 'hotel' ? '#DBEAFE' : 
                                                                                ($point->type == 'attraction' ? '#E0E7FF' : '#F3F4F6'))) }};
                                                             color: {{ $point->type == 'viewpoint' ? '#92400E' : 
                                                                      ($point->type == 'cafe' ? '#7F1D1D' : 
                                                                      ($point->type == 'hotel' ? '#1E40AF' : 
                                                                      ($point->type == 'attraction' ? '#3730A3' : '#374151'))) }};">
                                                    <i class="{{ $point->type_icon }} mr-1 text-xs"></i>
                                                    {{ $point->type_label }}
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    {{ number_format($point->lat, 4) }}, {{ number_format($point->lng, 4) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($point->description)
                                    <p class="text-gray-700 mb-4">{{ $point->description }}</p>
                                @endif
                                
                                @php
    // Преобразуем photos в массив если нужно
    $pointPhotos = [];
    if (!empty($point->photos)) {
        if (is_array($point->photos)) {
            $pointPhotos = $point->photos;
        } elseif (is_string($point->photos)) {
            $pointPhotos = json_decode($point->photos, true) ?? [];
        }
    }
@endphp

@if(count($pointPhotos) > 0)
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
    @foreach($pointPhotos as $photo)
        <div class="rounded-lg overflow-hidden">
            <img src="{{ Storage::url($photo) }}" alt="{{ $point->title }}" 
                 class="w-full h-32 object-cover cursor-pointer hover:opacity-90 transition duration-300"
                 onclick="openImageModal('{{ Storage::url($photo) }}')">
        </div>
    @endforeach
</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Отзывы -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Отзывы ({{ $route->reviews_count }})
                </h2>
                
                <!-- Средний рейтинг -->
                <div class="mb-8 p-6 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div class="mb-4 md:mb-0">
                            <div class="text-5xl font-bold text-gray-800 mb-2">{{ $route->average_rating }}</div>
                            <div class="flex items-center mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($route->average_rating) ? 'text-yellow-500' : 'text-gray-300' }} text-xl"></i>
                                @endfor
                                <span class="ml-2 text-gray-600">на основе {{ $route->reviews_count }} отзывов</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Красота</div>
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($averageRatings['scenery'] / 5) * 100 }}%"></div>
                                    </div>
                                    <span class="font-bold">{{ number_format($averageRatings['scenery'], 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Дороги</div>
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($averageRatings['road_quality'] / 5) * 100 }}%"></div>
                                    </div>
                                    <span class="font-bold">{{ number_format($averageRatings['road_quality'], 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Безопасность</div>
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($averageRatings['safety'] / 5) * 100 }}%"></div>
                                    </div>
                                    <span class="font-bold">{{ number_format($averageRatings['safety'], 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Инфраструктура</div>
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ ($averageRatings['infrastructure'] / 5) * 100 }}%"></div>
                                    </div>
                                    <span class="font-bold">{{ number_format($averageRatings['infrastructure'], 1) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Форма отзыва -->
                @auth
                    @if(!$route->reviews->contains('user_id', auth()->id()))
                        <div class="mb-8 p-6 border border-gray-200 rounded-xl">
                            <h3 class="font-bold text-lg text-gray-800 mb-4">Оставить отзыв</h3>
                            <form action="{{ route('reviews.store', $route) }}" method="POST">
                                @csrf
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Красота</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <input type="radio" id="scenery_{{ $i }}" name="scenery_rating" value="{{ $i }}" class="hidden">
                                                <label for="scenery_{{ $i }}" class="cursor-pointer text-2xl">
                                                    <i class="far fa-star text-gray-300 hover:text-yellow-500 rating-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Дороги</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <input type="radio" id="roads_{{ $i }}" name="road_quality_rating" value="{{ $i }}" class="hidden">
                                                <label for="roads_{{ $i }}" class="cursor-pointer text-2xl">
                                                    <i class="far fa-star text-gray-300 hover:text-blue-500 rating-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Безопасность</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <input type="radio" id="safety_{{ $i }}" name="safety_rating" value="{{ $i }}" class="hidden">
                                                <label for="safety_{{ $i }}" class="cursor-pointer text-2xl">
                                                    <i class="far fa-star text-gray-300 hover:text-green-500 rating-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Инфраструктура</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <input type="radio" id="infra_{{ $i }}" name="infrastructure_rating" value="{{ $i }}" class="hidden">
                                                <label for="infra_{{ $i }}" class="cursor-pointer text-2xl">
                                                    <i class="far fa-star text-gray-300 hover:text-orange-500 rating-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
                                    <textarea id="comment" name="comment" rows="4"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                              placeholder="Поделитесь своим опытом прохождения маршрута..."></textarea>
                                </div>
                                
                                <button type="submit" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-6 py-3 rounded-lg font-bold transition duration-300">
                                    Отправить отзыв
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="mb-8 p-6 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl text-center">
                        <p class="text-gray-700 mb-4">Войдите, чтобы оставить отзыв о маршруте</p>
                        <a href="{{ route('login') }}" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
                            Войти
                        </a>
                    </div>
                @endauth
                
                <!-- Список отзывов -->
                @if($route->reviews->count() > 0)
                    <div class="space-y-6">
                        @foreach($route->reviews as $review)
                            <div class="border border-gray-200 rounded-xl p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center text-white font-bold text-lg mr-4">
                                            {{ substr($review->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800">{{ $review->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    
                                    @can('update', $review)
                                        <div class="flex space-x-2">
                                            <button class="text-gray-500 hover:text-orange-600 edit-review" data-id="{{ $review->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-500 hover:text-red-600" onclick="return confirm('Удалить отзыв?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <div class="text-sm text-gray-600 mb-1">Красота</div>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->scenery_rating ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600 mb-1">Дороги</div>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->road_quality_rating ? 'text-blue-500' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600 mb-1">Безопасность</div>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->safety_rating ? 'text-green-500' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600 mb-1">Инфраструктура</div>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->infrastructure_rating ? 'text-orange-500' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                
                                @if($review->comment)
                                    <p class="text-gray-700">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="far fa-comments"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-600 mb-2">Пока нет отзывов</h3>
                        <p class="text-gray-500">Будьте первым, кто поделится впечатлениями об этом маршруте!</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Правая колонка -->
        <div class="space-y-6">
            <!-- Информация о маршруте -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4">Информация о маршруте</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Длина:</span>
                        <span class="font-bold text-gray-800">{{ $route->length_km }} км</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Время в пути:</span>
                        <span class="font-bold text-gray-800">{{ $route->duration_formatted }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Сложность:</span>
                        <span class="font-bold px-3 py-1 rounded-full text-sm {{ $route->difficulty_color }}">
                            {{ $route->difficulty_label }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Тип дороги:</span>
                        <span class="font-bold text-gray-800">{{ $route->road_type_label }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Создан:</span>
                        <span class="font-bold text-gray-800">{{ $route->created_at->translatedFormat('d.m.Y') }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Обновлён:</span>
                        <span class="font-bold text-gray-800">{{ $route->updated_at->translatedFormat('d.m.Y') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Блок запуска маршрута -->
            @php
                // Выносим логику в переменные в начале секции
                $activeSession = null;
                $hasCompleted = false;
                $userActiveQuests = collect();
                
                if (auth()->check()) {
                    $activeSession = \App\Models\RouteSession::where('user_id', auth()->id())
                        ->where('route_id', $route->id)
                        ->whereIn('status', ['active', 'paused'])
                        ->first();
                        
                    $hasCompleted = \App\Models\RouteCompletion::where('user_id', auth()->id())
                        ->where('route_id', $route->id)
                        ->exists();
                    
                    $userActiveQuests = auth()->user()->userQuests()
                        ->where('status', 'in_progress')
                        ->whereHas('quest.routes', function($q) use ($route) {
                            $q->where('travel_routes.id', $route->id);
                        })
                        ->with('quest')
                        ->get();
                }
            @endphp
            
            @auth
                @if($activeSession)
                    <!-- Есть активная сессия -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-100 rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800 mb-1">🚗 Маршрут в процессе</h3>
                                <p class="text-gray-700">Вы проходите этот маршрут</p>
                            </div>
                            <div class="px-3 py-1 bg-blue-500 text-white rounded-full text-sm font-medium">
                                @php
    $progress = 0;
    if ($activeSession->checkpoints_visited && is_array($activeSession->checkpoints_visited)) {
        $total = $activeSession->route->checkpoints->count() ?? $route->checkpoints()->count();
        if ($total > 0) {
            $visited = count($activeSession->checkpoints_visited);
            $progress = min(100, round(($visited / $total) * 100));
        }
    }
@endphp
{{ $progress }}%
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <a href="{{ route('routes.navigate', $route) }}" 
                               class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white text-center py-3 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                                <i class="fas fa-play-circle mr-2"></i> Продолжить навигацию
                            </a>
                            
                            <div class="flex space-x-3">
                                <form action="{{ route('routes.navigation.pause', $activeSession) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg font-medium transition duration-300">
                                        <i class="fas fa-pause mr-2"></i> Пауза
                                    </button>
                                </form>
                                
                                <form action="{{ route('routes.navigation.complete', $activeSession) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Завершить маршрут?')"
                                            class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-medium transition duration-300">
                                        <i class="fas fa-flag-checkered mr-2"></i> Завершить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                @elseif($hasCompleted)
                    <!-- Маршрут уже пройден -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-100 rounded-xl shadow-lg p-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-4">🎉 Маршрут пройден!</h3>
                        <p class="text-gray-700 mb-4">Вы успешно завершили этот маршрут!</p>
                        
                        @if($userActiveQuests->count() > 0)
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Пройти еще раз для квестов:</p>
                                <div class="space-y-2">
                                    @foreach($userActiveQuests as $userQuest)
                                        <div class="bg-white rounded-lg p-3 border border-green-200">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $userQuest->quest->title }}</p>
                                                    <p class="text-xs text-gray-600">
                                                        Прогресс: {{ $userQuest->progress_percentage ?? 0 }}%
                                                    </p>
                                                </div>
                                                <form action="{{ route('routes.navigation.start', $route) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="quest_id" value="{{ $userQuest->quest->id }}">
                                                    <button type="submit" 
                                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                                        <i class="fas fa-redo mr-1"></i> Пройти
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <form action="{{ route('routes.navigation.start', $route) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white text-center py-3 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                                <i class="fas fa-play mr-2"></i> Пройти еще раз
                            </button>
                        </form>
                    </div>
                    
                @else
                    <!-- Маршрут еще не проходился -->
                    <div class="bg-gradient-to-r from-orange-50 to-red-100 rounded-xl shadow-lg p-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-4">🚀 Начать путешествие</h3>
                        <p class="text-gray-700 mb-4">Запустите навигатор и отправляйтесь в путь!</p>
                        
                        @if($userActiveQuests->count() > 0)
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Начать маршрут для квестов:</p>
                                <div class="space-y-2">
                                    @foreach($userActiveQuests as $userQuest)
                                        <div class="bg-white rounded-lg p-3 border border-orange-200">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $userQuest->quest->title }}</p>
                                                    <p class="text-xs text-gray-600">
                                                        +{{ $userQuest->quest->reward_xp }} XP • 
                                                        {{ $userQuest->quest->routes->count() }} маршрутов
                                                    </p>
                                                </div>
                                                <form action="{{ route('routes.navigation.start', $route) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="quest_id" value="{{ $userQuest->quest->id }}">
                                                    <button type="submit" 
                                                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                                        <i class="fas fa-play mr-1"></i> Для квеста
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <form action="{{ route('routes.navigation.start', $route) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="block w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-center py-3 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                                <i class="fas fa-play-circle mr-2"></i> Запустить навигатор
                            </button>
                        </form>
                        
                        <div class="mt-4 p-3 bg-white rounded-lg border border-orange-200">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                                <p class="text-sm text-gray-700">
                                    Навигатор поможет вам следовать по маршруту, отмечать точки интереса и выполнять задания квестов
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
            @else
                <!-- Для неавторизованных -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Хотите отправиться в путь?</h3>
                    <p class="text-gray-700 mb-4">Войдите, чтобы запустить навигатор по маршруту!</p>
                    <a href="{{ route('login') }}" 
                       class="block w-full bg-gradient-to-r from-orange-500 to-red-600 text-white text-center py-3 rounded-lg font-bold text-lg hover:from-orange-600 hover:to-red-700 transition duration-300">
                        Войти и начать путешествие
                    </a>
                </div>
            @endauth
            
            <!-- Похожие маршруты -->
            @if($similarRoutes->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Похожие маршруты</h3>
                    
                    <div class="space-y-4">
                        @foreach($similarRoutes as $similar)
                            <a href="{{ route('routes.show', $similar) }}" class="block group">
                                <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition duration-300">
                                    @if($similar->cover_image)
                                        <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                                            <img src="{{ Storage::url($similar->cover_image) }}" alt="{{ $similar->title }}" 
                                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-800 truncate group-hover:text-orange-600">{{ $similar->title }}</h4>
                                        <div class="flex items-center mt-1">
                                            <span class="px-2 py-1 text-xs rounded {{ $similar->difficulty_color }} mr-2">
                                                {{ $similar->difficulty_label }}
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $similar->length_km }} км</span>
                                        </div>
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
                    <a href="{{ route('routes.export.gpx', $route) }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-download text-blue-600"></i>
                            </div>
                            <span class="font-medium text-gray-800">Скачать GPX</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                    
                    <button id="copy-link" class="w-full flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-link text-green-600"></i>
                            </div>
                            <span class="font-medium text-gray-800">Копировать ссылку</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </button>
                    
                    <a href="{{ route('routes.create') }}" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                                <i class="fas fa-plus text-purple-600"></i>
                            </div>
                            <span class="font-medium text-gray-800">Создать свой маршрут</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для изображений -->
<div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">
    <div class="relative max-w-4xl max-h-full">
        <button id="close-modal" class="absolute top-4 right-4 text-white text-3xl z-10">
            <i class="fas fa-times"></i>
        </button>
        <img id="modal-image" class="max-w-full max-h-screen" src="">
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
// Глобальная переменная для карты
let routeMap = null;

// Основная функция инициализации карты
function initializeMap() {
    console.log('🚀 Инициализация карты маршрута...');
    
    // Скрываем сообщение об ошибке если оно было показано
    const errorElement = document.querySelector('.map-error');
    if (errorElement) {
        errorElement.classList.add('hidden');
    }
    
    // Проверяем наличие Leaflet
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet не загружен!');
        showMapError('Библиотека карт не загружена');
        return;
    }
    
    const mapElement = document.getElementById('route-map');
    if (!mapElement) {
        console.error('❌ Элемент карты не найден');
        showMapError('Элемент карты не найден');
        return;
    }
    
    try {
        // 1. ПАРСИМ КООРДИНАТЫ ИЗ JSON
        // В базе данные хранятся как JSON строки, нужно их распарсить
        let startCoords, endCoords, pathCoords;
        
        try {
            // Стартовые координаты
            startCoords = JSON.parse('{!! addslashes($route->start_coordinates) !!}');
            if (!Array.isArray(startCoords) || startCoords.length < 2) {
                startCoords = [55.7558, 37.6173]; // Москва по умолчанию
            }
        } catch (e) {
            console.warn('⚠️ Ошибка парсинга start_coordinates:', e);
            startCoords = [55.7558, 37.6173];
        }
        
        try {
            // Конечные координаты
            endCoords = JSON.parse('{!! addslashes($route->end_coordinates) !!}');
        } catch (e) {
            console.warn('⚠️ Ошибка парсинга end_coordinates:', e);
            endCoords = null;
        }
        
        try {
            // Координаты пути
            pathCoords = JSON.parse('{!! addslashes($route->path_coordinates) !!}');
            if (!Array.isArray(pathCoords)) {
                pathCoords = [];
            }
        } catch (e) {
            console.warn('⚠️ Ошибка парсинга path_coordinates:', e);
            pathCoords = [];
        }
        
        console.log('📍 Координаты:', {
            start: startCoords,
            end: endCoords,
            path: pathCoords
        });
        
        const startLat = parseFloat(startCoords[0]);
        const startLng = parseFloat(startCoords[1]);
        
        if (isNaN(startLat) || isNaN(startLng)) {
            console.error('❌ Невалидные координаты маршрута');
            showMapError('Неверные координаты маршрута');
            return;
        }
        
        // 2. УДАЛЯЕМ СТАРУЮ КАРТУ ЕСЛИ ОНА СУЩЕСТВУЕТ
        if (routeMap) {
            routeMap.remove();
            routeMap = null;
        }
        
        // 3. СОЗДАЕМ НОВУЮ КАРТУ
        routeMap = L.map('route-map').setView([startLat, startLng], 10);
        console.log('🗺️ Карта создана');
        
        // 4. ДОБАВЛЯЕМ БАЗОВЫЙ СЛОЙ OPENSTREETMAP
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
            minZoom: 3
        }).addTo(routeMap);
        console.log('🖼️ Тайлы добавлены');
        
        // 5. ДОБАВЛЯЕМ МАРКЕР СТАРТА
        const startMarker = L.marker([startLat, startLng]).addTo(routeMap);
        startMarker.bindPopup(`
            <div class="p-2">
                <div class="font-bold text-gray-800 mb-1">📍 Старт маршрута</div>
                <div class="text-sm text-gray-600">{{ $route->title }}</div>
            </div>
        `);
        console.log('📍 Маркер старта добавлен');
        
        // 6. ДОБАВЛЯЕМ МАРКЕР ФИНИША ЕСЛИ ЕСТЬ
        if (endCoords && Array.isArray(endCoords) && endCoords.length >= 2) {
            const endLat = parseFloat(endCoords[0]);
            const endLng = parseFloat(endCoords[1]);
            
            if (!isNaN(endLat) && !isNaN(endLng)) {
                const endMarker = L.marker([endLat, endLng]).addTo(routeMap);
                endMarker.bindPopup(`
                    <div class="p-2">
                        <div class="font-bold text-gray-800 mb-1">🏁 Финиш маршрута</div>
                        <div class="text-sm text-gray-600">{{ $route->title }}</div>
                    </div>
                `);
                console.log('🏁 Маркер финиша добавлен');
            }
        }
        
        // 7. ДОБАВЛЯЕМ МАРШРУТ ЕСЛИ ЕСТЬ КООРДИНАТЫ ПУТИ
        if (pathCoords && pathCoords.length > 0) {
            console.log('🛣️ Координаты пути:', pathCoords.length, 'точек');
            
            try {
                // Фильтруем валидные координаты
                const validCoords = pathCoords.filter(coord => 
                    Array.isArray(coord) && 
                    coord.length >= 2 && 
                    !isNaN(parseFloat(coord[0])) && 
                    !isNaN(parseFloat(coord[1]))
                );
                
                if (validCoords.length > 1) {
                    // Создаем линию маршрута
                    const routeLine = L.polyline(validCoords, {
                        color: '#f97316',
                        weight: 4,
                        opacity: 0.8,
                        smoothFactor: 1,
                        lineCap: 'round'
                    }).addTo(routeMap);
                    
                    console.log('🛣️ Линия маршрута добавлена:', validCoords.length, 'точек');
                    
                    // Фокусируем карту на маршруте
                    routeMap.fitBounds(routeLine.getBounds());
                    console.log('🎯 Карта сфокусирована на маршруте');
                } else {
                    console.warn('⚠️ Недостаточно валидных координат для отрисовки маршрута');
                }
            } catch (e) {
                console.warn('⚠️ Не удалось добавить маршрут:', e);
            }
        } else {
            console.warn('⚠️ Координаты пути отсутствуют или пусты');
        }
        
        // 8. ДОБАВЛЯЕМ ТОЧКИ ИНТЕРЕСА
        const pointsData = @json($route->points);
        console.log('📍 Точки интереса:', pointsData);
        
        if (pointsData && pointsData.length > 0) {
            let pointsAdded = 0;
            
            pointsData.forEach((point, index) => {
                try {
                    if (point.lat && point.lng) {
                        const lat = parseFloat(point.lat);
                        const lng = parseFloat(point.lng);
                        
                        if (!isNaN(lat) && !isNaN(lng)) {
                            // Определяем цвет и иконку для типа точки
                            let pointColor, pointIcon;
                            
                            switch(point.type) {
                                case 'viewpoint':
                                    pointColor = '#F59E0B';
                                    pointIcon = 'fas fa-binoculars';
                                    break;
                                case 'cafe':
                                    pointColor = '#EF4444';
                                    pointIcon = 'fas fa-utensils';
                                    break;
                                case 'hotel':
                                    pointColor = '#3B82F6';
                                    pointIcon = 'fas fa-bed';
                                    break;
                                case 'attraction':
                                    pointColor = '#6366F1';
                                    pointIcon = 'fas fa-landmark';
                                    break;
                                case 'gas_station':
                                    pointColor = '#10B981';
                                    pointIcon = 'fas fa-gas-pump';
                                    break;
                                case 'camping':
                                    pointColor = '#8B5CF6';
                                    pointIcon = 'fas fa-campground';
                                    break;
                                case 'photo_spot':
                                    pointColor = '#EC4899';
                                    pointIcon = 'fas fa-camera';
                                    break;
                                case 'nature':
                                    pointColor = '#22C55E';
                                    pointIcon = 'fas fa-tree';
                                    break;
                                case 'historical':
                                    pointColor = '#A855F7';
                                    pointIcon = 'fas fa-monument';
                                    break;
                                default:
                                    pointColor = '#6B7280';
                                    pointIcon = 'fas fa-map-marker-alt';
                            }
                            
                            // Создаем кастомную иконку
                            const customIcon = L.divIcon({
                                html: `
                                    <div style="
                                        width: 36px;
                                        height: 36px;
                                        background-color: ${pointColor};
                                        border-radius: 50%;
                                        border: 3px solid white;
                                        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        color: white;
                                        font-size: 14px;
                                    ">
                                        <i class="${pointIcon}"></i>
                                    </div>
                                `,
                                className: 'custom-marker',
                                iconSize: [36, 36],
                                iconAnchor: [18, 36]
                            });
                            
                            // Создаем маркер
                            const pointMarker = L.marker([lat, lng], {
                                icon: customIcon
                            }).addTo(routeMap);
                            
                            // Создаем содержимое для всплывающего окна
                            let popupContent = `
                                <div class="p-3 max-w-xs">
                                    <div class="flex items-start mb-2">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" 
                                             style="background-color: ${pointColor}20; color: ${pointColor};">
                                            <i class="${pointIcon}"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800">${point.title || 'Точка интереса'}</div>
                                            <div class="text-sm text-gray-600 mt-1">${getTypeLabel(point.type)}</div>
                                        </div>
                                    </div>`;
                            
                            if (point.description) {
                                popupContent += `<div class="text-gray-700 text-sm mt-2">${point.description}</div>`;
                            }
                            
                            popupContent += `</div>`;
                            
                            // Добавляем всплывающее окно
                            pointMarker.bindPopup(popupContent);
                            
                            pointsAdded++;
                        }
                    }
                } catch (pointError) {
                    console.warn(`⚠️ Ошибка при добавлении точки ${index}:`, pointError);
                }
            });
            
            console.log(`📍 Добавлено точек интереса: ${pointsAdded} из ${pointsData.length}`);
        }
        
        // 9. ДОБАВЛЯЕМ ЭЛЕМЕНТЫ УПРАВЛЕНИЯ
        L.control.zoom({
            position: 'topright'
        }).addTo(routeMap);
        
        L.control.scale({
            position: 'bottomleft',
            imperial: false
        }).addTo(routeMap);
        
        // 10. СКРЫВАЕМ ИНДИКАТОР ЗАГРУЗКИ
        setTimeout(() => {
            const loadingElement = document.querySelector('.map-loading');
            if (loadingElement) {
                loadingElement.style.display = 'none';
                console.log('✅ Индикатор загрузки скрыт');
            }
            
            // Обновляем размер карты
            if (routeMap) {
                routeMap.invalidateSize();
                console.log('📏 Размер карты обновлен');
            }
        }, 100);
        
        // 11. ОБРАБОТЧИК ИЗМЕНЕНИЯ РАЗМЕРА ОКНА
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (routeMap) {
                    routeMap.invalidateSize();
                }
            }, 250);
        });
        
        // 12. СОХРАНЯЕМ КАРТУ В ГЛОБАЛЬНОЙ ПЕРЕМЕННОЙ
        window.routeMap = routeMap;
        
        console.log('✅ Карта успешно инициализирована!');
        
    } catch (error) {
        console.error('❌ Критическая ошибка при создании карты:', error, error.stack);
        showMapError('Ошибка создания карты: ' + error.message);
    }
}

// Вспомогательная функция для получения названия типа точки
function getTypeLabel(type) {
    const labels = {
        'viewpoint': 'Смотровая площадка',
        'cafe': 'Кафе',
        'hotel': 'Отель',
        'attraction': 'Достопримечательность',
        'gas_station': 'Заправка',
        'camping': 'Кемпинг',
        'photo_spot': 'Фото-спот',
        'nature': 'Природа',
        'historical': 'Историческое место',
        'other': 'Точка интереса'
    };
    return labels[type] || 'Точка интереса';
}

// Функция показа ошибки карты
function showMapError(message = 'Не удалось загрузить карту') {
    console.error('❌ Показываем ошибку карты:', message);
    
    const loadingElement = document.querySelector('.map-loading');
    const errorElement = document.querySelector('.map-error');
    
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    
    if (errorElement) {
        // Обновляем текст ошибки
        const errorText = errorElement.querySelector('h3');
        if (errorText) {
            errorText.textContent = message;
        }
        
        errorElement.classList.remove('hidden');
        errorElement.style.display = 'flex';
    }
}

// Функция перезагрузки карты
function reloadMap() {
    console.log('🔄 Перезагрузка карты...');
    const loadingElement = document.querySelector('.map-loading');
    const errorElement = document.querySelector('.map-error');
    
    if (loadingElement) {
        loadingElement.style.display = 'flex';
    }
    
    if (errorElement) {
        errorElement.classList.add('hidden');
    }
    
    // Даем время на скрытие ошибки
    setTimeout(initializeMap, 300);
}

// =============== ОСТАЛЬНЫЕ ФУНКЦИИ СТРАНИЦЫ ===============

// Открытие модального окна с изображением
function openImageModal(src) {
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    
    if (modal && modalImage) {
        modalImage.src = src;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM загружен, инициализируем карту...');
    
    // Инициализируем карту с задержкой для загрузки Leaflet
    setTimeout(initializeMap, 800);
    
    // =============== НАСТРОЙКА МОДАЛЬНОГО ОКНА ===============
    const modal = document.getElementById('image-modal');
    const closeBtn = document.getElementById('close-modal');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
    
    // =============== КНОПКА "СОХРАНИТЬ В ИЗБРАННОЕ" ===============
    const saveBtn = document.getElementById('save-route-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            try {
                console.log('💾 Сохранение маршрута...');
                const response = await fetch('{{ route("routes.save", $route) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const saveText = document.getElementById('save-text');
                    const favoritesCount = document.getElementById('favorites-count');
                    
                    if (data.saved) {
                        // Маршрут сохранен
                        saveBtn.classList.remove('bg-gray-100', 'text-gray-800');
                        saveBtn.classList.add('bg-red-100', 'text-red-800');
                        saveBtn.querySelector('i').className = 'fas fa-heart mr-2';
                        if (saveText) saveText.textContent = 'В избранном';
                        console.log('❤️ Маршрут добавлен в избранное');
                    } else {
                        // Маршрут удален из избранного
                        saveBtn.classList.remove('bg-red-100', 'text-red-800');
                        saveBtn.classList.add('bg-gray-100', 'text-gray-800');
                        saveBtn.querySelector('i').className = 'far fa-heart mr-2';
                        if (saveText) saveText.textContent = 'В избранное';
                        console.log('💔 Маршрут удален из избранного');
                    }
                    
                    if (favoritesCount) {
                        favoritesCount.textContent = data.favorites_count || data.count || 0;
                    }
                } else {
                    console.error('❌ Ошибка при сохранении:', response.status);
                    alert('Ошибка при сохранении маршрута');
                }
            } catch (error) {
                console.error('❌ Ошибка:', error);
                alert('Ошибка при сохранении маршрута. Проверьте подключение.');
            }
        });
    }
    
    // =============== КНОПКА "ПОДЕЛИТЬСЯ" ===============
    const shareBtn = document.getElementById('share-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            const shareUrl = window.location.href;
            const shareTitle = '{{ $route->title }}';
            const shareText = 'Посмотрите этот маршрут на AutoRuta!';
            
            if (navigator.share) {
                // Используем Web Share API если доступен
                navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: shareUrl
                }).then(() => {
                    console.log('✅ Успешно поделились');
                }).catch(err => {
                    console.warn('⚠️ Ошибка sharing:', err);
                    copyToClipboard(shareUrl);
                });
            } else {
                // Или копируем в буфер обмена
                copyToClipboard(shareUrl);
            }
        });
    }
    
    // =============== КНОПКА "КОПИРОВАТЬ ССЫЛКУ" ===============
    const copyLinkBtn = document.getElementById('copy-link');
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', function() {
            copyToClipboard(window.location.href);
        });
    }
    
    // =============== РЕЙТИНГ ЗВЕЗДОЧЕК ===============
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input && input.type === 'radio') {
                input.checked = true;
                
                // Обновляем отображение звезд
                const stars = this.parentNode.querySelectorAll('.rating-star');
                const rating = parseInt(input.value);
                
                stars.forEach((s, index) => {
                    const icon = s.querySelector('i');
                    if (index < rating) {
                        icon.className = 'fas fa-star';
                    } else {
                        icon.className = 'far fa-star';
                    }
                });
            }
        });
    });
    
    // =============== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===============
    
    // Функция копирования в буфер обмена
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Ссылка скопирована в буфер обмена!');
            console.log('📋 Ссылка скопирована');
        }).catch(err => {
            console.error('❌ Ошибка копирования:', err);
            showNotification('Не удалось скопировать ссылку', 'error');
        });
    }
    
    // Функция показа уведомления
    function showNotification(message, type = 'success') {
        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg font-medium transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        // Добавляем на страницу
        document.body.appendChild(notification);
        
        // Показываем с анимацией
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 10);
        
        // Удаляем через 3 секунды
        setTimeout(() => {
            notification.classList.remove('translate-x-0');
            notification.classList.add('translate-x-full');
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    // =============== ДЛЯ ОТЛАДКИ ===============
    // Выводим данные маршрута в консоль
    console.log('📊 Данные маршрута:', {
        title: '{{ $route->title }}',
        start_coordinates: '{!! $route->start_coordinates !!}',
        end_coordinates: '{!! $route->end_coordinates !!}',
        path_coordinates: '{!! $route->path_coordinates !!}',
        points_count: {{ $route->points ? $route->points->count() : 0 }}
    });
});

// Экспортируем функции для глобального использования
window.initializeMap = initializeMap;
window.reloadMap = reloadMap;
window.openImageModal = openImageModal;
</script>
@endpush