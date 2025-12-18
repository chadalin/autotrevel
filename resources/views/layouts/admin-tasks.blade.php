<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Админ панель') - {{ config('app.name', 'AutoRuta') }}</title>
     <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            overflow-x: hidden;
        }
        
        .sidebar {
            transition: transform 0.3s ease-in-out;
            z-index: 40;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 30;
            }
            .sidebar-overlay.open {
                display: block;
            }
        }
        
        .content-area {
            min-height: calc(100vh - 140px);
        }
        
        /* Кастомные стили для скроллбара */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Анимации */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-in-out;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Мобильное меню кнопка и оверлей -->
    <div class="md:hidden">
        <button id="menu-toggle" class="fixed top-4 left-4 z-50 bg-gray-800 text-white p-3 rounded-lg shadow-lg">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <div id="sidebar-overlay" class="sidebar-overlay"></div>
    </div>

    <div class="flex min-h-screen">
        <!-- Сайдбар -->
        <div id="sidebar" class="sidebar w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="h-full flex flex-col">
                <!-- Логотип -->
                <div class="p-6 border-b border-gray-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cogs text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">AutoRuta</h1>
                            <p class="text-xs text-gray-400">Admin Panel</p>
                        </div>
                    </div>
                </div>
                
                <!-- Навигация -->
                <div class="flex-1 overflow-y-auto py-4">
                    <nav class="px-4 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fas fa-tachometer-alt w-5 text-center"></i>
                            <span>Дашборд</span>
                        </a>
                        
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fas fa-users w-5 text-center"></i>
                            <span>Пользователи</span>
                        </a>
                        
                        <a href="{{ route('admin.routes.index') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.routes.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fas fa-route w-5 text-center"></i>
                            <span>Маршруты</span>
                        </a>
                        
                        <a href="{{ route('admin.quests.index') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.quests.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fas fa-tasks w-5 text-center"></i>
                            <span>Квесты</span>
                        </a>
                        
                        <a href="{{ route('admin.settings') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.settings') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fas fa-cog w-5 text-center"></i>
                            <span>Настройки</span>
                        </a>
                    </nav>
                    
                    <!-- Разделитель -->
                    <div class="px-4 my-6">
                        <div class="h-px bg-gray-800"></div>
                    </div>
                    
                    <!-- Выход -->
                    <div class="px-4">
                        <a href="{{ route('home') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200 mb-2">
                            <i class="fas fa-home w-5 text-center"></i>
                            <span>На сайт</span>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors duration-200">
                                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                                <span>Выйти</span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Информация о пользователе -->
                <div class="p-4 border-t border-gray-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ Auth::user()->name ?? 'Администратор' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Основной контент -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Хедер -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-800">@yield('page-title', 'Админ панель')</h2>
                            <p class="text-gray-600 text-sm md:text-base">@yield('page-subtitle', 'Управление системой')</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Уведомления -->
                            <div class="relative">
                                <button id="notifications-btn" class="p-2 rounded-full hover:bg-gray-100">
                                    <i class="fas fa-bell text-gray-600"></i>
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                                </button>
                            </div>
                            
                            <!-- Поиск -->
                            <div class="relative hidden md:block">
                                <input type="text" 
                                       placeholder="Поиск..." 
                                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Контент -->
            <main class="flex-1 overflow-y-auto content-area">
                <div class="p-4 md:p-6">
                    <!-- Сообщения об ошибках/успехе -->
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg fade-in">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                                </div>
                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg p-1.5 hover:bg-green-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg fade-in">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-red-800 font-medium">{{ session('error') }}</p>
                                </div>
                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg p-1.5 hover:bg-red-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg fade-in">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-red-800 font-bold mb-2">Ошибка!</p>
                                    <ul class="list-disc list-inside text-red-700">
                                        @foreach($errors->all() as $error)
                                            <li class="text-sm">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg p-1.5 hover:bg-red-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Основной контент страницы -->
                    <div class="slide-in">
                        @yield('content')
                    </div>
                </div>
            </main>
            
            <!-- Футер -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-gray-600 mb-2 md:mb-0">
                        © {{ date('Y') }} {{ config('app.name', 'AutoRuta') }}. Все права защищены.
                    </div>
                    <div class="text-sm text-gray-600">
                        <span>Версия: 1.0.0</span>
                        <span class="mx-2 hidden md:inline">•</span>
                        <span class="block md:inline">{{ now()->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script>
        // Мобильное меню
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('open');
                }
            });
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('open');
                });
            }
            
            // Закрыть меню при клике вне его
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                        sidebar.classList.remove('open');
                        if (sidebarOverlay) {
                            sidebarOverlay.classList.remove('open');
                        }
                    }
                }
            });
            
            // Закрыть меню при изменении размера окна
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('open');
                    }
                }
            });
        }
        
        // Управление выпадающими меню
        document.addEventListener('DOMContentLoaded', function() {
            // Закрыть все выпадающие меню при клике вне их
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.dropdown-content');
                dropdowns.forEach(function(dropdown) {
                    if (!dropdown.contains(event.target) && !event.target.closest('.dropdown-toggle')) {
                        dropdown.classList.add('hidden');
                    }
                });
            });
            
            // Инициализация выпадающих меню
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    const dropdown = this.nextElementSibling;
                    if (dropdown && dropdown.classList.contains('dropdown-content')) {
                        dropdown.classList.toggle('hidden');
                    }
                });
            });
        });
        
        // Анимация появления элементов
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);
        
        // Наблюдать за элементами с классом animate-on-scroll
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
        
        // Уведомления
        const notificationsBtn = document.getElementById('notifications-btn');
        if (notificationsBtn) {
            notificationsBtn.addEventListener('click', function() {
                // Здесь можно добавить логику для показа уведомлений
                alert('Уведомления будут реализованы позже');
            });
        }
        
        // Сохранение состояния меню
        document.addEventListener('DOMContentLoaded', function() {
            const menuState = localStorage.getItem('sidebarCollapsed');
            if (menuState === 'true' && window.innerWidth > 768) {
                sidebar.classList.add('collapsed');
            }
        });
        
        // Показать/скрыть меню на десктопе
        const collapseBtn = document.getElementById('collapse-btn');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>