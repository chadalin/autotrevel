@extends('layouts.app')

@section('title', 'Создание маршрута - AutoRuta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<style>
    #map-create {
        height: 500px;
        border-radius: 0.5rem;
        z-index: 1;
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
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    
    .route-point-marker {
        width: 30px;
        height: 30px;
        background: white;
        border: 3px solid #3B82F6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #3B82F6;
        font-size: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    
    .map-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .point-card {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .point-card:hover {
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .map-instruction {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border-left: 4px solid #0ea5e9;
    }
    
    .drawing-active {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
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
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200"
                               placeholder="Например: Золотое кольцо России">
                        <div class="text-sm text-red-500 mt-1 hidden" id="title-error"></div>
                    </div>
                    
                    <!-- Описание -->
                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Подробное описание *
                        </label>
                        <textarea id="description" name="description" rows="6" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200"
                                  placeholder="Опишите ваш маршрут: что интересного можно увидеть, особенности дороги, советы путешественникам..."></textarea>
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-sm text-gray-500">Минимум 100 символов</p>
                            <p class="text-sm text-gray-500"><span id="description-counter">0</span>/100 символов</p>
                        </div>
                        <div class="text-sm text-red-500 mt-1 hidden" id="description-error"></div>
                    </div>
                    
                    <!-- Длина и время -->
                    <div>
                        <label for="length_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Длина маршрута (км) *
                        </label>
                        <input type="number" id="length_km" name="length_km" required min="1" step="0.1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200"
                               placeholder="0.0">
                        <div class="text-sm text-red-500 mt-1 hidden" id="length-error"></div>
                    </div>
                    
                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Время в пути (минуты) *
                        </label>
                        <div class="relative">
                            <input type="number" id="duration_minutes" name="duration_minutes" required min="10"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200"
                                   placeholder="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500">мин</span>
                            </div>
                        </div>
                        <div class="text-sm text-red-500 mt-1 hidden" id="duration-error"></div>
                    </div>
                    
                    <!-- Сложность -->
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                            Сложность *
                        </label>
                        <select id="difficulty" name="difficulty" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200">
                            <option value="">Выберите сложность</option>
                            <option value="easy">Лёгкий (для новичков)</option>
                            <option value="medium">Средний (требуется опыт)</option>
                            <option value="hard">Сложный (для опытных водителей)</option>
                        </select>
                        <div class="text-sm text-red-500 mt-1 hidden" id="difficulty-error"></div>
                    </div>
                    
                    <!-- Тип дороги -->
                    <div>
                        <label for="road_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Тип дороги *
                        </label>
                        <select id="road_type" name="road_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200">
                            <option value="">Выберите тип дороги</option>
                            <option value="asphalt">Асфальт</option>
                            <option value="gravel">Гравий</option>
                            <option value="offroad">Бездорожье</option>
                            <option value="mixed">Смешанный</option>
                        </select>
                        <div class="text-sm text-red-500 mt-1 hidden" id="road-type-error"></div>
                    </div>
                    
                    <!-- Обложка -->
                    <div class="col-span-2">
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Обложка маршрута
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition duration-200">
                            <div class="space-y-1 text-center">
                                <div class="flex justify-center text-gray-400">
                                    <i class="fas fa-camera text-4xl"></i>
                                </div>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none">
                                        <span class="text-lg">Загрузите изображение</span>
                                        <input id="cover_image" name="cover_image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF до 5MB</p>
                            </div>
                        </div>
                        <div id="cover-preview" class="mt-4 hidden">
                            <div class="relative">
                                <img class="max-h-64 w-full object-cover rounded-lg" src="" alt="Предпросмотр обложки">
                                <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600" id="remove-cover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-red-500 mt-1 hidden" id="cover-error"></div>
                    </div>
                </div>
            </div>
            
            <!-- Карта для построения маршрута -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 relative">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Построение маршрута на карте</h2>
                <p class="text-gray-600 mb-4">Нарисуйте линию маршрута на карте или добавьте точки вручную</p>
                
                <div id="map-create" class="relative">
                    <!-- Индикатор загрузки -->
                    <div id="map-loading" class="absolute inset-0 bg-gray-100 rounded-lg flex items-center justify-center z-0">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mx-auto mb-4"></div>
                            <p class="text-gray-600">Загрузка карты...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Панель управления картой -->
                <div class="map-controls">
                    <div class="space-y-2">
                        <button type="button" id="draw-line-btn" 
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 flex items-center justify-center">
                            <i class="fas fa-draw-polygon mr-2"></i> Рисовать маршрут
                        </button>
                        <button type="button" id="add-point-btn-map" 
                                class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 flex items-center justify-center">
                            <i class="fas fa-map-marker-alt mr-2"></i> Добавить точку
                        </button>
                        <button type="button" id="clear-map-btn" 
                                class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 flex items-center justify-center">
                            <i class="fas fa-trash mr-2"></i> Очистить
                        </button>
                        <button type="button" id="center-map-btn" 
                                class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 flex items-center justify-center">
                            <i class="fas fa-crosshairs mr-2"></i> Центрировать
                        </button>
                    </div>
                </div>
                
                <!-- Скрытые поля для координат -->
                <input type="hidden" id="start_coordinates" name="start_coordinates">
                <input type="hidden" id="end_coordinates" name="end_coordinates">
                <input type="hidden" id="path_coordinates" name="path_coordinates">
                
                <!-- Инструкция -->
                <div class="mt-4 p-4 map-instruction rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Как построить маршрут:</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Нажмите <span class="font-bold">"Рисовать маршрут"</span> и кликайте по карте</li>
                                    <li>Дважды кликните или нажмите Enter для завершения</li>
                                    <li>Нажмите <span class="font-bold">"Добавить точку"</span> для создания остановок</li>
                                    <li>Перетаскивайте точки для редактирования</li>
                                    <li>Используйте <span class="font-bold">"Очистить"</span> чтобы начать заново</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Статус карты -->
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-route text-gray-500 mr-2"></i>
                            <span id="route-status" class="text-sm text-gray-600">Маршрут не построен</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-gray-500 mr-2"></i>
                            <span id="points-status" class="text-sm text-gray-600">Точек: 0</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Точки интереса -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Точки интереса (остановки)</h2>
                <p class="text-gray-600 mb-4">Добавьте интересные места вдоль маршрута. Точки будут автоматически добавлены с карты.</p>
                
                <div id="points-container">
                    <!-- Точки будут добавляться динамически -->
                </div>
                
                <!-- Шаблон точки -->
                <template id="point-template">
                    <div class="point-card border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-map-marker-alt text-orange-500"></i>
                                </div>
                                <h4 class="font-bold text-gray-800">Точка <span class="point-number">1</span></h4>
                            </div>
                            <button type="button" class="text-red-500 hover:text-red-700 remove-point p-2 hover:bg-red-50 rounded-full">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                                <input type="text" name="points[0][title]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200"
                                       placeholder="Например: Смотровая площадка">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Тип *</label>
                                <select name="points[0][type]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200">
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
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200"
                                          placeholder="Краткое описание точки..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Широта *</label>
                                <div class="relative">
                                    <input type="text" name="points[0][lat]" required readonly
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    <button type="button" class="absolute inset-y-0 right-0 px-3 copy-coords" data-coord="lat">
                                        <i class="fas fa-copy text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Долгота *</label>
                                <div class="relative">
                                    <input type="text" name="points[0][lng]" required readonly
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    <button type="button" class="absolute inset-y-0 right-0 px-3 copy-coords" data-coord="lng">
                                        <i class="fas fa-copy text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Фотографии</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-orange-400 transition duration-200">
                                    <input type="file" name="points[0][photos][]" multiple accept="image/*"
                                           class="w-full cursor-pointer" 
                                           onchange="previewPointPhotos(this)">
                                    <p class="mt-2 text-sm text-gray-500">Перетащите или выберите файлы</p>
                                </div>
                                <div class="point-photos-preview mt-2 grid grid-cols-4 gap-2 hidden"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Теги -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Теги и категории</h2>
                <p class="text-gray-600 mb-4">Выберите теги, которые лучше всего описывают ваш маршрут</p>
                
                <div class="flex flex-wrap gap-3" id="tags-container">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center cursor-pointer tag-item">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   class="hidden tag-checkbox">
                            <span class="px-4 py-2 rounded-full text-sm font-medium transition duration-300 hover:shadow-md border-2 border-transparent hover:scale-105 transform"
                                  style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                <i class="{{ $tag->icon }} mr-1"></i>#{{ $tag->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="text-sm text-red-500 mt-1 hidden" id="tags-error"></div>
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
                <a href="{{ route('routes.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition duration-300 flex items-center">
                    <i class="fas fa-times mr-2"></i> Отмена
                </a>
                <button type="submit" id="submit-btn" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-8 py-3 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl flex items-center">
                    <i class="fas fa-save mr-2"></i> Создать маршрут
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для подсказок -->
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Справка по карте</h3>
            <button type="button" id="close-help" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-3">
            <div class="flex items-start">
                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-draw-polygon text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Рисование маршрута</h4>
                    <p class="text-sm text-gray-600">Кликайте по карте чтобы добавить точки маршрута. Дважды кликните для завершения.</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-green-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-map-marker-alt text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Добавление точек</h4>
                    <p class="text-sm text-gray-600">В режиме добавления точек кликните по карте чтобы создать остановку.</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                    <i class="fas fa-arrows-alt text-yellow-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Редактирование</h4>
                    <p class="text-sm text-gray-600">Перетаскивайте точки для изменения маршрута или перемещения остановок.</p>
                </div>
            </div>
        </div>
        <button type="button" id="got-it-btn" class="mt-6 w-full bg-orange-500 text-white py-2 rounded-lg font-medium hover:bg-orange-600 transition duration-300">
            Понятно
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
// Глобальные переменные
let map;
let drawnItems;
let points = [];
let routePolyline = null;
let isDrawing = false;
let isAddingPoint = false;
let routeMarkers = [];

// Инициализация карты
function initMap() {
    console.log('Инициализация карты...');
    
    // Скрываем индикатор загрузки
    $('#map-loading').hide();
    
    try {
        // Создаем карту с центром в России
        map = L.map('map-create', {
            preferCanvas: true,
            zoomControl: false
        }).setView([55.7558, 37.6173], 5);
        
        // Добавляем слой OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            detectRetina: true
        }).addTo(map);
        
        // Добавляем контроль масштабирования
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);
        
        // Инициализируем группу для рисования
        drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);
        
        // Добавляем обработчик клика по карте
        map.on('click', function(e) {
            if (isDrawing) {
                addRoutePoint(e.latlng);
            } else if (isAddingPoint) {
                addInterestPointFromMap(e.latlng);
            }
        });
        
        // Добавляем обработчик двойного клика
        map.on('dblclick', function(e) {
            if (isDrawing && routeMarkers.length >= 2) {
                finishDrawing();
            }
        });
        
        // Обработка нажатия клавиш
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isDrawing) {
                cancelDrawing();
            } else if (e.key === 'Enter' && isDrawing && routeMarkers.length >= 2) {
                finishDrawing();
            }
        });
        
        // Обновляем размер карты при изменении размера окна
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
        
        // Инициализация кнопок управления
        initMapControls();
        
        console.log('Карта успешно инициализирована');
        showToast('Карта загружена. Начните рисовать маршрут!', 'success');
        
    } catch (error) {
        console.error('Ошибка при создании карты:', error);
        showToast('Не удалось загрузить карту. Пожалуйста, обновите страницу.', 'error');
    }
}

