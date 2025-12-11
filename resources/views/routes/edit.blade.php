@extends('layouts.app')

@section('title', 'Редактирование маршрута - AutoRuta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-edit {
        height: 500px;
        border-radius: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
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
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400"></i>
                            <a href="{{ route('routes.show', $route) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2">
                                {{ Str::limit($route->title, 30) }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Редактирование</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Редактирование маршрута</h1>
        <p class="text-gray-600 mb-8">Внесите изменения в маршрут "{{ $route->title }}"</p>
        
        <form id="edit-route-form" action="{{ route('routes.update', $route) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Основная информация</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Название -->
                    <div class="col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Название маршрута *
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title', $route->title) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <!-- Описание -->
                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Подробное описание *
                        </label>
                        <textarea id="description" name="description" rows="6" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">{{ old('description', $route->description) }}</textarea>
                    </div>
                    
                    <!-- Длина и время -->
                    <div>
                        <label for="length_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Длина маршрута (км) *
                        </label>
                        <input type="number" id="length_km" name="length_km" value="{{ old('length_km', $route->length_km) }}" required min="1" step="0.1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Время в пути (минуты) *
                        </label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $route->duration_minutes) }}" required min="10"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <!-- Сложность -->
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                            Сложность *
                        </label>
                        <select id="difficulty" name="difficulty" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="easy" {{ old('difficulty', $route->difficulty) == 'easy' ? 'selected' : '' }}>Лёгкий (для новичков)</option>
                            <option value="medium" {{ old('difficulty', $route->difficulty) == 'medium' ? 'selected' : '' }}>Средний (требуется опыт)</option>
                            <option value="hard" {{ old('difficulty', $route->difficulty) == 'hard' ? 'selected' : '' }}>Сложный (для опытных водителей)</option>
                        </select>
                    </div>
                    
                    <!-- Тип дороги -->
                    <div>
                        <label for="road_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Тип дороги *
                        </label>
                        <select id="road_type" name="road_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="asphalt" {{ old('road_type', $route->road_type) == 'asphalt' ? 'selected' : '' }}>Асфальт</option>
                            <option value="gravel" {{ old('road_type', $route->road_type) == 'gravel' ? 'selected' : '' }}>Гравий</option>
                            <option value="offroad" {{ old('road_type', $route->road_type) == 'offroad' ? 'selected' : '' }}>Бездорожье</option>
                            <option value="mixed" {{ old('road_type', $route->road_type) == 'mixed' ? 'selected' : '' }}>Смешанный</option>
                        </select>
                    </div>
                    
                    <!-- Обложка -->
                    <div class="col-span-2">
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Обложка маршрута
                        </label>
                        
                        @if($route->cover_image)
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Текущая обложка:</p>
                                <img src="{{ Storage::url($route->cover_image) }}" alt="Текущая обложка" class="max-h-64 rounded-lg">
                            </div>
                        @endif
                        
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none">
                                        <span>Загрузить новое изображение</span>
                                        <input id="cover_image" name="cover_image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF до 5MB</p>
                            </div>
                        </div>
                        <div id="cover-preview" class="mt-4 hidden">
                            <p class="text-sm text-gray-600 mb-2">Новая обложка:</p>
                            <img class="max-h-64 rounded-lg" src="" alt="Предпросмотр обложки">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Карта маршрута -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Карта маршрута</h2>
                <p class="text-gray-600 mb-4">Текущий маршрут. Для изменения используйте <a href="{{ route('routes.create') }}" class="text-orange-600 font-medium">создание нового маршрута</a>.</p>
                
                <div id="map-edit"></div>
                
                <!-- Скрытые поля с текущими координатами -->
                <input type="hidden" id="start_coordinates" name="start_coordinates" value="{{ json_encode($route->start_coordinates) }}">
                <input type="hidden" id="end_coordinates" name="end_coordinates" value="{{ json_encode($route->end_coordinates) }}">
                <input type="hidden" id="path_coordinates" name="path_coordinates" value="{{ json_encode($route->path_coordinates) }}">
            </div>
            
            <!-- Теги -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Теги и категории</h2>
                <p class="text-gray-600 mb-4">Выберите теги, которые лучше всего описывают ваш маршрут</p>
                
                <div class="flex flex-wrap gap-3">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                   {{ $route->tags->contains($tag->id) ? 'checked' : '' }}>
                            <span class="ml-2 px-4 py-2 rounded-full text-sm font-medium transition duration-300 hover:shadow-md"
                                  style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                <i class="{{ $tag->icon }} mr-1"></i>#{{ $tag->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Публикация -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-1">Публикация</h2>
                        <p class="text-gray-600">Статус публикации маршрута</p>
                    </div>
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" class="sr-only peer" {{ $route->is_published ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Опубликовано</span>
                    </label>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="flex justify-between">
                <!-- Кнопка удаления -->
                @can('delete', $route)
                    <button type="button" id="delete-route-btn" class="px-6 py-3 bg-red-100 text-red-800 rounded-lg font-medium hover:bg-red-200 transition duration-300">
                        <i class="fas fa-trash mr-2"></i> Удалить маршрут
                    </button>
                @endcan
                
                <div class="flex space-x-4">
                    <a href="{{ route('routes.show', $route) }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition duration-300">
                        Отмена
                    </a>
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-8 py-3 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                        <i class="fas fa-save mr-2"></i> Сохранить изменения
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Форма удаления (скрытая) -->
        @can('delete', $route)
            <form id="delete-route-form" action="{{ route('routes.destroy', $route) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let editMap;
