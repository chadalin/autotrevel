@extends('layouts.app')

@section('title', $user->name . ' - Профиль')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Заголовок профиля -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <!-- Аватар -->
                <div class="relative">
                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}" 
                         alt="{{ $user->name }}" 
                         class="w-32 h-32 rounded-full border-4 border-white shadow-lg">
                    @if($user->role === 'admin')
                        <div class="absolute bottom-0 right-0 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                            Админ
                        </div>
                    @elseif($user->role === 'moderator')
                        <div class="absolute bottom-0 right-0 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                            Модератор
                        </div>
                    @endif
                </div>

                <!-- Информация -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $user->email }}</p>
                    
                    <!-- Уровень и опыт -->
                    <div class="mt-4">
                        <div class="flex items-center justify-center md:justify-start gap-3">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-2 rounded-lg">
                                <div class="text-xs">Уровень</div>
                                <div class="text-2xl font-bold">{{ $user->level }}</div>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Опыт</span>
                                    <span>{{ $user->experience }} / {{ ($user->level + 1) * 1000 }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-400 to-blue-500 h-2 rounded-full" 
                                         style="width: {{ min(100, ($user->experience / (($user->level + 1) * 1000)) * 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Навигация -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <nav class="flex flex-wrap gap-2 justify-center md:justify-start">
                    <a href="{{ route('users.show', $user) }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Профиль
                    </a>
                    <a href="{{ route('users.routes', $user) }}" 
                       class="px-4 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-lg">
                        Маршруты
                    </a>
                    <a href="{{ route('users.achievements', $user) }}" 
                       class="px-4 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-lg">
                        Достижения
                    </a>
                    <a href="{{ route('users.activity', $user) }}" 
                       class="px-4 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-lg">
                        Активность
                    </a>
                </nav>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Создано маршрутов</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['routes_created'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Пройдено маршрутов</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['routes_completed'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Выполнено квестов</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['quests_completed'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Получено значков</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['badges_earned'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Последние активности -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Последние активности</h2>
                    <div class="space-y-4">
                        @forelse($recentActivities as $activity)
                            <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">
                                        Пройден маршрут
                                        <a href="{{ route('routes.show', $activity->route) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ $activity->route->title }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $activity->completed_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-green-600">+{{ $activity->earned_xp }} XP</div>
                                    @if($activity->rating)
                                        <div class="flex items-center gap-1 text-yellow-500">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $activity->rating ? 'fill-current' : '' }}" 
                                                     viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <p class="mt-2">Нет активностей</p>
                            </div>
                        @endforelse
                    </div>
                    @if($recentActivities->count() > 0)
                        <div class="mt-4 text-center">
                            <a href="{{ route('users.activity', $user) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Вся активность →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Последние маршруты -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Последние маршруты</h2>
                    <div class="space-y-4">
                        @forelse($recentRoutes as $route)
                            <a href="{{ route('routes.show', $route) }}" 
                               class="block group">
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:border-blue-300 transition-colors">
                                    @if($route->cover_image)
                                        <img src="{{ asset('storage/' . $route->cover_image) }}" 
                                             alt="{{ $route->title }}" 
                                             class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <h3 class="font-medium text-gray-900 group-hover:text-blue-600 line-clamp-1">
                                            {{ $route->title }}
                                        </h3>
                                        <div class="mt-2 flex items-center justify-between text-sm text-gray-500">
                                            <span>{{ $route->length_km }} км</span>
                                            <span>{{ $route->duration_minutes }} мин</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>Нет созданных маршрутов</p>
                            </div>
                        @endforelse
                    </div>
                    @if($recentRoutes->count() > 0)
                        <div class="mt-4 text-center">
                            <a href="{{ route('users.routes', $user) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Все маршруты →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection