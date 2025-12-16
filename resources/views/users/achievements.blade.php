@extends('layouts.app')

@section('title', $user->name . ' - Достижения')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Заголовок -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Достижения {{ $user->name }}</h1>
                    <p class="text-gray-600">Значки и выполненные квесты</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Значки -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Значки</h2>
                    @if($badges->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($badges as $badge)
                                @php
                                    $rarityColors = [
                                        'common' => 'bg-gray-100 border-gray-300',
                                        'rare' => 'bg-blue-50 border-blue-200',
                                        'epic' => 'bg-purple-50 border-purple-200',
                                        'legendary' => 'bg-yellow-50 border-yellow-200'
                                    ];
                                @endphp
                                
                                <div class="text-center">
                                    <div class="{{ $rarityColors[$badge->rarity] }} rounded-xl p-4 border-2">
                                        @if($badge->icon_svg)
                                            <div class="mb-3">
                                                {!! $badge->icon_svg !!}
                                            </div>
                                        @elseif($badge->icon)
                                            <img src="{{ asset($badge->icon) }}" 
                                                 alt="{{ $badge->name }}" 
                                                 class="w-16 h-16 mx-auto mb-3">
                                        @else
                                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-{{ $badge->color }}-400 to-{{ $badge->color }}-600 mx-auto mb-3 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        <h4 class="font-medium text-gray-900 text-sm">{{ $badge->name }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">{{ $badge->description }}</p>
                                        <div class="mt-2 text-xs font-medium {{ 
                                            $badge->rarity === 'common' ? 'text-gray-600' : 
                                            ($badge->rarity === 'rare' ? 'text-blue-600' : 
                                            ($badge->rarity === 'epic' ? 'text-purple-600' : 'text-yellow-600')) 
                                        }}">
                                            {{ ucfirst($badge->rarity) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $badges->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="mt-2 text-gray-500">Нет полученных значков</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Выполненные квесты -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Выполненные квесты</h2>
                    <div class="space-y-4">
                        @forelse($quests as $userQuest)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900">{{ $userQuest->quest->title }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $userQuest->quest->short_description }}</p>
                                <div class="mt-3 flex justify-between items-center">
                                    <span class="text-xs text-gray-500">
                                        {{ $userQuest->completed_at->diffForHumans() }}
                                    </span>
                                    <span class="text-sm font-medium text-green-600">
                                        +{{ $userQuest->quest->reward_exp }} XP
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>Нет выполненных квестов</p>
                            </div>
                        @endforelse
                    </div>
                    
                    @if($quests->count() > 0)
                        <div class="mt-4">
                            {{ $quests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection