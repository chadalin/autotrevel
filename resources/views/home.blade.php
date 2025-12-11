@extends('layouts.app')

@section('title', 'AutoRuta - Маршруты для автопутешествий')

@section('content')
    <!-- Hero секция -->
    <div class="relative bg-gradient-to-r from-gray-900 to-gray-800 text-white overflow-hidden">
        <div class="container mx-auto px-4 py-16 md:py-24">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Откройте Россию <br>
                        <span class="text-orange-400">за рулём</span>
                    </h1>
                    <p class="text-xl text-gray-300 mb-8">
                        Самые живописные маршруты для автопутешествий. 
                        Делитесь своими открытиями, участвуйте в квестах, 
                        находите новые места для поездок на выходные.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('search') }}" 
                           class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl flex items-center justify-center">
                            <i class="fas fa-search mr-3"></i> Найти маршрут
                        </a>
                        <a href="#" 
                           class="bg-transparent border-2 border-orange-500 text-orange-400 hover:bg-orange-500 hover:text-white px-8 py-4 rounded-lg font-bold text-lg transition duration-300 flex items-center justify-center">
                            <i class="fas fa-plus-circle mr-3"></i> Создать маршрут
                        </a>
                    </div>
                </div>
                
                <div class="md:w-1/2 relative">
                    <div class="relative">
                        <div class="absolute -top-6 -left-6 w-64 h-64 bg-gradient-to-r from-orange-400 to-red-500 rounded-full opacity-20 blur-3xl"></div>
                        <div class="absolute -bottom-6 -right-6 w-64 h-64 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full opacity-20 blur-3xl"></div>
                        
                        <!-- Статистика -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 shadow-2xl">
                            <div class="grid grid-cols-3 gap-6 text-center">
                                <div>
                                    <div class="text-3xl font-bold">{{ \App\Models\Route::published()->count() }}+</div>
                                    <div class="text-gray-300 text-sm">Маршрутов</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold">{{ \App\Models\User::count() }}+</div>
                                    <div class="text-gray-300 text-sm">Путешественников</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold">24/7</div>
                                    <div class="text-gray-300 text-sm">Поддержка</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Карта маршрутов -->
    <div class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-map-marked-alt text-orange-500 mr-3"></i>
                Маршруты на карте
            </h2>
            <a href="{{ route('search') }}" class="text-orange-500 hover:text-orange-600 font-medium">
                Все маршруты <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div id="map" class="h-96 w-full rounded-lg"></div>
        </div>
    </div>

    <!-- Избранные маршруты -->
    <div class="bg-gradient-to-br from-gray-50 to-blue-50 py-12">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-crown text-yellow-500 mr-3"></i>
                    Избранные маршруты
                </h2>
                <a href="{{ route('search') }}?sort=rating" class="text-orange-500 hover:text-orange-600 font-medium">
                    Все избранные <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            @if($featuredRoutes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($featuredRoutes as $route)
                        @include('partials.route-card', ['route' => $route])
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-600 mb-2">Пока нет избранных маршрутов</h3>
                    <p class="text-gray-500 mb-6">Будьте первым, кто создаст популярный маршрут!</p>
                    <a href="#" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-lg font-medium inline-block">
                        <i class="fas fa-plus-circle mr-2"></i> Создать маршрут
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Популярные теги -->
    <div class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
            <i class="fas fa-tags text-orange-500 mr-3"></i>
            Популярные категории
        </h2>
        
        <div class="flex flex-wrap justify-center gap-4 mb-12">
            @foreach($tags as $tag)
                <a href="{{ route('search') }}?tags[]={{ $tag->id }}" 
                   class="px-6 py-3 rounded-full font-medium transition duration-300 shadow-md hover:shadow-lg"
                   style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 2px solid {{ $tag->color }}40;">
                    <i class="fas fa-hashtag mr-2"></i>{{ $tag->name }}
                    <span class="ml-2 bg-white/50 px-2 py-1 rounded-full text-sm">{{ $tag->routes_count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Новые маршруты -->
    <div class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-bolt text-green-500 mr-3"></i>
                Новые маршруты
            </h2>
            <a href="{{ route('search') }}?sort=new" class="text-orange-500 hover:text-orange-600 font-medium">
                Все новые <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        @if($newRoutes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($newRoutes as $route)
                    @include('partials.route-card-small', ['route' => $route])
                @endforeach
            </div>
        @endif
    </div>

    <!-- Популярные маршруты -->
    <div class="bg-gradient-to-br from-orange-50 to-red-50 py-12">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-fire text-red-500 mr-3"></i>
                    Популярные сейчас
                </h2>
                <a href="{{ route('search') }}?sort=popular" class="text-orange-500 hover:text-orange-600 font-medium">
                    Все популярные <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            @if($popularRoutes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($popularRoutes->take(4) as $route)
                        @include('partials.route-card-featured', ['route' => $route])
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- CTA секция -->
    <div class="container mx-auto px-4 py-16">
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-8 md:p-12 text-center text-white">
            <div class="max-w-2xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    Готовы к новым приключениям?
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    Присоединяйтесь к сообществу автопутешественников. 
                    Делитесь своими открытиями, участвуйте в квестах, 
                    находите единомышленников для совместных поездок.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="#" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition duration-300 shadow-lg">
                            <i class="fas fa-plus-circle mr-3"></i> Создать маршрут
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition duration-300 shadow-lg">
                            <i class="fas fa-user-plus mr-3"></i> Присоединиться
                        </a>
                    @endauth
                    <a href="{{ route('search') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-4 rounded-lg font-bold text-lg transition duration-300">
                        <i class="fas fa-search mr-3"></i> Исследовать
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Инициализация карты
    let map;
    let routeMarkers = [];
    let routeLines = [];

    function initMap() {
        // Центр России
        map = L.map('map').setView([55.7558, 37.6173], 4);
        
        // OpenStreetMap слой
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18,
        }).addTo(map);

        // Загружаем данные маршрутов
        loadRoutes();
    }

    function loadRoutes() {
        $.getJSON("{{ route('map.data') }}", function(data) {
            // Удаляем старые маркеры и линии
            routeMarkers.forEach(marker => map.removeLayer(marker));
            routeLines.forEach(line => map.removeLayer(line));
            routeMarkers = [];
            routeLines = [];

            // Добавляем маркеры и линии для каждого маршрута
            data.forEach(route => {
                if (route.start && route.end) {
                    // Маркер начала
                    const startIcon = L.divIcon({
                        html: `<div class="w-8 h-8 rounded-full bg-green-500 border-2 border-white shadow-lg flex items-center justify-center">
                                 <i class="fas fa-play text-white text-xs"></i>
                               </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                        className: 'route-start-marker'
                    });

                    const startMarker = L.marker(route.start, { icon: startIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div class="p-2">
                                <h4 class="font-bold text-gray-800">${route.title}</h4>
                                <div class="flex items-center mt-1">
                                    <span class="px-2 py-1 text-xs rounded ${route.difficulty_color}">${route.difficulty}</span>
                                    <span class="ml-2 text-sm text-gray-600">${route.length} км</span>
                                </div>
                                <a href="${route.url}" class="block mt-2 text-orange-500 hover:text-orange-600 text-sm font-medium">
                                    Подробнее <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        `);

                    routeMarkers.push(startMarker);

                    // Маркер конца
                    const endIcon = L.divIcon({
                        html: `<div class="w-8 h-8 rounded-full bg-red-500 border-2 border-white shadow-lg flex items-center justify-center">
                                 <i class="fas fa-flag-checkered text-white text-xs"></i>
                               </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                        className: 'route-end-marker'
                    });

                    const endMarker = L.marker(route.end, { icon: endIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div class="p-2">
                                <h4 class="font-bold text-gray-800">${route.title}</h4>
                                <div class="flex items-center mt-1">
                                    <span class="px-2 py-1 text-xs rounded ${route.difficulty_color}">${route.difficulty}</span>
                                    <span class="ml-2 text-sm text-gray-600">${route.length} км</span>
                                </div>
                                <a href="${route.url}" class="block mt-2 text-orange-500 hover:text-orange-600 text-sm font-medium">
                                    Подробнее <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        `);

                    routeMarkers.push(endMarker);

                    // Линия маршрута
                    if (route.path && route.path.length > 0) {
                        const lineColor = route.difficulty === 'easy' ? '#10B981' : 
                                        route.difficulty === 'medium' ? '#F59E0B' : '#EF4444';
                        
                        const routeLine = L.polyline(route.path, {
                            color: lineColor,
                            weight: 4,
                            opacity: 0.7,
                            dashArray: '5, 10'
                        }).addTo(map);

                        routeLines.push(routeLine);
                    }
                }
            });

            // Если есть маршруты, подгоняем карту чтобы показать все
            if (data.length > 0) {
                const group = new L.featureGroup(routeMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        });
    }

    // Инициализация карты при загрузке страницы
    $(document).ready(function() {
        initMap();
        
        // Обновляем карту при изменении размера окна
        $(window).on('resize', function() {
            setTimeout(() => map.invalidateSize(), 200);
        });
    });
</script>
@endpush