@extends('layouts.admin')

@section('title', 'Создание задания для квеста')
@section('page-title', 'Создание задания')
@section('page-subtitle', 'Добавление нового задания в квест')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <!-- Хлебные крошки -->
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Дашборд
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('admin.quests.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            Квесты
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('admin.quests.tasks.index', $quest) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            Задания квеста
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Создание задания</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Информация о квесте -->
    <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Квест: {{ $quest->title }}</h3>
                <p class="text-gray-600 mt-1">{{ $quest->short_description ?? Str::limit($quest->description, 100) }}</p>
                <div class="flex items-center mt-2 space-x-3">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                        {{ $quest->type_label }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full {{ $quest->difficulty_color }}">
                        {{ $quest->difficulty_label }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Заданий: {{ $quest->tasks_count ?? 0 }}</p>
                <p class="text-sm text-gray-600">Награда: {{ $quest->reward_exp }} EXP</p>
            </div>
        </div>
    </div>

    <!-- Форма создания задания -->
    <form action="{{ route('admin.quests.tasks.store', $quest) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Левая колонка - Основная информация -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Основная информация</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Название задания *
                            </label>
                            <input type="text" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   required
                                   placeholder="Например: Расшифруй код или Найдите точку на карте"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Описание задания
                            </label>
                            <textarea name="description" 
                                      rows="3"
                                      placeholder="Детальное описание задания, что нужно сделать..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Тип задания *
                            </label>
                            <select name="type" 
                                    required
                                    id="task-type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите тип задания</option>
                                <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>📝 Текст</option>
                                <option value="image" {{ old('type') == 'image' ? 'selected' : '' }}>🖼️ Изображение</option>
                                <option value="code" {{ old('type') == 'code' ? 'selected' : '' }}>🔢 Код</option>
                                <option value="cipher" {{ old('type') == 'cipher' ? 'selected' : '' }}>🔐 Шифр</option>
                                <option value="location" {{ old('type') == 'location' ? 'selected' : '' }}>📍 Локация</option>
                                <option value="puzzle" {{ old('type') == 'puzzle' ? 'selected' : '' }}>🧩 Головоломка</option>
                                <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>❓ Викторина</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Контент задания (динамически меняется в зависимости от типа) -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Содержание задания</h4>
                    <div id="type-specific-fields">
                        <!-- Содержание будет заполняться через JavaScript -->
                        <p class="text-gray-500 text-sm">Выберите тип задания чтобы настроить его содержание</p>
                    </div>
                </div>
                
                <!-- Подсказки -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Подсказки</h4>
                    <div id="hints-container">
                        <div class="hint-item mb-4 p-4 border border-gray-200 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Текст подсказки *</label>
                                    <input type="text" 
                                           name="content_hints[0][text]" 
                                           required
                                           placeholder="Например: Ищите рядом с..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип подсказки</label>
                                    <select name="content_hints[0][type]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                        <option value="location">📍 Локация</option>
                                        <option value="decryption">🔐 Расшифровка</option>
                                        <option value="direct">🎯 Прямая</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Стоимость очков</label>
                                    <input type="number" 
                                           name="content_hints[0][points_cost]" 
                                           value="10"
                                           min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Доступна через (мин)</label>
                                    <input type="number" 
                                           name="content_hints[0][available_after_minutes]" 
                                           value="5"
                                           min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                            </div>
                            <button type="button" class="remove-hint text-red-600 text-sm hover:text-red-800">
                                <i class="fas fa-trash mr-1"></i> Удалить подсказку
                            </button>
                        </div>
                    </div>
                    <button type="button" id="add-hint" class="mt-2 text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plus mr-1"></i> Добавить подсказку
                    </button>
                </div>
            </div>
            
            <!-- Правая колонка - Настройки и параметры -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Параметры задания</h4>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Порядок *
                                </label>
                                <input type="number" 
                                       name="order" 
                                       value="{{ old('order', $nextTasks->count() + 1) }}"
                                       required
                                       min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Очки *
                                </label>
                                <input type="number" 
                                       name="points" 
                                       value="{{ old('points', 10) }}"
                                       required
                                       min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Лимит времени (минут) *
                            </label>
                            <input type="number" 
                                   name="time_limit_minutes" 
                                   value="{{ old('time_limit_minutes', 15) }}"
                                   required
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Количество подсказок
                            </label>
                            <input type="number" 
                                   name="hints_available" 
                                   value="{{ old('hints_available', 3) }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Правильный ответ *
                            </label>
                            <input type="text" 
                                   name="required_answer" 
                                   value="{{ old('required_answer') }}"
                                   required
                                   placeholder="Ответ который должен ввести пользователь"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Для location-заданий можно указать координаты</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Следующее задание
                            </label>
                            <select name="next_task_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">— Автоматически по порядку —</option>
                                @foreach($nextTasks as $nextTask)
                                    <option value="{{ $nextTask->id }}" {{ old('next_task_id') == $nextTask->id ? 'selected' : '' }}>
                                        {{ $nextTask->order }}. {{ $nextTask->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Привязать к локации
                            </label>
                            <select name="location_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">— Не привязывать —</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->title }} ({{ $location->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_required" 
                                   id="is_required"
                                   value="1"
                                   {{ old('is_required', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_required" class="ml-2 block text-sm text-gray-700">
                                Обязательное задание
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Предпросмотр задания -->
                <div class="bg-blue-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Предпросмотр задания</h4>
                    <div id="task-preview" class="space-y-3">
                        <div class="p-4 bg-white rounded-lg border">
                            <p class="text-sm text-gray-500">Выберите тип задания чтобы увидеть предпросмотр</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Кнопки действия -->
        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between items-center">
            <div>
                <a href="{{ route('admin.quests.tasks.index', $quest) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Назад к заданиям
                </a>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="location.reload()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-300">
                    <i class="fas fa-redo mr-2"></i> Сбросить
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300 font-medium">
                    <i class="fas fa-plus mr-2"></i> Создать задание
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Динамическое изменение полей в зависимости от типа задания
    document.getElementById('task-type').addEventListener('change', function() {
        const type = this.value;
        const container = document.getElementById('type-specific-fields');
        const preview = document.getElementById('task-preview');
        
        let html = '';
        let previewHtml = '';
        
        switch(type) {
            case 'text':
                html = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Текст задания *
                            </label>
                            <textarea name="content_text" 
                                      rows="4"
                                      required
                                      placeholder="Введите текст задания..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${oldContent('content_text')}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Цвет фона
                            </label>
                            <input type="color" 
                                   name="content_background" 
                                   value="${oldContent('content_background', '#f8f9fa')}"
                                   class="w-full h-10 px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>`;
                
                previewHtml = `
                    <div class="p-4 bg-white rounded-lg border">
                        <div class="p-4 rounded" style="background: ${oldContent('content_background', '#f8f9fa')}">
                            <p class="mb-0">${oldContent('content_text', 'Текст задания будет отображаться здесь')}</p>
                        </div>
                    </div>`;
                break;
                
            case 'image':
                html = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Изображение *
                            </label>
                            <input type="file" 
                                   name="content_image" 
                                   accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <p class="mt-1 text-sm text-gray-500">Рекомендуемый размер: 1200x800px</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Подпись к изображению
                            </label>
                            <input type="text" 
                                   name="content_caption" 
                                   value="${oldContent('content_caption')}"
                                   placeholder="Описание что на изображении"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Вопрос к изображению *
                            </label>
                            <textarea name="content_question" 
                                      rows="2"
                                      required
                                      placeholder="Что нужно сделать с изображением?"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${oldContent('content_question')}</textarea>
                        </div>
                    </div>`;
                
                previewHtml = `
                    <div class="p-4 bg-white rounded-lg border">
                        <div class="text-center">
                            <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center mb-3">
                                <i class="fas fa-image text-4xl text-gray-400"></i>
                            </div>
                            ${oldContent('content_caption') ? `<p class="text-sm text-gray-600 mb-2">${oldContent('content_caption')}</p>` : ''}
                            ${oldContent('content_question') ? `
                                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                                    <p class="text-sm font-medium">Вопрос: ${oldContent('content_question')}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>`;
                break;
                
            case 'code':
                html = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Описание поиска кода *
                            </label>
                            <textarea name="content_description" 
                                      rows="3"
                                      required
                                      placeholder="Опишите где искать код..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${oldContent('content_description')}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Формат кода *
                                </label>
                                <select name="content_format" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="numeric" ${oldContent('content_format') == 'numeric' ? 'selected' : ''}>Цифровой (1234)</option>
                                    <option value="alphanumeric" ${oldContent('content_format') == 'alphanumeric' ? 'selected' : ''}>Буквенно-цифровой (A1B2)</option>
                                    <option value="letters" ${oldContent('content_format') == 'letters' ? 'selected' : ''}>Буквенный (ABCD)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Длина кода *
                                </label>
                                <input type="number" 
                                       name="content_length" 
                                       required
                                       value="${oldContent('content_length', 4)}"
                                       min="1"
                                       max="20"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Подсказка для поиска
                            </label>
                            <input type="text" 
                                   name="content_hint" 
                                   value="${oldContent('content_hint')}"
                                   placeholder="Намек где искать код"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>`;
                
                previewHtml = `
                    <div class="p-4 bg-white rounded-lg border">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <h5 class="font-medium text-gray-800 mb-2">Найдите код:</h5>
                            <p class="text-sm text-gray-700 mb-3">${oldContent('content_description', 'Описание поиска кода будет здесь')}</p>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 bg-gray-200 rounded text-sm">Формат: ${oldContent('content_format', 'numeric')}</span>
                                <span class="px-3 py-1 bg-gray-200 rounded text-sm">Длина: ${oldContent('content_length', 4)}</span>
                            </div>
                            ${oldContent('content_hint') ? `
                                <div class="mt-3 p-2 bg-yellow-50 border border-yellow-100 rounded">
                                    <p class="text-xs text-yellow-800">💡 Подсказка: ${oldContent('content_hint')}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>`;
                break;
                
            case 'cipher':
                html = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Зашифрованный текст *
                            </label>
                            <textarea name="content_cipher_text" 
                                      rows="3"
                                      required
                                      placeholder="Введите зашифрованный текст..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${oldContent('content_cipher_text')}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Тип шифра *
                                </label>
                                <select name="content_cipher_type" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="caesar" ${oldContent('content_cipher_type') == 'caesar' ? 'selected' : ''}>Шифр Цезаря</option>
                                    <option value="atbash" ${oldContent('content_cipher_type') == 'atbash' ? 'selected' : ''}>Атбаш</option>
                                    <option value="morse" ${oldContent('content_cipher_type') == 'morse' ? 'selected' : ''}>Азбука Морзе</option>
                                    <option value="substitution" ${oldContent('content_cipher_type') == 'substitution' ? 'selected' : ''}>Подстановочный</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Ключ шифра
                                </label>
                                <input type="text" 
                                       name="content_key" 
                                       value="${oldContent('content_key')}"
                                       placeholder="Например: 3 для шифра Цезаря"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Описание задания *
                            </label>
                            <input type="text" 
                                   name="content_description" 
                                   required
                                   value="${oldContent('content_description', 'Расшифруйте текст')}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>`;
                
                previewHtml = `
                    <div class="p-4 bg-white rounded-lg border">
                        <div class="p-4 bg-purple-50 rounded-lg">
                            <h5 class="font-medium text-gray-800 mb-2">Шифр:</h5>
                            <div class="mb-4 p-3 bg-gray-900 text-gray-100 rounded font-mono text-center">
                                ${oldContent('content_cipher_text', 'ЗАШИФРОВАННЫЙ_ТЕКСТ')}
                            </div>
                            <p class="text-sm text-gray-700">${oldContent('content_description', 'Расшифруйте текст')}</p>
                            <div class="mt-3 flex items-center space-x-3">
                                <span class="px-2 py-1 bg-gray-200 rounded text-xs">Тип: ${oldContent('content_cipher_type', 'caesar')}</span>
                                ${oldContent('content_key') ? `<span class="px-2 py-1 bg-gray-200 rounded text-xs">Ключ: ${oldContent('content_key')}</span>` : ''}
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'location':
                html = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Широта (lat) *
                                </label>
                                <input type="text" 
                                       name="content_coordinates_lat" 
                                       required
                                       value="${oldContent('content_coordinates_lat', '55.7558')}"
                                       placeholder="55.7558"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Долгота (lng) *
                                </label>
                                <input type="text" 
                                       name="content_coordinates_lng" 
                                       required
                                       value="${oldContent('content_coordinates_lng', '37.6173')}"
                                       placeholder="37.6173"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Радиус (метры) *
                            </label>
                            <input type="number" 
                                   name="content_radius" 
                                   required
                                   value="${oldContent('content_radius', 50)}"
                                   min="10"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Вопрос/задание *
                            </label>
                            <input type="text" 
                                   name="content_question" 
                                   required
                                   value="${oldContent('content_question', 'Доберитесь до указанной точки')}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Намек/указание
                            </label>
                            <input type="text" 
                                   name="content_clue" 
                                   value="${oldContent('content_clue')}"
                                   placeholder="Намек где находится точка"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>`;
                
                previewHtml = `
                    <div class="p-4 bg-white rounded-lg border">
                        <div class="p-4 bg-green-50 rounded-lg">
                            <h5 class="font-medium text-gray-800 mb-2">Локация:</h5>
                            <p class="text-sm text-gray-700 mb-3">${oldContent('content_question', 'Доберитесь до указанной точки')}</p>
                            <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center mb-3">
                                <i class="fas fa-map-marker-alt text-4xl text-red-500"></i>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="text-center p-2 bg-gray-100 rounded">
                                    <p class="text-xs text-gray-500">Широта</p>
                                    <p class="font-mono">${oldContent('content_coordinates_lat', '55.7558')}</p>
                                </div>
                                <div class="text-center p-2 bg-gray-100 rounded">
                                    <p class="text-xs text-gray-500">Долгота</p>
                                    <p class="font-mono">${oldContent('content_coordinates_lng', '37.6173')}</p>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span class="px-3 py-1 bg-gray-200 rounded text-sm">Радиус: ${oldContent('content_radius', 50)} метров</span>
                            </div>
                            ${oldContent('content_clue') ? `
                                <div class="mt-3 p-2 bg-yellow-50 border border-yellow-100 rounded">
                                    <p class="text-xs text-yellow-800">💡 Намек: ${oldContent('content_clue')}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>`;
                break;
                
            case 'puzzle':
            case 'quiz':
                html = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Вопрос *
                            </label>
                            <textarea name="content_question" 
                                      rows="3"
                                      required
                                      placeholder="Введите вопрос..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${oldContent('content_question')}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Варианты ответов (по одному на строку) *
                            </label>
                            <textarea name="content_options[]" 
                                      rows="4"
                                      required
                                      placeholder="Вариант 1\nВариант 2\nВариант 3\n..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${oldContent('content_options[]')}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Индекс правильного ответа *
                            </label>
                            <input type="number" 
                                   name="content_correct_index" 
                                   required
                                   value="${oldContent('content_correct_index', 0)}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Нумерация с 0 (первый вариант = 0)</p>
                        </div>
                    </div>`;
                
                previewHtml = `
                    <div class="p-4 bg-white rounded-lg border">
                        <div class="p-4 bg-indigo-50 rounded-lg">
                            <h5 class="font-medium text-gray-800 mb-2">${type === 'puzzle' ? 'Головоломка' : 'Викторина'}:</h5>
                            <p class="text-sm text-gray-700 mb-4">${oldContent('content_question', 'Вопрос будет здесь')}</p>
                            <div class="space-y-2">
                                <div class="p-2 bg-white border rounded hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center mr-2">A</div>
                                        <span class="text-sm">Вариант ответа 1</span>
                                    </div>
                                </div>
                                <div class="p-2 bg-white border rounded hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center mr-2">B</div>
                                        <span class="text-sm">Вариант ответа 2</span>
                                    </div>
                                </div>
                                <div class="p-2 bg-white border rounded hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center mr-2">C</div>
                                        <span class="text-sm">Вариант ответа 3</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                break;
        }
        
        container.innerHTML = html;
        preview.innerHTML = previewHtml;
    });
    
    // Вспомогательная функция для получения старых значений
    function oldContent(field, defaultValue = '') {
        // В реальном приложении здесь должна быть логика получения старых значений
        // Для демонстрации возвращаем defaultValue
        return defaultValue;
    }
    
    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        const taskType = document.getElementById('task-type');
        if (taskType.value) {
            taskType.dispatchEvent(new Event('change'));
        }
    });
    
    // Управление подсказками
    let hintCounter = 1;
    
    document.getElementById('add-hint').addEventListener('click', function() {
        const container = document.getElementById('hints-container');
        const html = `
            <div class="hint-item mb-4 p-4 border border-gray-200 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Текст подсказки *</label>
                        <input type="text" 
                               name="content_hints[${hintCounter}][text]" 
                               required
                               placeholder="Например: Ищите рядом с..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Тип подсказки</label>
                        <select name="content_hints[${hintCounter}][type]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="location">📍 Локация</option>
                            <option value="decryption">🔐 Расшифровка</option>
                            <option value="direct">🎯 Прямая</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Стоимость очков</label>
                        <input type="number" 
                               name="content_hints[${hintCounter}][points_cost]" 
                               value="10"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Доступна через (мин)</label>
                        <input type="number" 
                               name="content_hints[${hintCounter}][available_after_minutes]" 
                               value="5"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <button type="button" class="remove-hint text-red-600 text-sm hover:text-red-800">
                    <i class="fas fa-trash mr-1"></i> Удалить подсказку
                </button>
            </div>`;
        
        container.insertAdjacentHTML('beforeend', html);
        hintCounter++;
    });
    
    // Удаление подсказки
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-hint') || e.target.closest('.remove-hint')) {
            const hintItem = e.target.closest('.hint-item');
            if (hintItem) {
                hintItem.remove();
            }
        }
    });
</script>
@endpush
@endsection