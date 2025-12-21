@extends('layouts.app')

@section('title', 'Интерактивный квест - ' . $quest->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Хлебные крошки -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('quests.index') }}" class="text-blue-600 hover:text-blue-800">
                        Квесты
                    </a>
                </li>
                <li class="text-gray-500">›</li>
                <li>
                    <a href="{{ route('quests.show', $quest->slug) }}" class="text-blue-600 hover:text-blue-800">
                        {{ $quest->title }}
                    </a>
                </li>
                <li class="text-gray-500">›</li>
                <li class="text-gray-700 font-medium">Задание</li>
            </ol>
        </nav>
        
        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $quest->title }}</h1>
            <div class="flex items-center space-x-4 text-gray-600">
                <div class="flex items-center">
                    <i class="fas fa-tasks mr-2"></i>
                    <span>Задание {{ $completedTasks + 1 }} из {{ $totalTasks }}</span>
                </div>
                @if($timeRemaining)
                    <div class="flex items-center text-red-600">
                        <i class="fas fa-clock mr-2"></i>
                        <span>Осталось: {{ $timeRemaining }} мин</span>
                    </div>
                @endif
            </div>
            
            <!-- Прогресс-бар -->
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Прогресс квеста</span>
                    <span>{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Основное задание -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-lg {{ $task->type === 'cipher' ? 'bg-yellow-100' : 'bg-blue-100' }} flex items-center justify-center mr-4">
                            <i class="{{ $task->type === 'cipher' ? 'fas fa-key text-yellow-600' : 'fas fa-tasks text-blue-600' }} text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $task->title }}</h2>
                            @if($task->description)
                                <p class="text-gray-600 mt-1">{{ $task->description }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Содержание задания -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Задание:</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if($task->type === 'cipher')
                                <div class="mb-4">
                                    <p class="text-gray-700 mb-2">{{ $formattedContent['description'] ?? 'Расшифруйте текст:' }}</p>
                                    <div class="bg-gray-900 text-yellow-300 rounded-lg p-4 font-mono text-center text-lg">
                                        {{ $formattedContent['cipher_text'] ?? '' }}
                                    </div>
                                    @if(isset($formattedContent['hint']))
                                        <p class="text-sm text-gray-500 mt-2">
                                            <i class="fas fa-lightbulb mr-1"></i> {{ $formattedContent['hint'] }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Форма ответа -->
                            <form id="task-form" action="{{ route('quests.interactive.submit', [$quest->slug, $task->id]) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                                        Ваш ответ:
                                    </label>
                                    <input type="text" 
                                           id="answer" 
                                           name="answer" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Введите ответ..."
                                           required>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button type="submit" 
                                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить ответ
                                    </button>
                                    
                                    @if(!empty($hints))
                                        <button type="button" 
                                                id="hint-button"
                                                class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition">
                                            <i class="fas fa-lightbulb mr-2"></i>Получить подсказку
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Статистика задания -->
                    <div class="border-t pt-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ $task->points }}</div>
                                <div class="text-sm text-gray-600">Баллов за задание</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ $currentTaskProgress->attempts ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Попыток</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-purple-600">{{ $currentTaskProgress->hints_used ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Подсказок использовано</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Боковая панель -->
            <div class="space-y-6">
                <!-- Чат квеста -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-comments mr-2"></i>Чат квеста
                    </h3>
                    
                    <div class="h-64 overflow-y-auto mb-4 space-y-3">
                        @forelse($chatMessages as $message)
                            <div class="{{ $message->user_id === auth()->id() ? 'text-right' : '' }}">
                                <div class="text-xs text-gray-500">
                                    {{ $message->user->name }} • {{ $message->created_at->format('H:i') }}
                                </div>
                                <div class="inline-block {{ $message->user_id === auth()->id() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }} rounded-lg px-3 py-2 mt-1">
                                    {{ $message->content }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">Пока нет сообщений</p>
                        @endforelse
                    </div>
                    
                    <form action="#" method="POST" class="flex">
                        <input type="text" 
                               name="message" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Написать сообщение...">
                        <button type="submit" 
                                class="px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('quests.interactive.chat', $quest->slug) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>Открыть полный чат
                        </a>
                    </div>
                </div>
                
                <!-- Управление квестом -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Управление квестом</h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('quests.interactive.pause', $quest->slug) }}"
                           class="block w-full px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-center">
                            <i class="fas fa-pause mr-2"></i>Приостановить квест
                        </a>
                        
                        <a href="{{ route('quests.show', $quest->slug) }}"
                           class="block w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-center">
                            <i class="fas fa-arrow-left mr-2"></i>Назад к квесту
                        </a>
                        
                        <button type="button" 
                                onclick="if(confirm('Завершить квест досрочно?')) window.location='{{ route('quests.interactive.complete', $quest->slug) }}'"
                                class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                            <i class="fas fa-flag-checkered mr-2"></i>Завершить квест
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка формы
    const form = document.getElementById('task-form');
    const hintButton = document.getElementById('hint-button');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Проверка...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.is_correct) {
                        showNotification('success', 'Правильный ответ!');
                        if (data.next_task) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            setTimeout(() => {
                                window.location.href = '{{ route("quests.show", $quest->slug) }}';
                            }, 1500);
                        }
                    } else {
                        showNotification('error', 'Неправильный ответ. Попробуйте ещё раз.');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } else {
                    showNotification('error', data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Ошибка при отправке ответа');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Подсказки
    if (hintButton) {
        hintButton.addEventListener('click', function() {
            const hintIndex = this.dataset.hintIndex || 0;
            
            fetch('{{ route("quests.interactive.hint", [$quest->slug, $task->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ hint_index: hintIndex })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('info', 'Подсказка: ' + data.hint.text);
                    this.dataset.hintIndex = parseInt(hintIndex) + 1;
                    
                    if (data.hints_used >= {{ count($hints ?? []) }}) {
                        this.disabled = true;
                        this.innerHTML = '<i class="fas fa-lightbulb mr-2"></i>Подсказки закончились';
                    }
                } else {
                    showNotification('warning', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Ошибка при получении подсказки');
            });
        });
    }
    
    // Таймер (если есть ограничение по времени)
    @if($timeRemaining)
        let timeLeft = {{ $timeRemaining * 60 }}; // в секундах
        
        const timerInterval = setInterval(() => {
            timeLeft--;
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                showNotification('warning', 'Время вышло!');
                // Автоматическая отправка
                document.getElementById('task-form').submit();
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            const timerElement = document.querySelector('.time-remaining');
            if (timerElement) {
                timerElement.textContent = `Осталось: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    @endif
    
    function showNotification(type, message) {
        // Простая реализация уведомлений
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
});
</script>
@endpush
@endsection