// Инициализация элементов управления картой
function initMapControls() {
    // Кнопка рисования маршрута
    $('#draw-line-btn').on('click', function() {
        if (isDrawing) {
            cancelDrawing();
        } else {
            startDrawing();
        }
    });
    
    // Кнопка добавления точки
    $('#add-point-btn-map').on('click', function() {
        if (isAddingPoint) {
            stopAddingPoints();
        } else {
            startAddingPoints();
        }
    });
    
    // Кнопка очистки карты
    $('#clear-map-btn').on('click', function() {
        if (confirm('Вы уверены, что хотите очистить карту? Все нарисованные элементы будут удалены.')) {
            clearMap();
        }
    });
    
    // Кнопка центрирования карты
    $('#center-map-btn').on('click', function() {
        if (routePolyline && routePolyline.getLatLngs().length > 0) {
            map.fitBounds(routePolyline.getBounds());
        } else {
            map.setView([55.7558, 37.6173], 5);
        }
    });
}

// Начало рисования маршрута
function startDrawing() {
    if (isAddingPoint) {
        stopAddingPoints();
    }
    
    isDrawing = true;
    $('#draw-line-btn').addClass('drawing-active bg-blue-600');
    $('#draw-line-btn').html('<i class="fas fa-stop-circle mr-2"></i> Завершить рисование');
    
    // Очищаем предыдущий маршрут если есть
    if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
    }
    
    // Удаляем маркеры маршрута
    routeMarkers.forEach(marker => map.removeLayer(marker));
    routeMarkers = [];
    
    // Создаем новую линию
    routePolyline = L.polyline([], {
        color: '#FF7A45',
        weight: 4,
        opacity: 0.8,
        smoothFactor: 1,
        dashArray: '10, 10'
    }).addTo(map);
    
    showToast('Начните кликать по карте чтобы добавить точки маршрута. Дважды кликните или нажмите Enter для завершения.', 'info');
}

