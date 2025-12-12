<div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300 transform hover:-translate-y-1">
    <!-- Изображение -->
    <div class="relative h-32 overflow-hidden">
        @if($quest->cover_image)
            <img src="{{ $quest->cover_image }}" alt="{{ $quest->title }}" 
                 class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-r {{ $quest->difficulty === 'hard' ? 'from-red-400 to-pink-500' : ($quest->difficulty === 'medium' ? 'from-orange-400 to-yellow-400' : 'from-green-400 to-emerald-500') }} 
                     flex items-center justify-center">
                <i class="fas fa-flag text-white text-3xl"></i>
            </div>
        @endif
        
        <!-- Бейдж сложности -->
        <div class="absolute top-2 left-2">
            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $quest->difficulty_color }} backdrop-blur-sm">
                {{ substr($quest->difficulty_label, 0, 1) }}
            </span>
        </div>
    </div>
    
    <!-- Контент -->
    <div class="p-3">
        <h3 class="font-bold text-gray-800 text-sm mb-1 line-clamp-1">{{ $quest->title }}</h3>
        
        <!-- Краткое описание -->
        <p class="text-gray-600 text-xs mb-2 line-clamp-2">{{ Str::limit($quest->short_description, 60) }}</p>
        
        <!-- Статистика -->
        <div class="flex justify-between text-xs text-gray-500 mb-3">
            <div class="flex items-center">
                <i class="fas fa-users mr-1 text-gray-400"></i>
                <span>{{ $quest->participants_count }}</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-trophy mr-1 text-yellow-500"></i>
                <span>{{ $quest->reward_exp }}</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-medal mr-1 text-purple-500"></i>
                <span>{{ $quest->routes()->count() }}</span>
            </div>
        </div>
        
        <!-- Кнопка -->
        <a href="{{ route('quests.show', $quest->slug) }}" 
           class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs py-1.5 rounded-lg font-medium transition duration-300">
            Подробнее
        </a>
    </div>
</div>