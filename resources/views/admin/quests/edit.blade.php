@extends('layouts.admin')

@section('title', 'Редактирование квеста: ' . $quest->title)
@section('page-title', 'Редактирование квеста')
@section('page-subtitle', $quest->title)

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <form action="{{ route('admin.quests.update', $quest) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Левая колонка -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Основная информация -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-3"></i>Основная информация
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Название квеста -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Название квеста *
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $quest->title) }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Введите название квеста">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Краткое описание -->
                        <div>
                            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Краткое описание
                            </label>
                            <textarea id="short_description" 
                                      name="short_description" 
                                      rows="2"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Краткое описание для карточек и списков">{{ old('short_description', $quest->short_description) }}</textarea>
                            @error('short_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Полное описание -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Полное описание *
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="6"
                                      required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Подробное описание квеста, условия выполнения, советы...">{{ old('description', $quest->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Параметры квеста -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-cog text-green-500 mr-3"></i>Параметры квеста
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Тип квеста -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Тип квеста *
                            </label>
                            <select id="type" 
                                    name="type" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите тип</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $quest->type) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Сложность -->
                        <div>
                            <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                                Сложность *
                            </label>
                            <select id="difficulty" 
                                    name="difficulty" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите сложность</option>
                                @foreach($difficulties as $value => $label)
                                    <option value="{{ $value }}" {{ old('difficulty', $quest->difficulty) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('difficulty')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Минимальный уровень -->
                        <div>
                            <label for="min_level" class="block text-sm font-medium text-gray-700 mb-2">
                                Минимальный уровень *
                            </label>
                            <input type="number" 
                                   id="min_level" 
                                   name="min_level" 
                                   value="{{ old('min_level', $quest->min_level) }}"
                                   min="1" 
                                   max="100"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('min_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Значок -->
                        <div>
                            <label for="badge_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Значок (награда)
                            </label>
                            <select id="badge_id" 
                                    name="badge_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Без значка</option>
                                @foreach($badges as $badge)
                                    <option value="{{ $badge->id }}" {{ old('badge_id', $quest->badge_id) == $badge->id ? 'selected' : '' }}>
                                        {{ $badge->name }} ({{ $badge->rarity_label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('badge_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Маршруты квеста -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-route text-orange-500 mr-3"></i>Маршруты квеста
                    </h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Выберите маршруты для квеста *
                        </label>
                        <p class="text-sm text-gray-500 mb-4">Выберите маршруты, которые необходимо пройти для выполнения квеста. Порядок важен!</p>
                        
                        <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                            @forelse($routes->chunk(2) as $chunk)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    @foreach($chunk as $route)
                                        <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                            <input type="checkbox" 
                                                   id="route_{{ $route->id }}" 
                                                   name="routes[]" 
                                                   value="{{ $route->id }}"
                                                   {{ in_array($route->id, old('routes', $selectedRoutes)) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="route_{{ $route->id }}" class="ml-3 flex-1 cursor-pointer">
                                                <div class="font-medium text-gray-800">{{ $route->title }}</div>
                                                <div class="text-sm text-gray-600">
                                                    {{ $route->length_km }} км • {{ $route->difficulty_label }}
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <div class="text-gray-400 text-4xl mb-3">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <p class="text-gray-600">Нет доступных маршрутов</p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Сначала создайте маршруты в разделе "Маршруты"
                                    </p>
                                </div>
                            @endforelse
                        </div>
                        @error('routes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Текущие маршруты -->
                    @if($quest->routes->count() > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-medium text-gray-800 mb-3">Текущие маршруты квеста:</h4>
                            <div class="space-y-2">
                                @foreach($quest->routes as $route)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <div class="font-medium text-gray-800">{{ $route->title }}</div>
                                            <div class="text-sm text-gray-600">
                                                Порядок: {{ $loop->iteration }} • {{ $route->length_km }} км • {{ $route->difficulty_label }}
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.routes.show', $route) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Правая колонка -->
            <div class="space-y-6">
                <!-- Настройки квеста -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sliders-h text-purple-500 mr-3"></i>Настройки
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Награды -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-800">Награды</h4>
                            
                            <div>
                                <label for="reward_exp" class="block text-sm font-medium text-gray-700 mb-2">
                                    Опыт (EXP) *
                                </label>
                                <input type="number" 
                                       id="reward_exp" 
                                       name="reward_exp" 
                                       value="{{ old('reward_exp', $quest->reward_exp) }}"
                                       min="0"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('reward_exp')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="reward_coins" class="block text-sm font-medium text-gray-700 mb-2">
                                    Монеты *
                                </label>
                                <input type="number" 
                                       id="reward_coins" 
                                       name="reward_coins" 
                                       value="{{ old('reward_coins', $quest->reward_coins) }}"
                                       min="0"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('reward_coins')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Даты -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-800">Временные рамки</h4>
                            
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Дата начала (необязательно)
                                </label>
                                <input type="datetime-local" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date', $quest->start_date ? $quest->start_date->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Дата окончания (необязательно)
                                </label>
                                <input type="datetime-local" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="{{ old('end_date', $quest->end_date ? $quest->end_date->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Опции -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-800">Опции</h4>
                            
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $quest->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-3 text-sm text-gray-700">
                                    Активный квест (виден пользователям)
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="is_repeatable" 
                                       name="is_repeatable" 
                                       value="1"
                                       {{ old('is_repeatable', $quest->is_repeatable) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_repeatable" class="ml-3 text-sm text-gray-700">
                                    Повторяемый квест (можно проходить несколько раз)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Условия выполнения -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tasks text-yellow-500 mr-3"></i>Условия выполнения
                    </h3>
                    
                    <div>
                        <label for="conditions" class="block text-sm font-medium text-gray-700 mb-2">
                            Условия (JSON)
                        </label>
                        <textarea id="conditions" 
                                  name="conditions" 
                                  rows="6"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                  placeholder='[
    "Проехать все маршруты",
    "Сделать фото на каждой точке",
    "Отметить все чекпоинты"
]'>{{ old('conditions', $quest->conditions ? json_encode($quest->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            Укажите условия выполнения квеста в формате JSON массива строк
                        </p>
                        @error('conditions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Кнопки действий -->
                <div class="sticky top-6 space-y-3">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 rounded-lg font-bold text-lg transition duration-300">
                        <i class="fas fa-save mr-2"></i> Обновить квест
                    </button>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('admin.quests.show', $quest) }}" 
                           class="block bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium text-center transition duration-300">
                            <i class="fas fa-eye mr-2"></i> Просмотр
                        </a>
                        
                        <a href="{{ route('admin.quests.index') }}" 
                           class="block bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium text-center transition duration-300">
                            <i class="fas fa-times mr-2"></i> Отмена
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Валидация дат
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        if (endDateInput.value && new Date(this.value) > new Date(endDateInput.value)) {
            alert('Дата начала не может быть позже даты окончания!');
            this.value = endDateInput.value;
        }
    });
    
    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && new Date(this.value) < new Date(startDateInput.value)) {
            alert('Дата окончания не может быть раньше даты начала!');
            this.value = startDateInput.value;
        }
    });
    
    // Превью JSON
    const conditionsTextarea = document.getElementById('conditions');
    conditionsTextarea.addEventListener('input', function() {
        try {
            const json = JSON.parse(this.value);
            this.classList.remove('border-red-300');
            this.classList.add('border-gray-300');
        } catch (e) {
            this.classList.remove('border-gray-300');
            this.classList.add('border-red-300');
        }
    });
});
</script>
@endpush