<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AutoRuta - Маршруты для автопутешествий')</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
        }
        
        .leaflet-container {
            font-family: 'Open Sans', sans-serif;
        }
        
        /* Кастомный scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #FF7A45;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #e56a35;
        }
        
        /* Анимация для уведомлений */
        @keyframes slide-up {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
        
        /* Для выпадающего меню профиля */
        .group:hover .group-hover\:block {
            display: block !important;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Навигация -->
    <nav class="bg-gradient-to-r from-gray-900 to-gray-800 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Логотип -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-route text-white text-lg"></i>
                        </div>
                        <span class="text-white font-bold text-xl tracking-tight">AutoRuta</span>
                    </a>
                </div>

                <!-- Центральное меню -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition duration-300 font-medium {{ request()->routeIs('home') ? 'text-orange-400' : '' }}">
                        <i class="fas fa-home mr-2"></i>Главная
                    </a>
                    <a href="{{ route('search') }}" class="text-gray-300 hover:text-white transition duration-300 font-medium {{ request()->routeIs('search') ? 'text-orange-400' : '' }}">
                        <i class="fas fa-search mr-2"></i>Поиск маршрутов
                    </a>
                    <a href="{{ route('quests.index') }}" class="text-gray-300 hover:text-white transition duration-300 font-medium {{ request()->is('quests*') ? 'text-orange-400' : '' }}">
                        <i class="fas fa-flag mr-2"></i>Квесты
                    </a>
                    <a href="{{ route('routes.index') }}" class="text-gray-300 hover:text-white transition duration-300 font-medium {{ request()->is('routes*') ? 'text-orange-400' : '' }}">
                        <i class="fas fa-route mr-2"></i>Все маршруты
                    </a>
                    @auth
                        <a href="{{ route('routes.create') }}" class="text-gray-300 hover:text-white transition duration-300 font-medium {{ request()->routeIs('routes.create') ? 'text-orange-400' : '' }}">
                            <i class="fas fa-plus-circle mr-2"></i>Создать маршрут
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition duration-300 font-medium">
                            <i class="fas fa-plus-circle mr-2"></i>Создать маршрут
                        </a>
                    @endauth
                </div>

                <!-- Правая часть -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Уведомления -->
                        <button class="relative text-gray-300 hover:text-white transition duration-300">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                        </button>

                        <!-- Профиль -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center text-white font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="text-gray-300 font-medium hidden md:inline">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                            </button>
                            
                            <!-- Выпадающее меню -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block z-50">
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Мой профиль
                                </a>
                                <a href="{{ route('quests.my') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-tasks mr-2"></i>Мои квесты
                                </a>
                                <a href="{{ route('quests.achievements') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-trophy mr-2"></i>Достижения
                                </a>
                                <a href="{{ route('quests.badges') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-medal mr-2"></i>Значки
                                </a>
                                @can('admin', App\Models\Quest::class)
                                <div class="border-t my-1"></div>
                                <a href="{{ route('quests.admin.index') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Админ-панель
                                </a>
                                @endcan
                                <div class="border-t my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Выйти
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Кнопка входа -->
                        <a href="{{ route('login') }}" class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-6 py-2 rounded-lg font-medium transition duration-300 shadow-md hover:shadow-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Войти
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Уведомления -->
    @if(session('success'))
        <div class="fixed bottom-4 right-4 z-50 animate-slide-up">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 z-50 animate-slide-up">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="fixed bottom-4 right-4 z-50 animate-slide-up">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span>Ошибка валидации</span>
                </div>
                <ul class="text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Основной контент -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Футер -->
    <footer class="bg-gradient-to-r from-gray-900 to-gray-800 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- О проекте -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-route text-white"></i>
                        </div>
                        <span class="font-bold text-xl">AutoRuta</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Сообщество автопутешественников России. Делитесь маршрутами, открывайте новые места, участвуйте в квестах.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-vk text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-telegram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Навигация -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Навигация</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white">Главная</a></li>
                        <li><a href="{{ route('search') }}" class="text-gray-400 hover:text-white">Поиск маршрутов</a></li>
                        <li><a href="{{ route('routes.index') }}" class="text-gray-400 hover:text-white">Все маршруты</a></li>
                        <li><a href="{{ route('quests.index') }}" class="text-gray-400 hover:text-white">Квесты</a></li>
                        <li><a href="{{ route('quests.leaderboard') }}" class="text-gray-400 hover:text-white">Таблица лидеров</a></li>
                        @auth
                            <li><a href="{{ route('quests.my') }}" class="text-gray-400 hover:text-white">Мои квесты</a></li>
                            <li><a href="{{ route('routes.create') }}" class="text-gray-400 hover:text-white">Создать маршрут</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white">Войти / Регистрация</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- Популярные теги -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Популярные теги</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-gray-700 rounded-full text-sm">#горы</span>
                        <span class="px-3 py-1 bg-gray-700 rounded-full text-sm">#озера</span>
                        <span class="px-3 py-1 bg-gray-700 rounded-full text-sm">#история</span>
                        <span class="px-3 py-1 bg-gray-700 rounded-full text-sm">#природа</span>
                        <span class="px-3 py-1 bg-gray-700 rounded-full text-sm">#фото</span>
                        <span class="px-3 py-1 bg-gray-700 rounded-full text-sm">#бездорожье</span>
                    </div>
                </div>

                <!-- Контакты -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Контакты</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3"></i>
                            <span>support@autoruta.ru</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3"></i>
                            <span>+7 (999) 123-45-67</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-3"></i>
                            <span>Москва, Россия</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Копирайт -->
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-500">
                <p>&copy; {{ date('Y') }} AutoRuta. Все права защищены.</p>
                <p class="mt-2 text-sm">Карты предоставлены Leaflet | © OpenStreetMap contributors</p>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Общие скрипты -->
    <script>
        // CSRF токен для AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Автоскрытие уведомлений
        $(document).ready(function() {
            setTimeout(function() {
                $('.fixed.bottom-4.right-4').fadeOut('slow');
            }, 5000);
            
            // Закрытие уведомлений по клику
            $(document).on('click', '.fixed.bottom-4.right-4', function() {
                $(this).fadeOut('slow');
            });
        });

        // Открытие/закрытие меню профиля
        document.addEventListener('click', function(event) {
            const profileMenu = document.querySelector('.group');
            if (!profileMenu.contains(event.target)) {
                const dropdown = profileMenu.querySelector('.hidden');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>