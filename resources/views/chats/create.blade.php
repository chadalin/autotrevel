@extends('layouts.app')

@section('title', 'Создать чат')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Создать новый чат</h1>
            <p class="text-gray-600 mt-2">Выберите тип чата и участников</p>
        </div>

        <!-- Навигация по типам чатов -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('private')" 
                            id="private-tab"
                            class="py-4 px-1 border-b-2 font-medium text-sm tab-button active"
                            data-tab="private">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Личный чат
                        </span>
                    </button>
                    <button onclick="showTab('group')" 
                            id="group-tab"
                            class="py-4 px-1 border-b-2 font-medium text-sm tab-button"
                            data-tab="group">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            Групповой чат
                        </span>
                    </button>
                    @if(isset($route))
                        <button onclick="showTab('route')" 
                                id="route-tab"
                                class="py-4 px-1 border-b-2 font-medium text-sm tab-button"
                                data-tab="route">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                </svg>
                                Чат маршрута
                            </span>
                        </button>
                    @endif
                </nav>
            </div>
        </div>

        <!-- Формы -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <!-- Форма приватного чата -->
            <form id="private-form" action="{{ route('chats.store') }}" method="POST" class="tab-content active">
                @csrf
                <input type="hidden" name="type" value="private">
                
                @if(isset($user))
                    <!-- Если передан конкретный пользователь -->
                    <div class="mb-6">
                        <div class="flex items-center gap-4 p-4 bg-blue-50 rounded-lg">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-12 h-12 rounded-full">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-600">Будет создан приватный чат с этим пользователем</p>
                            </div>
                        </div>
                        <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                    </div>
                @else
                    <!-- Выбор пользователя -->
                    <div class="mb-6">
                        <label for="private-user" class="block text-sm font-medium text-gray-700 mb-2">
                            Выберите пользователя
                        </label>
                        <select id="private-user" 
                                name="user_ids[]" 
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                            <option value="">-- Выберите пользователя --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('user_ids.0') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="flex justify-end gap-3">
                    <a href="{{ route('chats.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Создать чат
                    </button>
                </div>
            </form>

            <!-- Форма группового чата -->
       <!-- Форма группового чата -->
<form id="group-form" action="{{ route('chats.store') }}" method="POST" class="tab-content hidden">
    @csrf
    <input type="hidden" name="type" value="group">
    
    <!-- Название группы -->
    <div class="mb-6">
        <label for="group-name" class="block text-sm font-medium text-gray-700 mb-2">
            Название группы <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="group-name"
               name="name" 
               value="{{ old('name') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Например: 'Друзья на велопрогулку'"
               required>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Выбор участников -->
    <div class="mb-6">
        <label for="group-members" class="block text-sm font-medium text-gray-700 mb-2">
            Выберите участников <span class="text-red-500">*</span>
        </label>
        <div class="bg-gray-50 rounded-lg p-4 max-h-60 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($users as $u)
                    @if($u->id !== auth()->id())
                        <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 cursor-pointer group-member"
                             onclick="toggleMemberSelection({{ $u->id }})">
                            <input type="checkbox" 
                                   id="member-{{ $u->id }}"
                                   name="member_ids[]" 
                                   value="{{ $u->id }}"
                                   class="hidden member-checkbox"
                                   {{ in_array($u->id, old('member_ids', [])) ? 'checked' : '' }}>
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center member-selector">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $u->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $u->email }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @error('member_ids')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-sm text-gray-500">
            Выбрано: <span id="selected-count">0</span> участников
        </p>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('chats.index') }}" 
           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
            Отмена
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Создать группу
        </button>
    </div>
</form>

            <!-- Форма чата маршрута -->
            @if(isset($route))
                <form id="route-form" action="{{ route('chats.store') }}" method="POST" class="tab-content hidden">
                    @csrf
                    <input type="hidden" name="type" value="route">
                    <input type="hidden" name="route_id" value="{{ $route->id }}">
                    
                    <!-- Информация о маршруте -->
                    <div class="mb-6">
                        <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg">
                            @if($route->cover_image)
                                <img src="{{ asset('storage/' . $route->cover_image) }}" 
                                     alt="{{ $route->title }}" 
                                     class="w-16 h-16 rounded-lg object-cover">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $route->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($route->description, 150) }}</p>
                                <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $route->duration_minutes }} мин
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $route->length_km }} км
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Описание -->
                    <div class="mb-6">
                        <p class="text-gray-700">
                            Будет создано обсуждение для маршрута <strong>{{ $route->title }}</strong>. 
                            Все участники, присоединившиеся к маршруту, смогут участвовать в обсуждении.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('routes.show', $route) }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                            Отмена
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Создать обсуждение
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .tab-button {
        border-color: transparent;
        color: #6b7280;
    }
    
    .tab-button.active {
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .member-selector {
        transition: all 0.2s;
    }
    
    .group-member.selected .member-selector {
        background-color: #3b82f6;
        color: white;
    }
    
    .group-member.selected .member-selector svg {
        display: block;
    }
    
    .group-member .member-selector svg {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // Переключение между вкладками
    function showTab(tabName) {
        // Скрыть все вкладки
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.classList.add('hidden');
        });
        
        // Показать выбранную вкладку
        const activeTab = document.getElementById(`${tabName}-form`);
        if (activeTab) {
            activeTab.classList.remove('hidden');
            activeTab.classList.add('active');
        }
        
        // Обновить активную кнопку
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        const activeButton = document.getElementById(`${tabName}-tab`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
    }
    
    // Выбор участников группы
    function toggleMemberSelection(userId) {
        const member = document.querySelector(`[onclick="toggleMemberSelection(${userId})"]`);
        const checkbox = document.getElementById(`member-${userId}`);
        
        if (member && checkbox) {
            member.classList.toggle('selected');
            checkbox.checked = !checkbox.checked;
            updateSelectedCount();
        }
    }
    
    // Обновление счетчика выбранных участников
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.member-checkbox:checked');
        document.getElementById('selected-count').textContent = checkboxes.length;
    }
    
    // Инициализация выбранных участников при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        // Пометить предварительно выбранных участников
        document.querySelectorAll('.member-checkbox:checked').forEach(checkbox => {
            const member = checkbox.closest('.group-member');
            if (member) {
                member.classList.add('selected');
            }
        });
        
        updateSelectedCount();
        
        // Если передан пользователь для приватного чата, показать эту вкладку
        @if(isset($user))
            showTab('private');
        @elseif(isset($route))
            showTab('route');
        @endif
    });
</script>
@endpush
@endsection