// Добавление точки маршрута
function addRoutePoint(latlng) {
    if (!isDrawing || !routePolyline) return;
    
    // Добавляем точку к линии
    const currentPoints = routePolyline.getLatLngs();
    currentPoints.push(latlng);
    routePolyline.setLatLngs(currentPoints);
    
    // Создаем маркер
    const marker = L.marker(latlng, {
        draggable: true,
        icon: L.divIcon({
            html: `<div class="route-point-marker">${currentPoints.length}</div>`,
            iconSize: [30, 30],
            className: 'route-point-marker'
        })
    }).addTo(map);
    
    // Добавляем в массив маркеров
    routeMarkers.push(marker);
    
    // Обновление координат при перемещении маркера
    marker.on('dragend', function(e) {
        const newLatLng = e.target.getLatLng();
        const index = routeMarkers.indexOf(marker);
        if (index !== -1) {
            const points = routePolyline.getLatLngs();
            points[index] = newLatLng;
            routePolyline.setLatLngs(points);
            updateRouteCoordinates(points);
        }
    });
    
    // Обновляем скрытые поля
    updateRouteCoordinates(currentPoints);
    
    // Обновляем статус
    updateMapStatus();
}

// Завершение рисования
function finishDrawing() {
    if (!isDrawing || routeMarkers.length < 2) return;
    
    isDrawing = false;
    $('#draw-line-btn').removeClass('drawing-active bg-blue-600');
    $('#draw-line-btn').html('<i class="fas fa-draw-polygon mr-2"></i> Рисовать маршрут');
    
    // Убираем пунктирную линию
    routePolyline.setStyle({
        dashArray: null
    });
    
    // Добавляем начальную и конечную иконки
    if (routeMarkers.length > 0) {
        // Начальная точка
        routeMarkers[0].setIcon(L.divIcon({
            html: '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"><i class="fas fa-play text-white text-xs"></i></div>',
            iconSize: [32, 32]
        }));
        
        // Конечная точка
        routeMarkers[routeMarkers.length - 1].setIcon(L.divIcon({
            html: '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center"><i class="fas fa-flag-checkered text-white text-xs"></i></div>',
            iconSize: [32, 32]
        }));
    }
    
    showToast('Маршрут построен!', 'success');
    updateMapStatus();
}

