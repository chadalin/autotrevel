@php
    $isOwn = $message->user_id === auth()->id();
    $isSystem = $message->is_system ?? false;
@endphp

<div class="message {{ $isOwn ? 'own-message' : 'other-message' }} {{ $isSystem ? 'system-message' : '' }} mb-4" data-id="{{ $message->id }}">
    @if(!$isOwn && !$isSystem)
        <!-- Аватар отправителя -->
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <img src="{{ $message->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                     alt="{{ $message->user->name }}" 
                     class="w-8 h-8 rounded-full cursor-pointer"
                     onclick="showUserProfile({{ $message->user->id }})">
            </div>
            <div class="flex-1">
                <!-- Имя отправителя -->
                <div class="mb-1">
                    <span class="text-sm font-medium text-gray-700 cursor-pointer hover:text-blue-600"
                          onclick="showUserProfile({{ $message->user->id }})">
                        {{ $message->user->name }}
                    </span>
                    <span class="text-xs text-gray-500 ml-2">
                        {{ $message->created_at->format('H:i') }}
                    </span>
                </div>
                
                <!-- Содержимое сообщения -->
                <div class="relative group">
                    <div class="inline-block px-4 py-2 bg-gray-100 rounded-2xl rounded-tl-none max-w-lg">
                        @if($message->attachment)
                            <!-- Вложение -->
                            <div class="mb-2">
                                @if(Str::startsWith($message->attachment, ['image/', 'storage/chat_attachments']) && 
                                    in_array(pathinfo($message->attachment, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ $message->attachment_url }}" 
                                         alt="Вложение" 
                                         class="max-w-full h-auto rounded-lg cursor-pointer" 
                                         onclick="openImageModal('{{ $message->attachment_url }}')">
                                @else
                                    <a href="{{ $message->attachment_url }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2 p-3 bg-white rounded-lg border hover:bg-gray-50">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ basename($message->attachment) }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Вложение
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Текст сообщения -->
                        @if($message->content)
                            <div class="text-gray-800 whitespace-pre-wrap break-words">
                                {{ $message->content }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Действия с сообщением -->
                    @if(!$isSystem)
                        <div class="absolute -top-2 right-0 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                            <button onclick="showUserProfile({{ $message->user->id }})" 
                                    class="p-1 bg-white rounded-full shadow-sm hover:bg-blue-50 hover:text-blue-600"
                                    title="Профиль">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </button>
                            @if($isOwn)
                                <button onclick="deleteMessage({{ $message->id }})" 
                                        class="p-1 bg-white rounded-full shadow-sm hover:bg-red-50 hover:text-red-600"
                                        title="Удалить">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($isOwn && !$isSystem)
        <!-- Собственное сообщение -->
        <div class="flex justify-end">
            <div class="flex flex-col items-end max-w-lg">
                <!-- Содержимое сообщения -->
                <div class="relative group">
                    <div class="inline-block px-4 py-2 bg-blue-600 text-white rounded-2xl rounded-tr-none">
                        @if($message->attachment)
                            <!-- Вложение -->
                            <div class="mb-2">
                                @if(Str::startsWith($message->attachment, ['image/', 'storage/chat_attachments']) && 
                                    in_array(pathinfo($message->attachment, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ $message->attachment_url }}" 
                                         alt="Вложение" 
                                         class="max-w-full h-auto rounded-lg cursor-pointer" 
                                         onclick="openImageModal('{{ $message->attachment_url }}')">
                                @else
                                    <a href="{{ $message->attachment_url }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2 p-3 bg-blue-500 rounded-lg hover:bg-blue-700">
                                        <svg class="w-6 h-6 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-white">
                                                {{ basename($message->attachment) }}
                                            </div>
                                            <div class="text-xs text-white/80">
                                                Вложение
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Текст сообщения -->
                        @if($message->content)
                            <div class="whitespace-pre-wrap break-words">
                                {{ $message->content }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Действия с сообщением -->
                    <div class="absolute -top-2 right-0 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                        <button onclick="deleteMessage({{ $message->id }})" 
                                class="p-1 bg-white rounded-full shadow-sm hover:bg-red-50 hover:text-red-600"
                                title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Время отправки -->
                <div class="mt-1 text-xs text-gray-500">
                    {{ $message->created_at->format('H:i') }}
                    @if($message->read_at)
                        <span class="ml-1 text-blue-500" title="Прочитано">
                            <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @elseif($isSystem)
        <!-- Системное сообщение -->
        <div class="text-center my-4">
            <div class="inline-block px-4 py-2 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-600 italic">
                    {{ $message->content }}
                </p>
                <span class="text-xs text-gray-400">
                    {{ $message->created_at->format('H:i') }}
                </span>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function showUserProfile(userId) {
        window.open(`/users/${userId}`, '_blank');
    }
</script>
@endpush