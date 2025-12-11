@extends('layouts.app')

@section('title', 'Создание маршрута - AutoRuta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-create {
        height: 500px;
        border-radius: 0.5rem;
    }
    .leaflet-draw-toolbar a {
        background-image: url('https://unpkg.com/leaflet-draw@1.0.4/dist/images/spritesheet.png') !important;
    }
    .point-marker {
        width: 40px;
        height: 40px;
        background: white;
        border: 3px solid #FF7A45;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #FF7A45;
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
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Создание маршрута</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Создание нового маршрута</h1>
        <p class="text-gray-600 mb-8">Создайте уникальный маршрут для автопутешествий с интересными остановками</p>
        
        <form id="create-route-form" action="{{ route('routes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Основная информация</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Название -->
                    <div class="col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Название маршрута *
                        </label>
                        <input type="text" id="title" name="title" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Например: Золотое кольцо России">
                    </div>
                    
                    <!-- Описание -->
                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Подробное описание *
                        </label>
                        <textarea id="description" name="description" rows="6" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Опишите ваш маршрут: что интересного можно увидеть, особенности дороги, советы путешественникам..."></textarea>
                        <p class="mt-1 text-sm text-gray-500">Минимум 100 символов</p>
                    </div>
                    
                    <!-- Длина и время -->
                    <div>
                        <label for="length_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Длина маршрута (км) *
                        </label>
                        <input type="number" id="length_km" name="length_km" required min="1" step="0.1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Время в пути (минуты) *
                        </label>
                        <input type="number" id="duration_minutes" name="duration_minutes" required min="10"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    
                    <!-- Сложность -->
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                            Сложность *
                        </label>
                        <select id="difficulty" name="difficulty" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Выберите сложность</option>
                            <option value="easy">Лёгкий (для новичков)</option>
                            <option value="medium">Средний (требуется опыт)</option>
                            <option value="hard">Сложный (для опытных водителей)</option>
                        </select>
                    </div>
                    
                    <!-- Тип дороги -->
                    <div>
                        <label for="road_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Тип дороги *
                        </label>
                        <select id="road_type" name="road_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Выберите тип дороги</option>
                            <option value="asphalt">Асфальт</option>
                            <option value="gravel">Гравий</option>
                            <option value="offroad">Бездорожье</option>
                            <option value="mixed">Смешанный</option>
                        </select>
                    </div>
                    
                    <!-- Обложка -->
                    <div class="col-span-2">
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Обложка маршрута
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none">
                                        <span>Загрузите изображение</span>
                                        <input id="cover_image" name="cover_image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF до 5MB</p>
                            </div>
                        </div>
                        <div id="cover-preview" class="mt-4 hidden">
                            <img class="max-h-64 rounded-lg" src="" alt="Предпросмотр обложки">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Карта для построения маршрута -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Построение маршрута на карте</h2>
                <p class="text-gray-600 mb-4">Нарисуйте линию маршрута на карте или добавьте точки вручную</p>
                
                <div id="map-create"></div>
                
                <!-- Скрытые поля для координат -->
                <input type="hidden" id="start_coordinates" name="start_coordinates">
                <input type="hidden" id="end_coordinates" name="end_coordinates">
                <input type="hidden" id="path_coordinates" name="path_coordinates">
                
                <!-- Инструкция -->
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Как построить маршрут:</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Нажмите на кнопку <span class="font-bold">"Нарисовать линию"</span> и кликайте по карте для создания маршрута</li>
                                    <li>Дважды кликните для завершения рисования</li>
                                    <li>Используйте кнопку <span class="font-bold">"Добавить точку"</span> для создания остановок</li>
                                    <li>Перетаскивайте точки для редактирования маршрута</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Точки интереса -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Точки интереса (остановки)</h2>
                <p class="text-gray-600 mb-4">Добавьте интересные места вдоль маршрута</p>
                
                <div id="points-container">
                    <!-- Точки будут добавляться динамически -->
                </div>
                
                <button type="button" id="add-point-btn" class="mt-4 bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-medium transition duration-300">
                    <i class="fas fa-plus mr-2"></i> Добавить точку интереса
                </button>
                
                <!-- Шаблон точки -->
                <template id="point-template">
                    <div class="point-card border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-bold text-gray-800">Точка <span class="point-number">1</span></h4>
                            <button type="button" class="text-red-500 hover:text-red-700 remove-point">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                                <input type="text" name="points[0][title]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Тип *</label>
                                <select name="points[0][type]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Выберите тип</option>
                                    <option value="viewpoint">Смотровая площадка</option>
                                    <option value="cafe">Кафе/ресторан</option>
                                    <option value="hotel">Отель/гостиница</option>
                                    <option value="attraction">Достопримечательность</option>
                                    <option value="gas_station">АЗС</option>
                                    <option value="camping">Кемпинг/стоянка</option>
                                    <option value="photo_spot">Место для фото</option>
                                    <option value="nature">Природный объект</option>
                                    <option value="historical">Исторический объект</option>
                                    <option value="other">Другое</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                                <textarea name="points[0][description]" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Широта (lat) *</label>
                                <input type="text" name="points[0][lat]" required readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Долгота (lng) *</label>
                                <input type="text" name="points[0][lng]" required readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Фотографии</label>
                                <input type="file" name="points[0][photos][]" multiple accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <p class="mt-1 text-sm text-gray-500">Можно выбрать несколько файлов</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Теги -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Теги и категории</h2>
                <p class="text-gray-600 mb-4">Выберите теги, которые лучше всего описывают ваш маршрут</p>
                
                <div class="flex flex-wrap gap-3">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
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
                        <p class="text-gray-600">Опубликовать маршрут сразу или сохранить как черновик</p>
                    </div>
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="publish" value="1" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Опубликовать</span>
                    </label>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('routes.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition duration-300">
                    Отмена
                </a>
                <button type="submit" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-8 py-3 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i> Создать маршрут
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
let map;
let drawnItems = new L.FeatureGroup();
let points = [];
let pointCounter = 0;

// Инициализация карты
function initMap() {
    // Центр России
    map = L.map('map-create').setView([55.7558, 37.6173], 5);
    
    // OpenStreetMap слой
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);
    
    // Добавляем группу для нарисованных элементов
    map.addLayer(drawnItems);
    
    // Инициализация инструментов рисования
    const drawControl = new L.Control.Draw({
        draw: {
            polyline: {
                shapeOptions: {
                    color: '#FF7A45',
                    weight: 4,
                    opacity: 0.7
                },
                allowIntersection: false,
                showLength: true,
                metric: true,
                feet: false,
                zIndexOffset: 1000
            },
            polygon: false,
            circle: false,
            rectangle: false,
            marker: false,
            circlemarker: false
        },
        edit: {
            featureGroup: drawnItems
        }
    });
    
    map.addControl(drawControl);
    
    // Обработка событий рисования
    map.on(L.Draw.Event.CREATED, function (event) {
        const layer = event.layer;
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);
        
        // Получаем координаты пути
        const coordinates = layer.getLatLngs();
        const pathCoords = coordinates.map(coord => [coord.lat, coord.lng]);
        
        // Заполняем скрытые поля
        if (pathCoords.length > 0) {
            $('#start_coordinates').val(JSON.stringify([pathCoords[0][0], pathCoords[0][1]]));
            $('#end_coordinates').val(JSON.stringify([pathCoords[pathCoords.length-1][0], pathCoords[pathCoords.length-1][1]]));
            $('#path_coordinates').val(JSON.stringify(pathCoords));
        }
    });
    
    // Обработка кликов по карте для добавления точек
    map.on('click', function(e) {
        if (window.addingPoint) {
            addPointFromMap(e.latlng);
        }
    });
}

