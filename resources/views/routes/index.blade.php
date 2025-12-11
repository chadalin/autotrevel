@extends('layouts.app')

@section('title', 'Поиск маршрутов - AutoRuta')

@section('content')
<div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold mb-4">Поиск маршрутов</h1>
        <p class="text-xl text-gray-300">Найдите идеальный маршрут для вашего следующего путешествия</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Сайдбар с фильтрами -->
        <div class="lg:w-1/4">
            <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Фильтры</h2>
                
                <!-- Поиск -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Название или описание..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Сложность -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сложность</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="difficulty" value="easy" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Лёгкий</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="difficulty" value="medium" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Средний</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="difficulty" value="hard" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Сложный</span>
                        </label>
                    </div>
                </div>
                
                <!-- Тип дороги -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Тип дороги</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="road_type" value="asphalt" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Асфальт</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="road_type" value="gravel" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Гравий</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="road_type" value="offroad" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Бездорожье</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="road_type" value="mixed" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-gray-700">Смешанный</span>
                        </label>
                    </div>
                </div>
                
                <!-- Теги -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Теги</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="tags" value="{{ $tag->id }}" 
                                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                <span class="ml-1 text-sm px-2 py-1 rounded-full" 
                                      style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                    #{{ $tag->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                <!-- Кнопка применения фильтров -->
                <button id="apply-filters" class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white py-3 rounded-lg font-medium transition duration-300">
                    Применить фильтры
                </button>
                
                <!-- Кнопка сброса -->
                <button id="reset-filters" class="w-full mt-3 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 rounded-lg font-medium transition duration-300">
                    Сбросить фильтры
                </button>
            </div>
        </div>
        
        <!-- Основной контент -->
        <div class="lg:w-3/4">
            <!-- Сортировка -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-xl font-bold text-gray-800">
                            Найдено {{ $routes->total() }} маршрутов
                        </h2>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">Сортировка:</span>
                        <select id="sort-select" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="new" {{ $sort == 'new' ? 'selected' : '' }}>Сначала новые</option>
                            <option value="popular" {{ $sort == 'popular' ? 'selected' : '' }}>По популярности</option>
                            <option value="rating" {{ $sort == 'rating' ? 'selected' : '' }}>По рейтингу</option>
                            <option value="length_asc" {{ $sort == 'length_asc' ? 'selected' : '' }}>Короткие</option>
                            <option value="length_desc" {{ $sort == 'length_desc' ? 'selected' : '' }}>Длинные</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Список маршрутов -->
            <div id="routes-container">
                @if($routes->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($routes as $route)
                            @include('partials.route-card', ['route' => $route])
                        @endforeach
                    </div>
                    
                    <!-- Пагинация -->
                    <div class="mt-8">
                        {{ $routes->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-route"></i>
                        </div>
                        <h3 class="text-2xl font-medium text-gray-600 mb-2">Маршруты не найдены</h3>
                        <p class="text-gray-500 mb-6">Попробуйте изменить параметры фильтрации</p>
                        <a href="{{ route('routes.create') }}" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-lg font-medium inline-block">
                            <i class="fas fa-plus-circle mr-2"></i> Создать первый маршрут
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Применение фильтров
    $('#apply-filters').on('click', function() {
        loadRoutes();
    });
    
    // Сброс фильтров
    $('#reset-filters').on('click', function() {
        $('input[type="checkbox"]').prop('checked', false);
        $('#search-input').val('');
        loadRoutes();
    });
    
    // Сортировка
    $('#sort-select').on('change', function() {
        loadRoutes();
    });
    
    // Поиск при вводе (с задержкой)
    let searchTimeout;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadRoutes, 500);
    });
    
    function loadRoutes() {
        const params = {
            search: $('#search-input').val(),
            difficulty: getCheckedValues('difficulty'),
            road_type: getCheckedValues('road_type'),
            tags: getCheckedValues('tags'),
            sort: $('#sort-select').val(),
        };
        
        $.ajax({
            url: "{{ route('search') }}",
            method: 'GET',
            data: params,
            success: function(response) {
                $('#routes-container').html(response.html);
                updateRouteCount(response.routes.total);
            },
            error: function() {
                showNotification('Ошибка при загрузке маршрутов', 'error');
            }
        });
    }
    
    function getCheckedValues(name) {
        const values = [];
        $(`input[name="${name}"]:checked`).each(function() {
            values.push($(this).val());
        });
        return values.length > 0 ? values : null;
    }
    
    function updateRouteCount(count) {
        $('h2.text-xl').text('Найдено ' + count + ' маршрутов');
    }
    
    function showNotification(message, type = 'info') {
        // Реализация уведомления
    }
});
</script>
@endpush