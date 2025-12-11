<div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
    <div class="md:flex">
        <!-- Изображение -->
        <div class="md:w-2/5 relative">
            @if($route->cover_image)
                <img src="{{ $route->cover_image }}" alt="{{ $route->title }}" class="w-full h-full object-cover min-h-64">
            @else
                <div class="w-full h-full min-h-64 bg-gradient-to-r from-red-400 to-orange-500 flex items-center justify-center">
                    <i class="fas fa-fire text-white text-6xl"></i>
                </div>
            @endif
            
            <!-- Бейдж популярности -->
            <div class="absolute top-4 left-4">
                <span class="px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                    <i class="fas fa-fire mr-1"></i> Популярный
                </span>
            </div>
        </div>
        
        <!-- Контент -->
        <div class="md:w-3/5 p-6">
            <h3 class="font-bold text-2xl text-gray-800 mb-2">{{ $route->title }}</h3>
            
            <div class="flex items-center mb-3">
                <div class="flex items-center mr-4">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center text-white font-bold text-sm mr-2">
                        {{ substr($route->user->name, 0, 1) }}
                    </div>
                    <span class="text-gray-700 font-medium">{{ $route->user->name }}</span>
                </div>
                <div class="flex items-center text-yellow-500">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($route->average_rating) ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                    @endfor
                    <span class="ml-2 font-bold">{{ $route->average_rating }}</span>
                </div>
            </div>
            
            <p class="text-gray-600 mb-4 line-clamp-2">{{ $route->short_description ?? Str::limit($route->description, 150) }}</p>
            
            <!-- Теги -->
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($route->tags->take(4) as $tag)
                    <span class="px-3 py-1 text-xs rounded-full" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>
            
            <!-- Детали -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $route->length_km }}</div>
                    <div class="text-sm text-gray-600">километров</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $route->duration_formatted }}</div>
                    <div class="text-sm text-gray-600">время</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $route->completions_count }}</div>
                    <div class="text-sm text-gray-600">проехали</div>
                </div>
            </div>
            
            <!-- Кнопки -->
            <div class="flex space-x-3">
                <a href="#" class="flex-1 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-center py-3 rounded-lg font-bold transition duration-300">
                    Подробнее
                </a>
                <button class="w-12 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg flex items-center justify-center">
                    <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    </div>
</div>