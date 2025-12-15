@extends('layouts.app')

@section('title', 'Подтверждение прохождения - ' . $route->title)

@push('styles')
<style>
    .verification-container {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .photo-preview {
        max-height: 400px;
        object-fit: contain;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
    }
    
    .quest-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    
    .quest-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Подтверждение</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
    
    <div class="verification-container">
        <!-- Заголовок -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Подтверждение прохождения</h1>
            <p class="text-gray-600">Загрузите фото с места, чтобы подтвердить прохождение маршрута</p>
        </div>
        
        <!-- Информация о маршруте -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center mb-4">
                @if($route->cover_image)
                    <div class="w-16 h-16 rounded-lg overflow-hidden mr-4">
                        <img src="{{ Storage::url($route->cover_image) }}" 
                             alt="{{ $route->title }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-lg text-gray-800">{{ $route->title }}</h3>
                    <p class="text-gray-600">{{ $route->length_km }} км • {{ $route->duration_formatted }}</p>
                </div>
            </div>
        </div>
        
        <!-- Форма подтверждения -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Шаг 1: Загрузите фото</h3>
            <p class="text-gray-700 mb-4">
                Сделайте фото на фоне достопримечательностей маршрута. Фото должно содержать 
                <span class="font-medium text-orange-600">GPS-координаты</span> в EXIF данных.
            </p>
            
            <form id="verification-form" action="{{ route('quests.complete-route') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="route_id" value="{{ $route->id }}">
                
                <!-- Загрузка фото -->
                <div class="mb-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-orange-400 transition duration-300">
                        <div class="space-y-4">
                            <div class="mx-auto w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-camera text-orange-600 text-2xl"></i>
                            </div>
                            
                            <div>
                                <label for="photo" class="cursor-pointer">
                                    <span class="text-lg font-medium text-orange-600 hover:text-orange-700">Выберите фото</span>
                                    <input id="photo" name="photo" type="file" accept="image/*" capture="environment" class="hidden">
                                </label>
                                <p class="text-sm text-gray-500 mt-2">или перетащите файл сюда</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Превью фото -->
                    <div id="photo-preview" class="mt-4 hidden">
                        <img id="preview-image" src="" alt="Предпросмотр" class="w-full rounded-lg photo-preview">
                        <button type="button" id="remove-photo" class="mt-2 text-red-600 hover:text-red-800">
                            <i class="fas fa-times mr-1"></i> Удалить фото
                        </button>
                    </div>
                    
                    <!-- Ошибки валидации -->
                    @error('photo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Требования -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-medium text-blue-800 mb-2">Требования к фото:</h4>
                    <ul class="space-y-1 text-sm text-blue-700">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Разрешение не менее 2 МП
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Фото должно содержать GPS-данные
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Сделано в течение последних 24 часов
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Вы находитесь в пределах 500м от маршрута
                        </li>
                    </ul>
                </div>
                
                <!-- Выбор квеста -->
                @if($userQuests->count() > 0)
                    <div class="mb-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-4">Шаг 2: Выберите квест</h3>
                        
                        <div class="space-y-4">
                            @foreach($userQuests as $userQuest)
                                <label class="block cursor-pointer">
                                    <div class="quest-card bg-white border border-gray-200 rounded-lg p-4 
                                              hover:border-{{ $userQuest->quest->color }}-300 {{ in_array($userQuest->quest->id, $completedQuests) ? 'opacity-50' : '' }}">
                                        <div class="flex items-center">
                                            <input type="radio" 
                                                   name="quest_id" 
                                                   value="{{ $userQuest->quest->id }}"
                                                   {{ in_array($userQuest->quest->id, $completedQuests) ? 'disabled' : '' }}
                                                   class="mr-3">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h4 class="font-bold text-gray-800">{{ $userQuest->quest->title }}</h4>
                                                    @if($userQuest->quest->badge)
                                                        <span class="px-2 py-1 text-xs font-medium bg-{{ $userQuest->quest->color }}-100 text-{{ $userQuest->quest->color }}-800 rounded">
                                                            {{ $userQuest->quest->badge->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <p class="text-sm text-gray-600 mb-2">{{ $userQuest->quest->description }}</p>
                                                
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-700">
                                                        Прогресс: {{ $userQuest->progress_percentage }}%
                                                        ({{ $userQuest->completedRoutes->count() }}/{{ $userQuest->quest->routes->count() }})
                                                    </span>
                                                    <span class="font-bold text-green-600">
                                                        +{{ $userQuest->quest->reward_xp }} XP
                                                    </span>
                                                </div>
                                                
                                                <!-- Прогресс-бар -->
                                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-{{ $userQuest->quest->color }}-500 h-2 rounded-full" 
                                                         style="width: {{ $userQuest->progress_percentage }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if(in_array($userQuest->quest->id, $completedQuests))
                                            <div class="mt-2 p-2 bg-green-100 text-green-800 rounded text-sm">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Этот маршрут уже пройден в рамках данного квеста
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                            
                            <!-- Без квеста -->
                            <label class="block cursor-pointer">
                                <div class="quest-card bg-white border border-gray-200 rounded-lg p-4 hover:border-gray-300">
                                    <div class="flex items-center">
                                        <input type="radio" name="quest_id" value="" checked class="mr-3">
                                        <div>
                                            <h4 class="font-bold text-gray-800">Без квеста</h4>
                                            <p class="text-sm text-gray-600">Просто отметить маршрут как пройденный</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="quest_id" value="">
                @endif
                
                <!-- Кнопки -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('routes.show', $route) }}" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition duration-300">
                        Отмена
                    </a>
                    <button type="submit" 
                            id="submit-btn"
                            class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Подтвердить прохождение</span>
                        <span id="loading-spinner" class="hidden ml-2">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Важная информация -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <h4 class="font-bold text-yellow-800 mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>Важно знать:
            </h4>
            <ul class="space-y-2 text-sm text-yellow-700">
                <li>• Система автоматически проверит GPS-координаты на фото</li>
                <li>• Вы должны находиться в пределах 500 метров от маршрута</li>
                <li>• Одно фото можно использовать только для одного подтверждения</li>
                <li>• Фото должно быть сделано в течение последних 24 часов</li>
                <li>• Для получения достоверных GPS-данных включите геолокацию в настройках камеры</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    const previewImage = document.getElementById('preview-image');
    const removeButton = document.getElementById('remove-photo');
    const verificationForm = document.getElementById('verification-form');
    const submitBtn = document.getElementById('submit-btn');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    // Загрузка и предпросмотр фото
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    photoPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Удаление фото
    if (removeButton) {
        removeButton.addEventListener('click', function() {
            photoInput.value = '';
            photoPreview.classList.add('hidden');
        });
    }
    
    // Drag and drop для фото
    const dropArea = photoInput?.closest('.border-dashed');
    if (dropArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('border-orange-500', 'bg-orange-50');
        }
        
        function unhighlight() {
            dropArea.classList.remove('border-orange-500', 'bg-orange-50');
        }
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                photoInput.files = files;
                
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        photoPreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }
    
    // Отправка формы
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            const photo = photoInput.files[0];
            if (!photo) {
                e.preventDefault();
                alert('Пожалуйста, выберите фото для подтверждения');
                return;
            }
            
            // Проверка размера файла
            if (photo.size > 10 * 1024 * 1024) { // 10MB
                e.preventDefault();
                alert('Размер файла не должен превышать 10MB');
                return;
            }
            
            // Показываем индикатор загрузки
            submitBtn.disabled = true;
            loadingSpinner.classList.remove('hidden');
            submitBtn.querySelector('span').textContent = 'Проверка...';
        });
    }
    
    // Получение текущего местоположения (опционально)
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    console.log('Текущие координаты:', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    });
                },
                function(error) {
                    console.log('Ошибка получения геолокации:', error.message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }
    }
    
    // Получаем геолокацию при загрузке страницы
    getCurrentLocation();
});
</script>
@endpush