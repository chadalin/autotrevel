@extends('layouts.app')

@section('title', 'Значки квестов')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Заголовок -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Значки квестов</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Собирайте уникальные значки, выполняя квесты и достигая новых высот!
        </p>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Всего значков</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $badges->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L11.414 10l2.293-2.293zM6.293 9.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L8.586 10 6.293 7.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Легендарные</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $badges->where('rarity', 'legendary')->count() }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Ваши значки</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ auth()->user()->badges()->count() }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Прогресс</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $badges->total() > 0 ? round((auth()->user()->badges()->count() / $badges->total()) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры по редкости -->
    <div class="mb-8">
        <div class="flex flex-wrap gap-2 justify-center">
            <button onclick="filterBadges('all')" 
                    class="px-4 py-2 rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                Все
            </button>
            <button onclick="filterBadges('common')" 
                    class="px-4 py-2 rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200 transition-colors">
                Обычные
            </button>
            <button onclick="filterBadges('rare')" 
                    class="px-4 py-2 rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                Редкие
            </button>
            <button onclick="filterBadges('epic')" 
                    class="px-4 py-2 rounded-full bg-purple-100 text-purple-800 hover:bg-purple-200 transition-colors">
                Эпические
            </button>
            <button onclick="filterBadges('legendary')" 
                    class="px-4 py-2 rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors">
                Легендарные
            </button>
            <button onclick="filterBadges('collected')" 
                    class="px-4 py-2 rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                Ваши значки
            </button>
            <button onclick="filterBadges('missing')" 
                    class="px-4 py-2 rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition-colors">
                Отсутствующие
            </button>
        </div>
    </div>

    <!-- Сетка значков -->
    <div id="badges-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
        @foreach($badges as $badge)
            @php
                $hasBadge = auth()->user()->badges->contains($badge);
                $rarityColors = [
                    'common' => 'bg-gray-100 border-gray-300',
                    'rare' => 'bg-blue-50 border-blue-200',
                    'epic' => 'bg-purple-50 border-purple-200',
                    'legendary' => 'bg-yellow-50 border-yellow-200'
                ];
                
                $rarityText = [
                    'common' => 'Обычный',
                    'rare' => 'Редкий',
                    'epic' => 'Эпический',
                    'legendary' => 'Легендарный'
                ];
                
                $rarityIconColors = [
                    'common' => 'text-gray-400',
                    'rare' => 'text-blue-400',
                    'epic' => 'text-purple-400',
                    'legendary' => 'text-yellow-400'
                ];
            @endphp
            
            <div class="badge-card {{ $hasBadge ? 'collected' : 'missing' }} {{ $badge->rarity }} relative group">
                <a href="{{ route('quests.badges.show', $badge) }}" 
                   class="block bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden border-2 {{ $rarityColors[$badge->rarity] }} {{ !$hasBadge ? 'opacity-60' : '' }}">
                    <!-- Значок -->
                    <div class="p-6 flex flex-col items-center">
                        @if($badge->icon_svg)
                            <div class="mb-4 transform group-hover:scale-110 transition-transform duration-300">
                                {!! $badge->icon_svg !!}
                            </div>
                        @elseif($badge->icon)
                            <img src="{{ asset($badge->icon) }}" 
                                 alt="{{ $badge->name }}" 
                                 class="w-24 h-24 mb-4">
                        @else
                            <div class="w-24 h-24 rounded-full {{ $hasBadge ? 'bg-gradient-to-br from-' . $badge->color . '-400 to-' . $badge->color . '-600' : 'bg-gray-200' }} flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 {{ $hasBadge ? 'text-white' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Название -->
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                            {{ $badge->name }}
                        </h3>
                        
                        <!-- Редкость -->
                        <div class="flex items-center gap-1 mb-3">
                            @for($i = 0; $i < 4; $i++)
                                <svg class="w-4 h-4 {{ $i < ['common' => 1, 'rare' => 2, 'epic' => 3, 'legendary' => 4][$badge->rarity] ? $rarityIconColors[$badge->rarity] : 'text-gray-200' }}" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="text-xs font-medium {{ $rarityIconColors[$badge->rarity] }}">
                                {{ $rarityText[$badge->rarity] }}
                            </span>
                        </div>
                        
                        <!-- Описание -->
                        <p class="text-sm text-gray-600 text-center mb-4 line-clamp-2">
                            {{ $badge->description }}
                        </p>
                    </div>
                    
                    <!-- Статус -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                        @if($hasBadge)
                            <div class="flex items-center justify-center gap-2 text-green-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium">Получен</span>
                            </div>
                        @else
                            <div class="flex items-center justify-center gap-2 text-gray-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm">Не получен</span>
                            </div>
                        @endif
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Пагинация -->
    <div class="mt-8">
        {{ $badges->links() }}
    </div>
</div>

@push('scripts')
<script>
    function filterBadges(filter) {
        const badges = document.querySelectorAll('.badge-card');
        
        badges.forEach(badge => {
            switch(filter) {
                case 'all':
                    badge.style.display = 'block';
                    break;
                case 'collected':
                    badge.style.display = badge.classList.contains('collected') ? 'block' : 'none';
                    break;
                case 'missing':
                    badge.style.display = badge.classList.contains('missing') ? 'block' : 'none';
                    break;
                default:
                    badge.style.display = badge.classList.contains(filter) ? 'block' : 'none';
            }
        });
    }
</script>
@endpush
@endsection