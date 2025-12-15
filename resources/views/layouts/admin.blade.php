<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Админ панель') - {{ config('app.name', 'AutoRuta') }}</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    <!-- Мобильное меню кнопка -->
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menu-toggle" class="bg-gray-800 text-white p-2 rounded-lg">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="flex h-screen">
        <!-- Сайдбар -->
        <div id="sidebar" class="sidebar fixed md:relative w-64 bg-gray-900 text-white h-full overflow-y-auto z-40">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-8 flex items-center">
                    <i class="fas fa-cogs mr-3"></i> AutoRuta Admin
                </h1>
                
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Дашборд</span>
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.users.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Пользователи</span>
                    </a>
                    
                    <a href="{{ route('admin.routes.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.routes.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-route"></i>
                        <span>Маршруты</span>
                    </a>
                    
                    <a href="{{ route('admin.quests.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.quests.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-tasks"></i>
                        <span>Квесты</span>
                    </a>
                    
                    <!-- Убираем пункт жалоб, если нет модели Report -->
                    <!-- <a href="{{ route('admin.reports.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.reports.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-flag"></i>
                        <span>Жалобы</span>
                    </a> -->
                    <!--
                    <a href="{{ route('admin.reports.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.activity_log') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-history"></i>
                        <span>Лог действий</span>
                    </a>-->
                    
                    <a href="{{ route('admin.reports.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.settings') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Настройки</span>
                    </a>
                    
                    <div class="pt-8 mt-8 border-t border-gray-700">
                        <a href="{{ route('home') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300">
                            <i class="fas fa-home"></i>
                            <span>На сайт</span>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-700 bg-red-600 transition duration-300">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Выйти</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Основной контент -->
        <div class="flex-1 overflow-y-auto">
            <!-- Хедер -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">@yield('page-title', 'Админ панель')</h2>
                            <p class="text-gray-600 text-sm">@yield('page-subtitle', 'Управление системой')</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <button id="user-menu" class="flex items-center space-x-3 focus:outline-none">
                                    <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-white">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="text-left hidden md:block">
                                        <p class="font-medium text-gray-800">{{ Auth::user()->name }}</p>
                                        <p class="text-sm text-gray-600">Администратор</p>
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </button>
                                
                                <!-- Выпадающее меню пользователя -->
                                <div id="user-dropdown" 
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border hidden z-50">
                                    <div class="p-4 border-b">
                                        <p class="font-medium">{{ Auth::user()->name }}</p>
                                        <p class="text-sm text-gray-600">{{ Auth::user()->email }}</p>
                                    </div>
                                    <a href="{{ route('admin.reports.index') }}" 
                                       class="block px-4 py-3 hover:bg-gray-100">
                                        <i class="fas fa-user-edit mr-3 text-gray-500"></i>Профиль
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full text-left block px-4 py-3 hover:bg-gray-100 text-red-600">
                                            <i class="fas fa-sign-out-alt mr-3"></i>Выйти
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Контент -->
            <main class="p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <div>
                                <p class="font-bold">Ошибка!</p>
                                <ul class="mt-1 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                @yield('content')
            </main>
            
            <!-- Футер -->
            <footer class="bg-white border-t px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        © {{ date('Y') }} {{ config('app.name', 'AutoRuta') }}. Все права защищены.
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="hidden md:inline">Версия: 1.0.0</span>
                        <span class="mx-2">•</span>
                        <span>{{ now()->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script>
        // Мобильное меню
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        // Закрыть меню при клике вне его на мобильных
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
        
        // Выпадающее меню пользователя
        document.getElementById('user-menu').addEventListener('click', function() {
            document.getElementById('user-dropdown').classList.toggle('hidden');
        });
        
        // Закрыть выпадающее меню при клике вне его
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });
        
        // Закрыть меню при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('open');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>