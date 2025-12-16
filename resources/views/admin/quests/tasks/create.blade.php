@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Создание задания для квеста: {{ $quest->title }}</h3>
                </div>
                <form action="{{ route('admin.quests.tasks.store', $quest) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Название задания *</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Тип задания *</label>
                                    <select name="type" class="form-control" id="task-type" required>
                                        <option value="text">Текст</option>
                                        <option value="image">Изображение</option>
                                        <option value="code">Код</option>
                                        <option value="cipher">Шифр</option>
                                        <option value="location">Локация</option>
                                        <option value="puzzle">Головоломка</option>
                                        <option value="quiz">Викторина</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Порядок *</label>
                                    <input type="number" name="order" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Описание</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Типозависимые поля -->
                        <div id="type-specific-fields">
                            <!-- Поля появятся через JS в зависимости от типа -->
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Очки *</label>
                                    <input type="number" name="points" class="form-control" min="1" value="10" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Лимит времени (мин) *</label>
                                    <input type="number" name="time_limit_minutes" class="form-control" min="1" value="15" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Подсказки доступны</label>
                                    <input type="number" name="hints_available" class="form-control" min="0" value="3">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Следующее задание</label>
                                    <select name="next_task_id" class="form-control">
                                        <option value="">— Выберите —</option>
                                        @foreach($nextTasks as $nextTask)
                                            <option value="{{ $nextTask->id }}">{{ $nextTask->order }}. {{ $nextTask->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Правильный ответ</label>
                                    <input type="text" name="required_answer" class="form-control">
                                    <small class="text-muted">Оставьте пустым, если не требуется</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Локация (для location-заданий)</label>
                                    <select name="location_id" class="form-control">
                                        <option value="">— Выберите —</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Подсказки -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h4 class="card-title">Подсказки</h4>
                            </div>
                            <div class="card-body">
                                <div id="hints-container">
                                    <div class="hint-item row mb-3">
                                        <div class="col-md-4">
                                            <input type="text" name="content_hints[0][text]" class="form-control" placeholder="Текст подсказки">
                                        </div>
                                        <div class="col-md-3">
                                            <select name="content_hints[0][type]" class="form-control">
                                                <option value="location">Локация</option>
                                                <option value="decryption">Расшифровка</option>
                                                <option value="direct">Прямая</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="content_hints[0][points_cost]" class="form-control" placeholder="Стоимость" value="10">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="content_hints[0][available_after_minutes]" class="form-control" placeholder="Доступна через (мин)" value="5">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger remove-hint">×</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-hint" class="btn btn-sm btn-secondary">+ Добавить подсказку</button>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input type="checkbox" name="is_required" class="form-check-input" id="is_required" checked>
                            <label class="form-check-label" for="is_required">Обязательное задание</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Создать задание</button>
                        <a href="{{ route('admin.quests.tasks.index', $quest) }}" class="btn btn-default">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Динамическое отображение полей в зависимости от типа задания
    document.getElementById('task-type').addEventListener('change', function() {
        const type = this.value;
        const container = document.getElementById('type-specific-fields');
        
        let html = '';
        
        switch(type) {
            case 'text':
                html = `
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Текст задания</label>
                                <textarea name="content_text" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Цвет фона</label>
                                <input type="color" name="content_background" class="form-control" value="#f8f9fa">
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'image':
                html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Изображение</label>
                                <input type="file" name="content_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Подпись к изображению</label>
                                <input type="text" name="content_caption" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Вопрос к изображению</label>
                                <textarea name="content_question" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'code':
                html = `
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Описание поиска кода</label>
                                <textarea name="content_description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Формат кода</label>
                                <select name="content_format" class="form-control">
                                    <option value="numeric">Цифровой</option>
                                    <option value="alphanumeric">Буквенно-цифровой</option>
                                    <option value="letters">Буквенный</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Длина кода</label>
                                <input type="number" name="content_length" class="form-control" value="4" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Подсказка для поиска</label>
                                <input type="text" name="content_hint" class="form-control">
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'cipher':
                html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Зашифрованный текст</label>
                                <textarea name="content_cipher_text" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Тип шифра</label>
                                <select name="content_cipher_type" class="form-control">
                                    <option value="caesar">Шифр Цезаря</option>
                                    <option value="atbash">Атбаш</option>
                                    <option value="morse">Азбука Морзе</option>
                                    <option value="substitution">Подстановочный</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Ключ шифра</label>
                                <input type="text" name="content_key" class="form-control">
                                <small class="text-muted">Например: 3 для шифра Цезаря</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Описание задания</label>
                                <input type="text" name="content_description" class="form-control" value="Расшифруйте текст">
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'location':
                html = `
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Широта</label>
                                <input type="text" name="content_coordinates_lat" class="form-control" placeholder="55.7558">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Долгота</label>
                                <input type="text" name="content_coordinates_lng" class="form-control" placeholder="37.6173">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Радиус (метры)</label>
                                <input type="number" name="content_radius" class="form-control" value="50" min="10">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Вопрос/задание</label>
                                <input type="text" name="content_question" class="form-control" value="Доберитесь до указанной точки">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Намек/указание</label>
                                <input type="text" name="content_clue" class="form-control">
                            </div>
                        </div>
                    </div>`;
                break;
                
            case 'puzzle':
                html = `
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Тип головоломки</label>
                                <select name="content_puzzle_type" class="form-control">
                                    <option value="riddle">Загадка</option>
                                    <option value="logic">Логическая</option>
                                    <option value="math">Математическая</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Вопрос головоломки</label>
                                <textarea name="content_question" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Варианты ответов (по одному на строку)</label>
                                <textarea name="content_options[]" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Индекс правильного ответа</label>
                                <input type="number" name="content_correct_index" class="form-control" value="0" min="0">
                                <small class="text-muted">Нумерация с 0</small>
                            </div>
                        </div>
                    </div>`;
                break;
        }
        
        container.innerHTML = html;
    });

    // Управление подсказками
    let hintCounter = 1;
    
    document.getElementById('add-hint').addEventListener('click', function() {
        const container = document.getElementById('hints-container');
        const html = `
            <div class="hint-item row mb-3">
                <div class="col-md-4">
                    <input type="text" name="content_hints[${hintCounter}][text]" class="form-control" placeholder="Текст подсказки">
                </div>
                <div class="col-md-3">
                    <select name="content_hints[${hintCounter}][type]" class="form-control">
                        <option value="location">Локация</option>
                        <option value="decryption">Расшифровка</option>
                        <option value="direct">Прямая</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="content_hints[${hintCounter}][points_cost]" class="form-control" placeholder="Стоимость" value="10">
                </div>
                <div class="col-md-2">
                    <input type="number" name="content_hints[${hintCounter}][available_after_minutes]" class="form-control" placeholder="Доступна через (мин)" value="5">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-hint">×</button>
                </div>
            </div>`;
        
        container.insertAdjacentHTML('beforeend', html);
        hintCounter++;
    });

    // Удаление подсказки
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-hint')) {
            e.target.closest('.hint-item').remove();
        }
    });

    // Инициализация полей для первого типа
    document.getElementById('task-type').dispatchEvent(new Event('change'));
</script>
@endpush