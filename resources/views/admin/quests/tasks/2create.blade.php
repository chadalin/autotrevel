@extends('layouts.admin-tasks')

@section('title', 'Создание задания')
@section('page-title', 'Создание задания')
@section('page-subtitle', 'Добавление нового задания в квест')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle me-2"></i>Создание задания для квеста: "{{ $quest->title }}"
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.quests.tasks.store', $quest) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Название задания <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               placeholder="Введите название задания">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание задания</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Опишите задание..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Тип задания <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Выберите тип</option>
                            <option value="text">📝 Текст</option>
                            <option value="image">🖼️ Изображение</option>
                            <option value="code">🔢 Код</option>
                            <option value="cipher">🔐 Шифр</option>
                            <option value="location">📍 Локация</option>
                            <option value="puzzle">🧩 Головоломка</option>
                            <option value="quiz">❓ Викторина</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order" class="form-label">Порядок <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="order" name="order" required 
                               min="1" value="{{ $nextTasks->count() + 1 }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="points" class="form-label">Очки <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="points" name="points" required 
                               min="1" value="10">
                    </div>
                    
                    <div class="mb-3">
                        <label for="time_limit_minutes" class="form-label">Лимит времени (минут) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="time_limit_minutes" name="time_limit_minutes" 
                               required min="1" value="15">
                    </div>
                    
                    <div class="mb-3">
                        <label for="hints_available" class="form-label">Доступные подсказки</label>
                        <input type="number" class="form-control" id="hints_available" name="hints_available" 
                               min="0" value="3">
                    </div>
                    
                    <div class="mb-3">
                        <label for="required_answer" class="form-label">Правильный ответ</label>
                        <input type="text" class="form-control" id="required_answer" name="required_answer" 
                               placeholder="Ответ для проверки">
                    </div>
                    
                    <div class="mb-3">
                        <label for="next_task_id" class="form-label">Следующее задание</label>
                        <select class="form-select" id="next_task_id" name="next_task_id">
                            <option value="">Автоматически по порядку</option>
                            @foreach($nextTasks as $task)
                                <option value="{{ $task->id }}">{{ $task->order }}. {{ $task->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location_id" class="form-label">Привязать к локации</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <option value="">Не привязывать</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->title }} ({{ $location->type }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" checked>
                        <label class="form-check-label" for="is_required">
                            Обязательное задание
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Поля для контента (будут динамически меняться) -->
            <div id="type-specific-fields" class="border-top pt-4 mt-4">
                <h6 class="text-muted">Выберите тип задания для настройки контента</h6>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Создать задание
                </button>
                <a href="{{ route('admin.quests.tasks.index', $quest) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const container = document.getElementById('type-specific-fields');
        
        let html = '';
        
        switch(type) {
            case 'text':
                html = `
                    <h6><i class="fas fa-file-alt me-2"></i>Настройки текстового задания</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Текст задания</label>
                                <textarea name="content_text" class="form-control" rows="4" 
                                          placeholder="Введите текст задания..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Цвет фона</label>
                                <input type="color" name="content_background" class="form-control form-control-color" value="#f8f9fa">
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'image':
                html = `
                    <h6><i class="fas fa-image me-2"></i>Настройки задания с изображением</h6>
                    <div class="mb-3">
                        <label class="form-label">Изображение</label>
                        <input type="file" name="content_image" class="form-control" accept="image/*">
                        <small class="text-muted">Рекомендуемый размер: 1200x800px</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подпись к изображению</label>
                        <input type="text" name="content_caption" class="form-control" placeholder="Описание изображения...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Вопрос к изображению</label>
                        <textarea name="content_question" class="form-control" rows="2" 
                                  placeholder="Что нужно сделать с изображением?"></textarea>
                    </div>`;
                break;
                
            case 'code':
                html = `
                    <h6><i class="fas fa-code me-2"></i>Настройки задания с кодом</h6>
                    <div class="mb-3">
                        <label class="form-label">Описание поиска кода</label>
                        <textarea name="content_description" class="form-control" rows="3" 
                                  placeholder="Опишите где искать код..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Формат кода</label>
                                <select name="content_format" class="form-select">
                                    <option value="numeric">Цифровой (1234)</option>
                                    <option value="alphanumeric">Буквенно-цифровой (A1B2)</option>
                                    <option value="letters">Буквенный (ABCD)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Длина кода</label>
                                <input type="number" name="content_length" class="form-control" min="1" value="4">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подсказка для поиска</label>
                        <input type="text" name="content_hint" class="form-control" placeholder="Намек где искать код...">
                    </div>`;
                break;
                
            case 'location':
                html = `
                    <h6><i class="fas fa-map-marker-alt me-2"></i>Настройки локационного задания</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Широта (lat)</label>
                                <input type="text" name="content_coordinates_lat" class="form-control" placeholder="55.7558">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Долгота (lng)</label>
                                <input type="text" name="content_coordinates_lng" class="form-control" placeholder="37.6173">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Радиус (метры)</label>
                        <input type="number" name="content_radius" class="form-control" min="10" value="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Вопрос/задание</label>
                        <input type="text" name="content_question" class="form-control" value="Доберитесь до указанной точки">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Намек/указание</label>
                        <input type="text" name="content_clue" class="form-control" placeholder="Намек где находится точка...">
                    </div>`;
                break;
        }
        
        container.innerHTML = html;
    });
</script>
@endpush
@endsection