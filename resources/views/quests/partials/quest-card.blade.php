<div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-500 transform hover:-translate-y-1">
    <!-- Верхняя часть с изображением -->
    <div class="relative h-56 overflow-hidden">
        @if($quest->cover_image)
            <img src="{{ $quest->cover_image }}" alt="{{ $quest->title }}" 
                 class="w-full h-full object-cover transform hover:scale-110 transition duration-700">
        @else
            <div class="w-full h-full bg-gradient-to-br {{ $quest->difficulty === 'hard' ? 'from-red-500 to-pink-600' : ($quest->difficulty === 'medium' ? 'from-orange-500 to-yellow-500' : 'from-green-500 to-emerald-600') }} 
                     flex items-center justify-center">
                <i class="fas fa-flag text-white text-6xl"></i>
            </div>
        @endif
        
        <!-- Бейдж сложности -->
        <div class="absolute top-4 left-4">
            <span class="px-4 py-2 rounded-full text-sm font-bold {{ $quest->difficulty_color }} backdrop-blur-sm">
                {{ $quest->difficulty_label }}
            </span>
        </div>
        
        <!-- Бейдж типа -->
        <div class="absolute top-4 right-4">
            <span class="px-4 py-2 rounded-full text-sm font-bold bg-white/90 text-gray-800 backdrop-blur-sm">
                {{ $quest->type_label }}
            </span>
        </div>
        
        <!-- Значок квеста -->
        @if($quest->badge)
            <div class="absolute -bottom-6 right-6">
                <div class="w-16 h-16 rounded-full {{ $quest->badge->color ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 'bg-gradient-to-r from-purple-500 to-pink-500' }} 
                     flex items-center justify-center shadow-lg border-4 border-white">
                    @if($quest->badge->icon)
                        <i class="{{ $quest->badge->icon }} text-white text-2xl"></i>
                    @else
                        <i class="fas fa-medal text-white text-2xl"></i>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    <!-- Контент -->
    <div class="p-6">
        <!-- Заголовок и метки -->
        <div class="flex justify-between items-start mb-3">
            <h3 class="font-bold text-2xl text-gray-800 flex-1">{{ $quest->title }}</h3>
            @if($quest->is_featured)
                <span class="ml-2 px-3 py-1 bg-gradient-to-r from-red-500 to-orange-500 text-white text-xs rounded-full font-bold">
                    <i class="fas fa-star mr-1"></i>Выбор редакции
                </span>
            @endif
        </div>
        
        <!-- Описание -->
        <p class="text-gray-600 mb-4 line-clamp-2">{{ $quest->short_description ?? Str::limit($quest->description, 120) }}</p>
        
        <!-- Статистика -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $quest->participants_count }}</div>
                <div class="text-sm text-gray-600">участников</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $quest->completion_rate }}%</div>
                <div class="text-sm text-gray-600">завершили</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $quest->routes_count ?? $quest->routes()->count() }}</div>
                <div class="text-sm text-gray-600">маршрутов</div>
            </div>
        </div>
        
        <!-- Награды -->
        <div class="flex items-center justify-between mb-6 p-4 bg-gray-50 rounded-xl">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center mr-3">
                    <i class="fas fa-bolt text-white"></i>
                </div>
                <div>
                    <div class="text-gray-800 font-bold text-lg">+{{ $quest->reward_exp }} XP</div>
                    <div class="text-gray-500 text-sm">опыт</div>
                </div>
            </div>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-yellow-500 to-orange-500 flex items-center justify-center mr-3">
                    <i class="fas fa-coins text-white"></i>
                </div>
                <div>
                    <div class="text-gray-800 font-bold text-lg">+{{ $quest->reward_coins }}</div>
                    <div class="text-gray-500 text-sm">монет</div>
                </div>
            </div>
            @if($quest->badge)
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full {{ $quest->badge->color ? 'bg-gradient-to-r from-purple-500 to-pink-500' : 'bg-gradient-to-r from-blue-500 to-indigo-600' }} 
                         flex items-center justify-center mr-3">
                        <i class="fas fa-medal text-white"></i>
                    </div>
                    <div>
                        <div class="text-gray-800 font-bold text-lg">Значок</div>
                        <div class="text-gray-500 text-sm">награда</div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Кнопки -->
        <div class="flex space-x-3">
            <a href="{{ route('quests.show', $quest->slug) }}" 
               class="flex-1 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-center py-3 rounded-lg font-bold transition duration-300 transform hover:scale-105">
                <i class="fas fa-info-circle mr-2"></i>Подробнее
            </a>
            @auth
                @php
                    $userProgress = $quest->getProgressForUser(auth()->user());
                @endphp
                @if($userProgress['status'] === 'in_progress')
                    <button class="w-12 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg flex items-center justify-center hover:scale-105 transition duration-300">
                        <i class="fas fa-play"></i>
                    </button>
                @elseif($userProgress['status'] === 'completed')
                    <button class="w-12 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg flex items-center justify-center" disabled>
                        <i class="fas fa-check"></i>
                    </button>
                @else
                    <form action="{{ route('quests.start', $quest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="w-12 bg-gradient-to-r from-gray-200 to-gray-300 text-gray-800 rounded-lg flex items-center justify-center hover:from-gray-300 hover:to-gray-400 transition duration-300">
                            <i class="fas fa-plus"></i>
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>