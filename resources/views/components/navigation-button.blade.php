@auth
    @php
        $activeSession = \App\Models\RouteSession::where('user_id', auth()->id())
            ->where('route_id', $route->id)
            ->whereIn('status', ['active', 'paused'])
            ->first();
    @endphp
    
    @if($activeSession)
        <!-- Активная сессия -->
        <a href="{{ route('routes.navigate', $route) }}" 
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-play-circle mr-3 text-xl"></i>
            <div class="text-left">
                <div class="text-sm font-normal opacity-90">Продолжить навигацию</div>
                <div>{{ $activeSession->progress_percentage }}% пройдено</div>
            </div>
        </a>
    @else
        <!-- Начать навигацию -->
        <form action="{{ route('routes.navigation.start', $route) }}" method="POST" class="inline">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300">
                <i class="fas fa-play-circle mr-3 text-xl"></i>
                <div class="text-left">
                    <div class="text-sm font-normal opacity-90">Начать путешествие</div>
                    <div>Запустить навигатор</div>
                </div>
            </button>
        </form>
    @endif
@else
    <!-- Для неавторизованных -->
    <a href="{{ route('login') }}" 
       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300">
        <i class="fas fa-sign-in-alt mr-3 text-xl"></i>
        <div class="text-left">
            <div class="text-sm font-normal opacity-90">Войдите, чтобы начать</div>
            <div>Путешествие по маршруту</div>
        </div>
    </a>
@endauth