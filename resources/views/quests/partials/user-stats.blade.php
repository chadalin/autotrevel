<div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl shadow-xl p-6 mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center">
        <!-- Левая часть: Уровень и прогресс -->
        <div class="flex-1 mb-6 md:mb-0 md:mr-8">
            <div class="flex items-center mb-4">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center">
                        <span class="text-white text-2xl font-bold">{{ auth()->user()->level }}</span>
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-2 shadow-lg">
                        <i class="fas fa-medal text-yellow-500 text-xl"></i>
                    </div>
                </div>
                <div class="ml-6">
                    <h3 class="text-white text-2xl font-bold">{{ auth()->user()->name }}</h3>
                    <p class="text-gray-300">Путешественник</p>
                </div>
            </div>

            <!-- Прогресс бар -->
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-300 mb-1">
                    <span>Уровень {{ auth()->user()->level }}</span>
                    <span>{{ auth()->user()->level_progress['current'] }}/{{ auth()->user()->level_progress['needed'] }} XP</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full transition-all duration-500" 
                         style="width: {{ auth()->user()->level_progress['percentage'] }}%">
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-2 text-right">
                    {{ auth()->user()->level_progress['percentage'] }}% до следующего уровня
                </div>
            </div>
        </div>

        <!-- Правая часть: Статистика -->
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Всего квестов -->
            <div class="text-center">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-4">
                    <i class="fas fa-tasks text-white text-2xl mb-2"></i>
                    <div class="text-white text-2xl font-bold">
                        {{ auth()->user()->userQuests()->count() }}
                    </div>
                    <div class="text-blue-100 text-sm">Всего квестов</div>
                </div>
            </div>

            <!-- Активные -->
            <div class="text-center">
                <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-xl p-4">
                    <i class="fas fa-play-circle text-white text-2xl mb-2"></i>
                    <div class="text-white text-2xl font-bold">
                        {{ auth()->user()->getActiveQuests()->count() }}
                    </div>
                    <div class="text-orange-100 text-sm">Активные</div>
                </div>
            </div>

            <!-- Завершено -->
            <div class="text-center">
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-4">
                    <i class="fas fa-check-circle text-white text-2xl mb-2"></i>
                    <div class="text-white text-2xl font-bold">
                        {{ auth()->user()->getCompletedQuests()->count() }}
                    </div>
                    <div class="text-green-100 text-sm">Завершено</div>
                </div>
            </div>

            <!-- Значки -->
            <div class="text-center">
                <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-4">
                    <i class="fas fa-medal text-white text-2xl mb-2"></i>
                    <div class="text-white text-2xl font-bold">
                        {{ auth()->user()->badges()->count() }}
                    </div>
                    <div class="text-purple-100 text-sm">Значков</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="mt-8 pt-6 border-t border-gray-700">
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('quests.my') }}" 
               class="px-6 py-3 bg-white hover:bg-gray-100 text-gray-800 rounded-lg font-medium transition duration-300">
                <i class="fas fa-list mr-2"></i>Мои квесты
            </a>
            <a href="{{ route('quests.achievements') }}" 
               class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-lg font-medium transition duration-300">
                <i class="fas fa-trophy mr-2"></i>Достижения
            </a>
            <a href="{{ route('quests.badges') }}" 
               class="px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-lg font-medium transition duration-300">
                <i class="fas fa-medal mr-2"></i>Мои значки
            </a>
            <a href="{{ route('quests.leaderboard') }}" 
               class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg font-medium transition duration-300">
                <i class="fas fa-chart-line mr-2"></i>Таблица лидеров
            </a>
        </div>
    </div>
</div>