// Добавление точки с карты
function addPointFromMap(latlng) {
    const pointIndex = points.length;
    
    // Создаем маркер
    const marker = L.marker(latlng, {
        draggable: true,
        icon: L.divIcon({
            html: `<div class="point-marker">${pointIndex + 1}</div>`,
            iconSize: [40, 40],
            className: 'point-marker'
        })
    }).addTo(map);
    
    // Добавляем точку в массив
    points.push({
        marker: marker,
        lat: latlng.lat,
        lng: latlng.lng,
        index: pointIndex
    });
    
    // Обновляем координаты при перемещении маркера
    marker.on('dragend', function(e) {
        const index = points.findIndex(p => p.marker === marker);
        if (index !== -1) {
            points[index].lat = e.target.getLatLng().lat;
            points[index].lng = e.target.getLatLng().lng;
            updatePointForm(index);
        }
    });
    
    // Создаем форму точки
    addPointForm(pointIndex, latlng.lat, latlng.lng);
    
    // Отключаем режим добавления точек
    window.addingPoint = false;
    $('#add-point-btn').text('Добавить точку интереса');
}

// Добавление формы точки
function addPointForm(index, lat, lng) {
    const template = document.getElementById('point-template');
    const clone = template.content.cloneNode(true);
    
    // Обновляем номера и имена полей
    const pointNumber = index + 1;
    $(clone).find('.point-number').text(pointNumber);
    
    // Обновляем имена полей
    $(clone).find('input, select, textarea').each(function() {
        const name = $(this).attr('name');
        if (name) {
            $(this).attr('name', name.replace('[0]', `[${index}]`));
        }
    });
    
    // Устанавливаем координаты
    $(clone).find('input[name$="[lat]"]').val(lat.toFixed(6));
    $(clone).find('input[name$="[lng]"]').val(lng.toFixed(6));
    
    // Обработчик удаления
    $(clone).find('.remove-point').on('click', function() {
        removePoint(index);
    });
    
    $('#points-container').append(clone);
}

