@extends('layouts.app')

@section('title', $route->title . ' - AutoRuta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #route-map {
        height: 500px;
        border-radius: 0.5rem;
    }
    .leaflet-popup-content {
        font-family: 'Open Sans', sans-serif;
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
                <div id="route-map"></div>
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
                                
                                @if($point->photos && count($point->photos) > 0)
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                        @foreach($point->photos as $photo)
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let routeMap