// Отмена рисования
function cancelDrawing() {
    isDrawing = false;
    $('#draw-line-btn').removeClass('drawing-active bg-blue-600');
    $('#draw-line-btn').html('<i class="fas fa-draw-polygon mr-2"></i> Рисовать маршрут');
    
    // Удаляем временную линию
    if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
    }
    
    // Удаляем временные маркеры
    routeMarkers.forEach(marker => map.removeLayer(marker));
    routeMarkers = [];
    
    showToast('Рисование отменено', 'warning');
    updateMapStatus();
}

// Обновление координат маршрута в форме
function updateRouteCoordinates(points) {
    if (!points || points.length === 0) {
        $('#start_coordinates').val('');
        $('#end_coordinates').val('');
        $('#path_coordinates').val('');
        return;
    }
    
    const latlngs = points.map(p => [p.lat, p.lng]);
    
    $('#start_coordinates').val(JSON.stringify([points[0].lat, points[0].lng]));
    $('#end_coordinates').val(JSON.stringify([points[points.length - 1].lat, points[points.length - 1].lng]));
    $('#path_coordinates').val(JSON.stringify(latlngs));
    
    // Автоматически вычисляем длину маршрута
    if (points.length >= 2) {
        const lengthKm = calculateRouteLength(points);
        $('#length_km').val(lengthKm.toFixed(1));
        
        // Вычисляем примерное время в пути (средняя скорость 60 км/ч)
        const durationMinutes = Math.round((lengthKm / 60) * 60);
        $('#duration_minutes').val(durationMinutes > 10 ? durationMinutes : 10);
    }
}

