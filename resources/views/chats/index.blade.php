@extends('layouts.app')

@section('title', 'Мои чаты')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Мои чаты</h1>
        <a href="{{ route('chats.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Новый чат
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Список чатов -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($chats->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($chats as $chat)
                            <a href="{{ route('chats.show', $chat) }}" 
                               class="block p-4 hover:bg-gray-50 transition duration-150 ease-in-out {{ $chat->unread_messages_count > 0 ? 'bg-blue-50' : '' }}">
                                <div class="flex items-start gap-4">
                                    <!-- Аватар чата -->
                                    <div class="flex-shrink-0">
                                        @if($chat->type === 'private')
                                            @php
                                                $otherUser = $chat->users->where('id', '!=', auth()->id())->first();
                                            @endphp
                                            <img src="{{ $otherUser->avatar_url ?? asset('images/default-avatar.png') }}" 
                                                 alt="{{ $otherUser->name }}" 
                                                 class="w-12 h-12 rounded-full">
                                        @elseif($chat->type === 'route')
                                            @if($chat->route)
                                                <img src="{{ $chat->route->cover_image_url ?? asset('images/default-route.jpg') }}" 
                                                     alt="{{ $chat->route->title }}" 
                                                     class="w-12 h-12 rounded-lg object-cover">
                                            @else
                                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Информация о чате -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                                    @if($chat->type === 'private')
                                                        {{ $otherUser->name ?? 'Пользователь' }}
                                                    @elseif($chat->type === 'route')
                                                        {{ $chat->route->title ?? 'Маршрут' }}
                                                    @else
                                                        {{ $chat->name ?? 'Групповой чат' }}
                                                    @endif
                                                </h3>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    @if($chat->type === 'private')
                                                        Личный чат
                                                    @elseif($chat->type === 'route')
                                                        Обсуждение маршрута
                                                    @else
                                                        {{ $chat->users_count }} участников
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $chat->updated_at->diffForHumans() }}
                                            </div>
                                        </div>

                                        @if($chat->lastMessage)
                                            <p class="text-gray-600 mt-2 truncate">
                                                <span class="font-medium">{{ $chat->lastMessage->user->name }}:</span>
                                                {{ Str::limit($chat->lastMessage->content, 80) }}
                                            </p>
                                        @endif

                                        @if($chat->unread_messages_count > 0)
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full mt-2">
                                                {{ $chat->unread_messages_count }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Пагинация -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                        {{ $chats->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Нет чатов</h3>
                        <p class="mt-1 text-sm text-gray-500">Начните новый разговор</p>
                        <div class="mt-6">
                            <a href="{{ route('chats.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Создать чат
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Боковая панель -->
       <!-- Боковая панель -->
<div class="lg:col-span-1">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Типы чатов</h3>
        <div class="space-y-3">
            <a href="{{ route('chats.index') }}?type=all" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 {{ $type === 'all' ? 'bg-blue-50' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 005 10a6 6 0 0112 0c0 .459-.031.905-.086 1.333A5.002 5.002 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-gray-700">Все чаты</span>
                </div>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ $stats['private'] + $stats['route'] + $stats['group'] }}
                </span>
            </a>
            <a href="{{ route('chats.index') }}?type=private" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 {{ $type === 'private' ? 'bg-blue-50' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-gray-700">Личные чаты</span>
                </div>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ $stats['private'] }}
                </span>
            </a>
            <a href="{{ route('chats.index') }}?type=route" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 {{ $type === 'route' ? 'bg-blue-50' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-gray-700">Обсуждения маршрутов</span>
                </div>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ $stats['route'] }}
                </span>
            </a>
            <a href="{{ route('chats.index') }}?type=group" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 {{ $type === 'group' ? 'bg-blue-50' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                    </div>
                    <span class="text-gray-700">Групповые чаты</span>
                </div>
                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ $stats['group'] }}
                </span>
            </a>
        </div>
    </div>
</div>
@endsection