@extends('layouts.admin')

@section('title', 'Задания квеста')
@section('page-title', 'Задания квеста')
@section('page-subtitle', 'Управление заданиями')

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
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Задания: {{ $quest->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Информация о квесте -->
    <div class="mb-8 p-6 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">{{ $quest->title }}</h2>
                <p class="text-blue-100">{{ $quest->short_description ?? Str::limit($quest->description, 150) }}</p>
                <div class="flex items-center mt-3 space-x-3">
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
                        {{ $quest->type_label }}
                    </span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
                        {{ $quest->difficulty_label }}
                    </span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
                        {{ $tasks->count() }} заданий
                    </span>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="text-right">
                    <p class="text-xl font-bold">{{ $quest->reward_exp }} EXP</p>
                    <p class="text-blue-100">Награда за прохождение</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Заголовок и кнопки -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Список заданий</h3>
            <p class="text-gray-600">Управление заданиями квеста</p>
        </div>
        
        <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
            <a href="{{ route('admin.quests.tasks.create', $quest) }}" 
               class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Добавить задание
            </a>
            <a href="{{ route('admin.quests.edit', $quest) }}" 
               class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center">
                <i class="fas fa-edit mr-2"></i> Редактировать квест
            </a>
        </div>
    </div>

    @if($tasks->isEmpty())
    <!-- Если заданий нет -->
    <div class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">
            <i class="fas fa-tasks"></i>
        </div>
        <h3 class="text-xl font-medium text-gray-600 mb-2">Задания не найдены</h3>
        <p class="text-gray-500 mb-6">Создайте первое задание для этого квеста</p>
        <a href="{{ route('admin.quests.tasks.create', $quest) }}" 
           class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-bold inline-block">
            <i class="fas fa-plus mr-2"></i> Создать задание
        </a>
    </div>
    @else
    <!-- Список заданий -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Задание
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Тип
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Параметры
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статус
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                {{ $task->order }}
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $task->title }}</div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">
                                    {{ $task->description ?: 'Описание отсутствует' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $typeIcons = [
                                'text' => 'fas fa-file-alt text-blue-500',
                                'image' => 'fas fa-image text-green-500',
                                'code' => 'fas fa-code text-purple-500',
                                'cipher' => 'fas fa-lock text-red-500',
                                'location' => 'fas fa-map-marker-alt text-yellow-500',
                                'puzzle' => 'fas fa-puzzle-piece text-indigo-500',
                                'quiz' => 'fas fa-question-circle text-pink-500',
                            ];
                        @endphp
                        <div class="flex items-center">
                            <i class="{{ $typeIcons[$task->type] ?? 'fas fa-question' }} mr-2"></i>
                            <span class="text-sm font-medium">{{ $task->type }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-1">
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-500 mr-1 text-sm"></i>
                                <span class="text-sm">{{ $task->points }} очков</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock text-gray-500 mr-1 text-sm"></i>
                                <span class="text-sm">{{ $task->time_limit_minutes }} мин.</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col space-y-1">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 inline-block w-24 text-center">
                                {{ $task->is_required ? 'Обязательное' : 'Дополнительное' }}
                            </span>
                            @if($task->next_task_id)
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 inline-block w-20 text-center">
                                    Связное
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.quests.tasks.edit', [$quest, $task]) }}" 
                               class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                               title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="previewTask({{ $task->id }})"
                                    class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" 
                                    title="Предпросмотр">
                                <i class="fas fa-eye"></i>
                            </button>
                            <form action="{{ route('admin.quests.tasks.destroy', [$quest, $task]) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Удалить задание? Это действие нельзя отменить.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" 
                                        title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Подсказка по перетаскиванию -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-3 text-xl"></i>
            <div>
                <p class="text-sm text-blue-800">
                    <strong>Совет:</strong> Вы можете менять порядок заданий с помощью перетаскивания. 
                    Порядок заданий важен для последовательного прохождения квеста.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function previewTask(taskId) {
        // Реализация предпросмотра задания
        alert('Предпросмотр задания #' + taskId);
    }
</script>
@endpush
@endsection