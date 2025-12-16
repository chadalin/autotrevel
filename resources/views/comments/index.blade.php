@extends('layouts.app')

@section('title', 'Обсуждения')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Заголовок и фильтры -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Обсуждения</h1>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <select id="type-filter" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Все типы</option>
                            <option value="App\\Models\\TravelRoute" {{ $type === 'App\\Models\\TravelRoute' ? 'selected' : '' }}>Маршруты</option>
                            <option value="App\\Models\\Quest" {{ $type === 'App\\Models\\Quest' ? 'selected' : '' }}>Квесты</option>
                            <option value="App\\Models\\User" {{ $type === 'App\\Models\\User' ? 'selected' : '' }}>Пользователи</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Список комментариев -->
        <div class="space-y-6">
            @forelse($comments as $comment)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Комментарий -->
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <!-- Аватар пользователя -->
                            <a href="{{ route('users.show', $comment->user) }}" class="flex-shrink-0">
                                <img src="{{ $comment->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                                     alt="{{ $comment->user->name }}" 
                                     class="w-12 h-12 rounded-full">
                            </a>

                            <!-- Содержимое -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <a href="{{ route('users.show', $comment->user) }}" 
                                           class="font-semibold text-gray-900 hover:text-blue-600">
                                            {{ $comment->user->name }}
                                        </a>
                                        <span class="text-gray-500 text-sm ml-2">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                        @if($comment->is_pinned)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M5.5 17.5v-11l7 7 7-7v11a.5.5 0 01-.5.5H6a.5.5 0 01-.5-.5z"/>
                                                </svg>
                                                Закреплено
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @can('update', $comment)
                                        <div class="relative group">
                                            <button class="p-1 text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                </svg>
                                            </button>
                                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                                                <a href="#" 
                                                   onclick="editComment({{ $comment->id }})"
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Редактировать
                                                </a>
                                                <form action="{{ route('comments.destroy', $comment) }}" method="POST" 
                                                      onsubmit="return confirm('Удалить комментарий?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endcan
                                </div>

                                <!-- Контент комментария -->
                                <div class="mt-3 text-gray-700 prose max-w-none">
                                    {!! nl2br(e($comment->content)) !!}
                                </div>

                                <!-- Информация об объекте -->
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        @if($comment->commentable_type === 'App\\Models\\TravelRoute')
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-600">
                                                Комментарий к маршруту:
                                            </span>
                                            <a href="{{ route('routes.show', $comment->commentable) }}" 
                                               class="font-medium text-blue-600 hover:text-blue-800">
                                                {{ $comment->commentable->title }}
                                            </a>
                                        @elseif($comment->commentable_type === 'App\\Models\\Quest')
                                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-600">
                                                Комментарий к квесту:
                                            </span>
                                            <a href="{{ route('quests.show', $comment->commentable) }}" 
                                               class="font-medium text-purple-600 hover:text-purple-800">
                                                {{ $comment->commentable->title }}
                                            </a>
                                        @elseif($comment->commentable_type === 'App\\Models\\User')
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-600">
                                                Комментарий в профиле:
                                            </span>
                                            <a href="{{ route('users.show', $comment->commentable) }}" 
                                               class="font-medium text-blue-600 hover:text-blue-800">
                                                {{ $comment->commentable->name }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Действия -->
                                <div class="mt-4 flex items-center gap-4">
                                    <button onclick="toggleLike({{ $comment->id }})" 
                                            class="flex items-center gap-2 text-gray-500 hover:text-red-600">
                                        <svg id="like-icon-{{ $comment->id }}" 
                                             class="w-5 h-5 {{ $comment->isLikedBy(auth()->user()) ? 'text-red-600 fill-current' : '' }}" 
                                             fill="{{ $comment->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}" 
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span id="likes-count-{{ $comment->id }}" class="text-sm">
                                            {{ $comment->likes_count }}
                                        </span>
                                    </button>
                                    
                                    <button onclick="toggleReplyForm({{ $comment->id }})" 
                                            class="flex items-center gap-2 text-gray-500 hover:text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                        </svg>
                                        <span class="text-sm">Ответить</span>
                                    </button>
                                </div>

                                <!-- Форма ответа -->
                                <div id="reply-form-{{ $comment->id }}" class="mt-4 hidden">
                                    <form action="{{ route('comments.store') }}" method="POST" 
                                          onsubmit="submitReply(event, {{ $comment->id }})">
                                        @csrf
                                        <input type="hidden" name="commentable_type" value="{{ $comment->commentable_type }}">
                                        <input type="hidden" name="commentable_id" value="{{ $comment->commentable_id }}">
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <textarea name="content" 
                                                  rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  placeholder="Напишите ответ..."></textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" 
                                                    onclick="toggleReplyForm({{ $comment->id }})"
                                                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                                Отмена
                                            </button>
                                            <button type="submit" 
                                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                                Отправить
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ответы -->
                    @if($comment->replies->count() > 0)
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="space-y-4 ml-12">
                                @foreach($comment->replies as $reply)
                                    <div class="border-l-2 border-blue-200 pl-4">
                                        @include('comments.partials.comment', ['comment' => $reply])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Нет обсуждений</h3>
                    <p class="mt-2 text-gray-500">Будьте первым, кто оставит комментарий!</p>
                </div>
            @endforelse

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $comments->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Фильтрация по типу
    document.getElementById('type-filter').addEventListener('change', function() {
        const type = this.value;
        window.location.href = "{{ route('comments.index') }}?type=" + type;
    });

    // Лайки
    function toggleLike(commentId) {
        fetch(`/comments/${commentId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const icon = document.getElementById(`like-icon-${commentId}`);
            const count = document.getElementById(`likes-count-${commentId}`);
            
            count.textContent = data.likes_count;
            
            if (data.liked) {
                icon.classList.add('text-red-600', 'fill-current');
            } else {
                icon.classList.remove('text-red-600', 'fill-current');
                icon.setAttribute('fill', 'none');
            }
        });
    }

    // Форма ответа
    function toggleReplyForm(commentId) {
        const form = document.getElementById(`reply-form-${commentId}`);
        form.classList.toggle('hidden');
    }

    function submitReply(event, commentId) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            form.reset();
            toggleReplyForm(commentId);
            location.reload();
        })
        .catch(error => console.error('Error:', error));
    }

    // Редактирование комментария
    function editComment(commentId) {
        // Реализация редактирования
    }
</script>
@endpush
@endsection