// Вычисление длины маршрута
function calculateRouteLength(points) {
    let totalDistance = 0;
    for (let i = 1; i < points.length; i++) {
        const dist = map.distance(points[i-1], points[i]);
        totalDistance += dist;
    }
    return totalDistance / 1000; // Конвертируем в километры
}

// Начало добавления точек интереса
function startAddingPoints() {
    if (isDrawing) {
        cancelDrawing();
    }
    
    isAddingPoint = true;
    $('#add-point-btn-map').addClass('bg-green-600');
    $('#add-point-btn-map').html('<i class="fas fa-times mr-2"></i> Отменить добавление');
    
    showToast('Кликайте по карте чтобы добавить точку интереса. Заполните информацию о точке в форме ниже.', 'info');
}

// Добавление точки интереса с карты
function addInterestPointFromMap(latlng) {
    if (!isAddingPoint) return;
    
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
    
    // Добавляем всплывающую подсказку
    marker.bindPopup(`Точка ${pointIndex + 1}<br>Перетащите для перемещения`);
    
    // Добавляем точку в массив
    const point = {
        marker: marker,
        lat: latlng.lat,
        lng: latlng.lng,
        index: pointIndex
    };
    points.push(point);
    
    // Обновление координат при перемещении маркера
    marker.on('dragend', function(e) {
        point.lat = e.target.getLatLng().lat;
        point.lng = e.target.getLatLng().lng;
        updatePointForm(pointIndex);
        marker.setPopupContent(`Точка ${pointIndex + 1}<br>Координаты: ${point.lat.toFixed(6)}, ${point.lng.toFixed(6)}`);
    });
    
    // Создаем форму точки
    addPointForm(pointIndex, latlng.lat, latlng.lng);
    
    // Обновляем статус
    updateMapStatus();
}

// Остановка добавления точек
function stopAddingPoints() {
    isAddingPoint = false;
    $('#add-point-btn-map').removeClass('bg-green-600');
    $('#add-point-btn-map').html('<i class="fas fa-map-marker-alt mr-2"></i> Добавить точку');
}

// Добавление формы точки
function addPointForm(index, lat, lng) {
    const template = document.getElementById('point-template');
    const clone = template.content.cloneNode(true);
    
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
    
    // Устанавливаем название по умолчанию
    $(clone).find('input[name$="[title]"]').val(`Точка ${pointNumber}`);
    
    // Обработчик удаления
    $(clone).find('.remove-point').on('click', function() {
        removePoint(index);
    });
    
    // Обработчик копирования координат
    $(clone).find('.copy-coords').on('click', function() {
        const coordType = $(this).data('coord');
        const coordValue = $(this).closest('.relative').find('input').val();
        copyToClipboard(coordValue);
        showToast(`Координата ${coordType} скопирована!`, 'success');
    });
    
    $('#points-container').append(clone);
    updateMapStatus();
}

// Обновление формы точки
function updatePointForm(index) {
    const point = points[index];
    if (!point) return;
    
    $(`input[name="points[${index}][lat]"]`).val(point.lat.toFixed(6));
    $(`input[name="points[${index}][lng]"]`).val(point.lng.toFixed(6));
}

