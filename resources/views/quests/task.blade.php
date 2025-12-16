@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <!-- Таймер -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Таймер</h5>
                        <div id="timer" class="badge bg-danger fs-5">00:00</div>
                    </div>
                    <div class="progress mt-2" style="height: 5px;">
                        <div id="timer-progress" class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <!-- Задание -->
            <div class="card">
                <div class="card-header">
                    <h4>{{ $task->title }}</h4>
                    <small class="text-muted">Задание {{ $task->order }} из {{ $quest->tasks()->count() }}</small>
                </div>
                <div class="card-body">
                    @if($task->description)
                        <p class="lead">{{ $task->description }}</p>
                    @endif

                    <!-- Контент задания -->
                    @switch($task->type)
                        @case('text')
                            <div class="p-3 rounded" style="background: {{ $taskData['background'] ?? '#f8f9fa' }}">
                                <p class="mb-0">{{ $taskData['text'] }}</p>
                            </div>
                            @break

                        @case('image')
                            <div class="text-center mb-3">
                                <img src="{{ $taskData['image_url'] }}" alt="Задание" class="img-fluid rounded">
                                @if($taskData['caption'])
                                    <p class="text-muted mt-2">{{ $taskData['caption'] }}</p>
                                @endif
                            </div>
                            @if($taskData['question'])
                                <div class="alert alert-info">
                                    <strong>Вопрос:</strong> {{ $taskData['question'] }}
                                </div>
                            @endif
                            @break

                        @case('code')
                            <div class="alert alert-secondary">
                                <h5>Найдите код:</h5>
                                <p>{{ $taskData['description'] }}</p>
                                <p><strong>Формат:</strong> {{ $taskData['code_format'] }}, 
                                   <strong>Длина:</strong> {{ $taskData['length'] }} символов</p>
                                @if($taskData['hint'])
                                    <p><small class="text-muted">Подсказка: {{ $taskData['hint'] }}</small></p>
                                @endif
                            </div>
                            @break

                        @case('cipher')
                            <div class="alert alert-warning">
                                <h5>Шифр:</h5>
                                <div class="cipher-text mb-3 p-3 bg-dark text-light rounded">
                                    <code>{{ $taskData['cipher_text'] }}</code>
                                </div>
                                <p>{{ $taskData['description'] }}</p>
                                <p><strong>Тип шифра:</strong> {{ $taskData['cipher_type'] }}</p>
                            </div>
                            @break

                        @case('location')
                            <div class="alert alert-success">
                                <h5>Локация:</h5>
                                <p>{{ $taskData['question'] }}</p>
                                @if($taskData['clue'])
                                    <p><small>Подсказка: {{ $taskData['clue'] }}</small></p>
                                @endif
                                <!-- Здесь будет карта -->
                                <div id="map" style="height: 300px;" class="rounded"></div>
                            </div>
                            @break

                        @case('puzzle')
                            <div class="alert alert-primary">
                                <h5>Головоломка:</h5>
                                <p>{{ $taskData['question'] }}</p>
                                @if(!empty($taskData['options']))
                                    <div class="puzzle-options mt-3">
                                        @foreach($taskData['options'] as $index => $option)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" 
                                                       name="puzzle_option" value="{{ $index }}" 
                                                       id="option{{ $index }}">
                                                <label class="form-check-label" for="option{{ $index }}">
                                                    {{ $option }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @break
                    @endswitch

                    <!-- Форма ответа -->
                    <form id="answer-form" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="answer" class="form-label">Ваш ответ:</label>
                            <input type="text" class="form-control" id="answer" 
                                   name="answer" placeholder="Введите ответ..." required>
                            <div class="form-text">Будьте внимательны, ответ чувствителен к регистру!</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить ответ</button>
                        <button type="button" id="skip-btn" class="btn btn-outline-warning">Пропустить</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Информация -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Информация</h5>
                </div>
                <div class="card-body">
                    <p><strong>Очки:</strong> {{ $task->points }}</p>
                    <p><strong>Лимит времени:</strong> {{ $task->time_limit_minutes }} мин.</p>
                    <p><strong>Доступно подсказок:</strong> {{ $task->hints_available }}</p>
                    <p><strong>Попытки:</strong> {{ $progress->attempts }}</p>
                </div>
            </div>

            <!-- Подсказки -->
            <div class="card">
                <div class="card-header">
                    <h5>Подсказки</h5>
                </div>
                <div class="card-body">
                    @foreach($task->getHints() as $index => $hint)
                        <div class="hint-item mb-2">
                            <button class="btn btn-sm btn-outline-info use-hint-btn" 
                                    data-hint-index="{{ $index }}"
                                    data-points-cost="{{ $hint['points_cost'] ?? 0 }}">
                                Подсказка {{ $index + 1 }}
                                @if($hint['points_cost'] ?? 0)
                                    <span class="badge bg-warning">-{{ $hint['points_cost'] }} очков</span>
                                @endif
                            </button>
                            <small class="text-muted d-block">
                                Доступна через {{ $hint['available_after_minutes'] ?? 0 }} мин.
                            </small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Таймер
    let timeLeft = {{ $task->time_limit_minutes * 60 }};
    let totalTime = timeLeft;
    const timerElement = document.getElementById('timer');
    const timerProgress = document.getElementById('timer-progress');
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        const progress = (timeLeft / totalTime) * 100;
        timerProgress.style.width = `${progress}%`;
        
        if (progress < 20) {
            timerProgress.className = 'progress-bar bg-danger';
        } else if (progress < 50) {
            timerProgress.className = 'progress-bar bg-warning';
        }
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            alert('Время вышло! Задание будет пропущено.');
            skipTask();
        }
        
        timeLeft--;
    }
    
    const timerInterval = setInterval(updateTimer, 1000);
    
    // Отправка ответа
    document.getElementById('answer-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const response = await fetch('{{ route("quest.task.submit", [$quest, $task]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            if (result.next_task_url) {
                window.location.href = result.next_task_url;
            } else {
                window.location.href = '{{ route("quest.show", $quest) }}';
            }
        } else {
            alert(result.message);
        }
    });
    
    // Использование подсказки
    document.querySelectorAll('.use-hint-btn').forEach(button => {
        button.addEventListener('click', async function() {
            if (!confirm(`Использовать подсказку? Вы потеряете ${this.dataset.pointsCost} очков.`)) {
                return;
            }
            
            const response = await fetch('{{ route("quest.task.hint", [$quest, $task]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    hint_index: this.dataset.hintIndex
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.hint);
                this.disabled = true;
                this.classList.remove('btn-outline-info');
                this.classList.add('btn-secondary');
            } else {
                alert(result.error || 'Ошибка использования подсказки');
            }
        });
    });
    
    // Пропуск задания
    document.getElementById('skip-btn').addEventListener('click', function() {
        if (confirm('Пропустить задание? Вы потеряете половину очков.')) {
            skipTask();
        }
    });
    
    function skipTask() {
        fetch('{{ route("quest.task.skip", [$quest, $task]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            window.location.href = '{{ route("quest.show", $quest) }}';
        });
    }
    
    // Инициализация карты для location-заданий
    @if($task->type === 'location' && isset($taskData['coordinates']))
        function initMap() {
            const map = L.map('map').setView([
                {{ $taskData['coordinates']['lat'] }},
                {{ $taskData['coordinates']['lng'] }}
            ], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            
            L.marker([
                {{ $taskData['coordinates']['lat'] }},
                {{ $taskData['coordinates']['lng'] }}
            ]).addTo(map)
              .bindPopup('Целевая точка')
              .openPopup();
            
            // Круг радиуса
            L.circle([
                {{ $taskData['coordinates']['lat'] }},
                {{ $taskData['coordinates']['lng'] }}
            ], {
                color: 'green',
                fillColor: '#0f0',
                fillOpacity: 0.2,
                radius: {{ $taskData['radius'] ?? 50 }}
            }).addTo(map);
        }
        
        // Загружаем Leaflet CSS и JS
        const leafletCSS = document.createElement('link');
        leafletCSS.rel = 'stylesheet';
        leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(leafletCSS);
        
        const leafletJS = document.createElement('script');
        leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        leafletJS.onload = initMap;
        document.body.appendChild(leafletJS);
    @endif
</script>
@endpush