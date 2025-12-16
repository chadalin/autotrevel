@extends('layouts.app')

@section('title', $chat->name ?? 'Чат')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Заголовок чата -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($chat->type === 'private')
                        @php
                            $otherUser = $chat->users->where('id', '!=', auth()->id())->first();
                        @endphp
                        <img src="{{ $otherUser->avatar_url ?? asset('images/default-avatar.png') }}" 
                             alt="{{ $otherUser->name }}" 
                             class="w-12 h-12 rounded-full">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $otherUser->name }}</h1>
                            <p class="text-gray-500">Личный чат</p>
                        </div>
                    @elseif($chat->type === 'route')
                        @if($chat->route)
                            <img src="{{ $chat->route->cover_image_url ?? asset('images/default-route.jpg') }}" 
                                 alt="{{ $chat->route->title }}" 
                                 class="w-12 h-12 rounded-lg object-cover">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $chat->route->title }}</h1>
                                <p class="text-gray-500">Обсуждение маршрута</p>
                                <a href="{{ route('routes.show', $chat->route) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1 mt-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Посмотреть маршрут
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $chat->name ?? 'Групповой чат' }}</h1>
                            <p class="text-gray-500">{{ $chat->users->count() }} участников</p>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="toggleParticipants()" class="p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.828 0-1.5.672-1.5 1.5v4.5a1.5 1.5 0 001.5 1.5h.5a1.5 1.5 0 001.5-1.5V7.5a1.5 1.5 0 00-1.5-1.5h-.5z"></path>
                        </svg>
                    </button>
                    <form action="{{ route('chats.leave', $chat) }}" method="POST" 
                          onsubmit="return confirm('Вы уверены, что хотите выйти из чата?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-red-500 hover:text-red-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Основное окно чата -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Сообщения -->
                    <div id="messages-container" class="h-[600px] overflow-y-auto p-6 space-y-4">
                        @foreach($messages->reverse() as $message)
                            @include('chats.partials.message', ['message' => $message])
                        @endforeach
                    </div>

                    <!-- Форма отправки -->
                    <div class="border-t border-gray-200 p-4">
                        <form id="message-form" action="{{ route('messages.store', $chat) }}" method="POST" 
                              enctype="multipart/form-data" class="flex gap-2">
                            @csrf
                            <div class="flex-1">
                                <textarea name="content" 
                                          id="message-input"
                                          rows="1"
                                          placeholder="Напишите сообщение..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                          oninput="autoResize(this)"></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="attachment" class="cursor-pointer p-3 text-gray-500 hover:text-gray-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <input type="file" name="attachment" id="attachment" class="hidden">
                                </label>
                                <button type="submit" 
                                        id="send-button"
                                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Боковая панель с участниками -->
            <div class="lg:col-span-1">
                <div id="participants-panel" class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Участники</h3>
                    <div class="space-y-3">
                        @foreach($chat->users as $user)
                            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50">
                                <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}" 
                                     alt="{{ $user->name }}" 
                                     class="w-10 h-10 rounded-full">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if($user->id === auth()->id())
                                            Вы
                                        @else
                                            {{ $user->role === 'admin' ? 'Администратор' : 'Пользователь' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($chat->type === 'group' && $chat->users->contains(auth()->id()))
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-3">Добавить участников</h4>
                            <form action="{{ route('chats.add-users', $chat) }}" method="POST" class="space-y-3">
                                @csrf
                                <select name="user_ids[]" 
                                        multiple
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        style="height: 120px;">
                                    @foreach(\App\Models\User::whereNotIn('id', $chat->users->pluck('id'))->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                    Добавить
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Модальное окно для изображений -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl max-h-screen">
            <button onclick="closeImageModal()" 
                    class="absolute -top-10 right-0 text-white hover:text-gray-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-screen rounded-lg">
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openImageModal(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.getElementById('modalImage').src = '';
        document.body.style.overflow = 'auto';
    }
    
    // Закрытие по клику на фон
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
    
    // Удаление сообщения
    function deleteMessage(messageId) {
        if (confirm('Удалить это сообщение?')) {
            fetch(`/messages/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`.message[data-id="${messageId}"]`).remove();
                }
            });
        }
    }
</script>
@endpush
@push('scripts')
<script>
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }

    function toggleParticipants() {
        const panel = document.getElementById('participants-panel');
        panel.classList.toggle('hidden');
    }

    // WebSocket для реального времени
    const chatId = {{ $chat->id }};
    const userId = {{ auth()->id() }};
    
    // Инициализация Pusher/Echo
    Echo.private(`chat.${chatId}`)
        .listen('NewMessage', (e) => {
            if (e.message.user_id !== userId) {
                const html = e.html;
                const container = document.getElementById('messages-container');
                const temp = document.createElement('div');
                temp.innerHTML = html;
                container.appendChild(temp.firstChild);
                container.scrollTop = container.scrollHeight;
            }
        });

    // AJAX отправка сообщений
    document.getElementById('message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const button = document.getElementById('send-button');
        const textarea = document.getElementById('message-input');
        
        button.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            textarea.value = '';
            textarea.style.height = 'auto';
            button.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
        });
    });
</script>
@endpush
@endsection