// Удаление точки
function removePoint(index) {
    const point = points[index];
    if (!point) return;
    
    // Удаляем маркер с карты
    if (point.marker) {
        map.removeLayer(point.marker);
    }
    
    // Удаляем из массива
    points.splice(index, 1);
    
    // Удаляем форму
    $(`.point-card`).eq(index).remove();
    
    // Обновляем номера оставшихся точек
    updatePointNumbers();
    
    // Обновляем маркеры на карте
    points.forEach((p, i) => {
        p.index = i;
        if (p.marker) {
            p.marker.setIcon(L.divIcon({
                html: `<div class="point-marker">${i + 1}</div>`,
                iconSize: [40, 40],
                className: 'point-marker'
            }));
            p.marker.setPopupContent(`Точка ${i + 1}<br>Координаты: ${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`);
        }
    });
    
    updateMapStatus();
    showToast('Точка удалена', 'warning');
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

// Обновление статуса карты
function updateMapStatus() {
    const routePoints = routePolyline ? routePolyline.getLatLngs().length : 0;
    $('#route-status').text(`Точек маршрута: ${routePoints}`);
    $('#points-status').text(`Точек интереса: ${points.length}`);
}

// Очистка карты
function clearMap() {
    // Очищаем маршрут
    if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
    }
    
    // Очищаем маркеры маршрута
    routeMarkers.forEach(marker => map.removeLayer(marker));
    routeMarkers = [];
    
    // Очищаем точки интереса
    points.forEach(point => {
        if (point.marker) {
            map.removeLayer(point.marker);
        }
    });
    points = [];
    
    // Очищаем формы точек
    $('#points-container').empty();
    
    // Очищаем скрытые поля
    $('#start_coordinates').val('');
    $('#end_coordinates').val('');
    $('#path_coordinates').val('');
    
    // Сбрасываем режимы
    isDrawing = false;
    isAddingPoint = false;
    $('#draw-line-btn').removeClass('drawing-active bg-blue-600');
    $('#draw-line-btn').html('<i class="fas fa-draw-polygon mr-2"></i> Рисовать маршрут');
    $('#add-point-btn-map').removeClass('bg-green-600');
    $('#add-point-btn-map').html('<i class="fas fa-map-marker-alt mr-2"></i> Добавить точку');
    
    // Обновляем статус
    updateMapStatus();
    
    showToast('Карта очищена', 'warning');
}

