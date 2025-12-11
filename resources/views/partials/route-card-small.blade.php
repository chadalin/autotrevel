<div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300">
    <!-- Изображение -->
    <div class="relative h-40 overflow-hidden">
        @if($route->cover_image)
            <img src="{{ $route->cover_image }}" alt="{{ $route->title }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center">
                <i class="fas fa-route text-white text-4xl"></i>
            </div>
        @endif
        
        <!-- Бейдж сложности -->
        <div class="absolute top-2 left-2">
            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $route->difficulty_color }}">
                {{ substr($route->difficulty_label, 0, 1) }}
            </span>
        </div>
    </div>
    
    <!-- Контент -->
    <div class="p-4">
        <h3 class="font-bold text-gray-800 mb-1 line-clamp-1">{{ $route->title }}</h3>
        
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <i class="fas fa-user text-gray-400 mr-1 text-xs"></i>
            <span class="truncate">{{ $route->user->name }}</span>
        </div>
        
        <!-- Статистика -->
        <div class="flex justify-between text-xs text-gray-500 mb-3">
            <div class="flex items-center">
                <i class="fas fa-road mr-1"></i>
                <span>{{ $route->length_km }}км</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-star text-yellow-500 mr-1"></i>
                <span>{{ $route->average_rating }}</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-eye mr-1"></i>
                <span>{{ $route->views_count }}</span>
            </div>
        </div>
        
        <a href="#" class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 rounded-lg font-medium transition duration-300">
            Подробнее
        </a>
    </div>
</div>