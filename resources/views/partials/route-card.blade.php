<div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300">
    <!-- Изображение -->
    <div class="relative h-48 overflow-hidden">
        @if($route->cover_image)
            <img src="{{ Storage::url($route->cover_image) }}" alt="{{ $route->title }}" 
                 class="w-full h-full object-cover hover:scale-105 transition duration-500">
        @else
            <div class="w-full h-full bg-gradient-to-r 
                {{ $route->difficulty === 'hard' ? 'from-red-400 to-orange-500' : 
                   ($route->difficulty === 'medium' ? 'from-yellow-400 to-orange-400' : 'from-green-400 to-blue-400') }} 
                flex items-center justify-center">
                <i class="fas fa-route text-white text-5xl"></i>
            </div>
        @endif
        
        <!-- Бейдж сложности -->
        <div class="absolute top-3 left-3">
            @php
                $difficultyColors = [
                    'easy' => 'bg-green-100 text-green-800',
                    'medium' => 'bg-yellow-100 text-yellow-800',
                    'hard' => 'bg-red-100 text-red-800',
                ];
                $difficultyLabels = [
                    'easy' => 'Легкий',
                    'medium' => 'Средний',
                    'hard' => 'Сложный',
                ];
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $difficultyColors[$route->difficulty] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $difficultyLabels[$route->difficulty] ?? $route->difficulty }}
            </span>
        </div>
    </div>
    
    <!-- Контент -->
    <div class="p-5">
        <!-- Заголовок и автор -->
        <div class="mb-3">
            <h3 class="font-bold text-gray-800 text-lg mb-1 line-clamp-1">
                <a href="{{ route('routes.show', $route) }}" class="hover:text-orange-600 transition duration-300">
                    {{ $route->title }}
                </a>
            </h3>
            <div class="flex items-center text-sm text-gray-600">
                <div class="w-6 h-6 rounded-full bg-gradient-to-r from-orange-400 to-red-500 
                           flex items-center justify-center text-white text-xs font-bold mr-2">
                    {{ substr($route->user->name, 0, 1) }}
                </div>
                <span>{{ $route->user->name }}</span>
            </div>
        </div>
        
        <!-- Краткое описание -->
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
            {{ $route->short_description ?? Str::limit($route->description, 100) }}
        </p>
        
        <!-- Теги -->
        @if($route->tags->count() > 0)
            <div class="flex flex-wrap gap-1.5 mb-4">
                @foreach($route->tags->take(3) as $tag)
                    <span class="px-2 py-1 text-xs rounded-full" 
                          style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                        #{{ $tag->name }}
                    </span>
                @endforeach
                @if($route->tags->count() > 3)
                    <span class="px-2 py-1 text-xs text-gray-500">
                        +{{ $route->tags->count() - 3 }}
                    </span>
                @endif
            </div>
        @endif
        
        <!-- Статистика -->
        <div class="grid grid-cols-3 gap-3 text-center mb-4">
            <div>
                <div class="text-lg font-bold text-gray-800">{{ $route->length_km }} км</div>
                <div class="text-xs text-gray-600">Длина</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-800">
                    @php
                        $hours = floor($route->duration_minutes / 60);
                        $minutes = $route->duration_minutes % 60;
                    @endphp
                    @if($hours > 0)
                        {{ $hours }}ч
                    @endif
                    @if($minutes > 0)
                        {{ $minutes }}мин
                    @endif
                </div>
                <div class="text-xs text-gray-600">Время</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-800">{{ $route->views_count }}</div>
                <div class="text-xs text-gray-600">Просмотры</div>
            </div>
        </div>
        
        <!-- Кнопки -->
        <div class="flex space-x-2">
            <a href="{{ route('routes.show', $route) }}" 
               class="flex-1 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 
                      text-white text-center py-2.5 rounded-lg font-medium text-sm transition duration-300">
                Подробнее
            </a>
            @auth
                <button class="w-10 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg 
                             flex items-center justify-center transition duration-300">
                    <i class="far fa-heart"></i>
                </button>
            @endauth
        </div>
    </div>
</div>