// Предпросмотр фотографий точки
function previewPointPhotos(input) {
    const previewContainer = $(input).siblings('.point-photos-preview');
    previewContainer.empty().removeClass('hidden');
    
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            if (index < 4) { // Показываем только первые 4 фото
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.append(`
                        <div class="relative">
                            <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg">
                            <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center hover:bg-red-600 remove-photo">×</button>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            }
        });
        
        if (input.files.length > 4) {
            previewContainer.append(`
                <div class="col-span-4 text-center text-sm text-gray-500">
                    + еще ${input.files.length - 4} фото
                </div>
            `);
        }
    }
}

// Превью обложки
$('#cover_image').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showToast('Файл слишком большой. Максимальный размер: 5MB', 'error');
            $(this).val('');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#cover-preview').removeClass('hidden').find('img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }
});

// Удаление обложки
$('#remove-cover').on('click', function() {
    $('#cover_image').val('');
    $('#cover-preview').addClass('hidden');
    showToast('Обложка удалена', 'warning');
});

// Счетчик символов для описания
$('#description').on('input', function() {
    const length = $(this).val().length;
    $('#description-counter').text(length);
    
    if (length < 100) {
        $(this).addClass('border-red-500');
        $('#description-counter').addClass('text-red-500');
    } else {
        $(this).removeClass('border-red-500');
        $('#description-counter').removeClass('text-red-500');
    }
});

// Выбор тегов
$('.tag-item').on('click', function() {
    const checkbox = $(this).find('.tag-checkbox');
    const isChecked = checkbox.prop('checked');
    checkbox.prop('checked', !isChecked);
    
    if (!isChecked) {
        $(this).find('span').addClass('border-2 border-current');
    } else {
        $(this).find('span').removeClass('border-2 border-current');
    }
});

// Копирование в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        console.log('Текст скопирован: ' + text);
    }).catch(err => {
        console.error('Ошибка копирования: ', err);
    });
}

// Всплывающие уведомления
function showToast(message, type = 'info') {
    const colors = {
        info: 'bg-blue-100 border-blue-400 text-blue-700',
        success: 'bg-green-100 border-green-400 text-green-700',
        warning: 'bg-yellow-100 border-yellow-400 text-yellow-700',
        error: 'bg-red-100 border-red-400 text-red-700'
    };
    
    const icon = {
        info: 'fas fa-info-circle',
        success: 'fas fa-check-circle',
        warning: 'fas fa-exclamation-triangle',
        error: 'fas fa-times-circle'
    };
    
    const toastId = 'toast-' + Date.now();
    const toast = $(`
        <div id="${toastId}" class="toast ${colors[type]} border rounded-lg p-4 shadow-lg">
            <div class="flex items-center">
                <i class="${icon[type]} mr-3 text-lg"></i>
                <span class="flex-1">${message}</span>
                <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="$('#${toastId}').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

// Валидация формы
function validateForm() {
    let isValid = true;
    const errors = [];
    
    // Очищаем предыдущие ошибки
    $('[id$="-error"]').addClass('hidden').text('');
    $('input, select, textarea').removeClass('border-red-500');
    
    // Проверка обязательных полей
    const requiredFields = [
        { id: 'title', name: 'Название маршрута' },
        { id: 'description', name: 'Описание' },
        { id: 'length_km', name: 'Длина маршрута' },
        { id: 'duration_minutes', name: 'Время в пути' },
        { id: 'difficulty', name: 'Сложность' },
        { id: 'road_type', name: 'Тип дороги' }
    ];
    
    requiredFields.forEach(field => {
        const $field = $(`#${field.id}`);
        if (!$field.val().trim()) {
            $(`#${field.id}-error`).text(`Поле "${field.name}" обязательно для заполнения`).removeClass('hidden');
            $field.addClass('border-red-500');
            errors.push(`Заполните поле "${field.name}"`);
            isValid = false;
        }
    });
    
    // Проверка описания (минимум 100 символов)
    if ($('#description').val().length < 100) {
        $('#description-error').text('Описание должно содержать минимум 100 символов').removeClass('hidden');
        $('#description').addClass('border-red-500');
        errors.push('Описание должно содержать минимум 100 символов');
        isValid = false;
    }
    
    // Проверка маршрута на карте
    if (!$('#path_coordinates').val()) {
        errors.push('Нарисуйте маршрут на карте');
        isValid = false;
        showToast('Пожалуйста, нарисуйте маршрут на карте', 'error');
    }
    
    // Проверка обложки
    const coverFile = $('#cover_image')[0].files[0];
    if (coverFile && coverFile.size > 5 * 1024 * 1024) {
        $('#cover-error').text('Размер файла не должен превышать 5MB').removeClass('hidden');
        errors.push('Размер файла обложки не должен превышать 5MB');
        isValid = false;
    }
    
    // Проверка точек интереса
    $('.point-card').each(function(index) {
        const $title = $(this).find('input[name$="[title]"]');
        const $type = $(this).find('select[name$="[type]"]');
        
        if (!$title.val().trim()) {
            errors.push(`Заполните название для точки ${index + 1}`);
            $title.addClass('border-red-500');
            isValid = false;
        }
        
        if (!$type.val()) {
            errors.push(`Выберите тип для точки ${index + 1}`);
            $type.addClass('border-red-500');
            isValid = false;
        }
    });
    
    if (!isValid) {
        const errorMessage = errors.slice(0, 3).join('<br>');
        if (errors.length > 3) {
            errorMessage += '<br>...и еще ' + (errors.length - 3) + ' ошибок';
        }
        showToast(errorMessage, 'error');
    }
    
    return isValid;
}

// Модальное окно помощи
$('#close-help, #got-it-btn').on('click', function() {
    $('#help-modal').addClass('hidden');
});

// Инициализация при загрузке страницы
$(document).ready(function() {
    console.log('Страница загружена, инициализируем карту...');
    
    // Загружаем карту с небольшой задержкой
    setTimeout(initMap, 300);
    
    // Обработчик отправки формы
    $('#create-route-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        // Показываем индикатор загрузки
        $('#submit-btn').html('<i class="fas fa-spinner fa-spin mr-2"></i> Создание...');
        $('#submit-btn').prop('disabled', true);
        
        // Отправляем форму
        this.submit();
    });
    
    // Обработчик ресайза окна
    $(window).on('resize', function() {
        if (map) {
            setTimeout(() => {
                map.invalidateSize();
            }, 200);
        }
    });
    
    // Добавляем кнопку помощи
    $('.map-controls').append(`
        <button type="button" id="help-btn" 
                class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 flex items-center justify-center mt-2">
            <i class="fas fa-question-circle mr-2"></i> Помощь
        </button>
    `);
    
    $('#help-btn').on('click', function() {
        $('#help-modal').removeClass('hidden');
    });
    
    // Удаление превью фотографий точки
    $(document).on('click', '.remove-photo', function() {
        $(this).closest('div').remove();
    });
});
</script>
@endpush