let routeLayer;

// Инициализация карты
function initEditMap() {
    editMap = L.map('map-edit').setView([55.7558, 37.6173], 5);
    
    // OpenStreetMap слой
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(editMap);
    
    // Отображаем текущий маршрут
    displayCurrentRoute();
}

// Отображение текущего маршрута
function displayCurrentRoute() {
    const startCoords = @json($route->start_coordinates);
    const endCoords = @json($route->end_coordinates);
    const pathCoords = @json($route->path_coordinates);
    
    // Маркер начала
    if (startCoords) {
        const startIcon = L.divIcon({
            html: `<div class="w-10 h-10 rounded-full bg-green-500 border-2 border-white shadow-lg flex items-center justify-center">
                     <i class="fas fa-play text-white"></i>
                   </div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        
        L.marker(startCoords, { icon: startIcon })
            .addTo(editMap)
            .bindPopup('Начало маршрута');
    }
    
    // Маркер конца
    if (endCoords) {
        const endIcon = L.divIcon({
            html: `<div class="w-10 h-10 rounded-full bg-red-500 border-2 border-white shadow-lg flex items-center justify-center">
                     <i class="fas fa-flag-checkered text-white"></i>
                   </div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        
        L.marker(endCoords, { icon: endIcon })
            .addTo(editMap)
            .bindPopup('Конец маршрута');
    }
    
    // Линия маршрута
    if (pathCoords && pathCoords.length > 0) {
        routeLayer = L.polyline(pathCoords, {
            color: '#FF7A45',
            weight: 4,
            opacity: 0.7,
            dashArray: '5, 10'
        }).addTo(editMap);
        
        // Устанавливаем границы карты по маршруту
        editMap.fitBounds(routeLayer.getBounds());
    }
    
    // Добавляем точки интереса
    @foreach($route->points as $point)
        const pointIcon = L.divIcon({
            html: `<div class="w-8 h-8 rounded-full bg-white border-3 border-orange-500 shadow-lg flex items-center justify-center">
                     <i class="{{ $point->type_icon }} text-orange-500"></i>
                   </div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        L.marker([{{ $point->lat }}, {{ $point->lng }}], { icon: pointIcon })
            .addTo(editMap)
            .bindPopup(`<b>{{ $point->title }}</b><br>{{ $point->type_label }}`);
    @endforeach
}

// Превью обложки
$('#cover_image').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#cover-preview').removeClass('hidden').find('img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }
});

// Удаление маршрута
$('#delete-route-btn').on('click', function() {
    if (confirm('Вы уверены, что хотите удалить этот маршрут? Это действие нельзя отменить.')) {
        $('#delete-route-form').submit();
    }
});

// Валидация формы
$('#edit-route-form').on('submit', function(e) {
    let valid = true;
    
    // Проверка обязательных полей
    $('input[required], select[required], textarea[required]').each(function() {
        if (!$(this).val().trim()) {
            valid = false;
            $(this).addClass('border-red-500');
        } else {
            $(this).removeClass('border-red-500');
        }
    });
    
    // Проверка описания (минимум 100 символов)
    if ($('#description').val().length < 100) {
        valid = false;
        $('#description').addClass('border-red-500');
        alert('Описание должно содержать минимум 100 символов');
    }
    
    if (!valid) {
        e.preventDefault();
        return false;
    }
    
    // Показываем индикатор загрузки
    $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin mr-2"></i> Сохранение...');
    $('button[type="submit"]').prop('disabled', true);
});

$(document).ready(function() {
    // Инициализация карты
    initEditMap();
    
    // Обновление карты при изменении размера
    $(window).on('resize', function() {
        setTimeout(() => editMap.invalidateSize(), 200);
    });
});
</script>
@endpush