// Обновление координат в форме
function updatePointForm(index) {
    const point = points[index];
    $(`input[name="points[${index}][lat]"]`).val(point.lat.toFixed(6));
    $(`input[name="points[${index}][lng]"]`).val(point.lng.toFixed(6));
}

// Удаление точки
function removePoint(index) {
    // Удаляем маркер с карты
    if (points[index] && points[index].marker) {
        map.removeLayer(points[index].marker);
    }
    
    // Удаляем из массива
    points.splice(index, 1);
    
    // Удаляем форму
    $(`.point-card`).eq(index).remove();
    
    // Обновляем номера оставшихся точек
    updatePointNumbers();
    
    // Обновляем индексы в массиве
    points.forEach((point, i) => {
        point.index = i;
        if (point.marker) {
            point.marker.setIcon(L.divIcon({
                html: `<div class="point-marker">${i + 1}</div>`,
                iconSize: [40, 40],
                className: 'point-marker'
            }));
        }
    });
}

// Обновление номеров точек
function updatePointNumbers() {
    $('.point-card').each(function(index) {
        $(this).find('.point-number').text(index + 1);
        
        // Обновляем имена полей
        $(this).find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            if (name) {
                const newName = name.replace(/points\[\d+\]/, `points[${index}]`);
                $(this).attr('name', newName);
            }
        });
    });
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

$(document).ready(function() {
    // Инициализация карты
    initMap();
    
    // Режим добавления точек
    $('#add-point-btn').on('click', function() {
        if (!window.addingPoint) {
            window.addingPoint = true;
            $(this).html('<i class="fas fa-times mr-2"></i> Отменить добавление точки');
            $(this).addClass('bg-red-100 hover:bg-red-200 text-red-800');
        } else {
            window.addingPoint = false;
            $(this).html('<i class="fas fa-plus mr-2"></i> Добавить точку интереса');
            $(this).removeClass('bg-red-100 hover:bg-red-200 text-red-800');
            $(this).addClass('bg-gray-100 hover:bg-gray-200 text-gray-800');
        }
    });
    
    // Валидация формы
    $('#create-route-form').on('submit', function(e) {
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
        
        // Проверка наличия маршрута на карте
        if (!$('#path_coordinates').val()) {
            valid = false;
            alert('Пожалуйста, нарисуйте маршрут на карте');
        }
        
        if (!valid) {
            e.preventDefault();
            return false;
        }
        
        // Показываем индикатор загрузки
        $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin mr-2"></i> Создание...');
        $('button[type="submit"]').prop('disabled', true);
    });
});
</script>
@endpush