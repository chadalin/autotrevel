<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ-панель - AutoRuta')</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar {
            width: 250px;
            min-height: 100vh;
        }
        
        .content {
            margin-left: 250px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                position: static;
            }
            
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Боковая панель -->
    <div class="sidebar bg-gradient-to-b from-gray-900 to-gray-800 text-white fixed top-0 left-0">
        <div class="p-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cogs text-white"></i>
                </div>
                <div>
                    <div class="font-bold text-lg">AutoRuta</div>
                    <div class="text-sm text-gray-400">Админ-панель</div>
                </div>
            </a>
            
            <nav class="space-y-2">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800' : '' }}">
                    <i class="fas fa-chart-bar"></i>
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
                    <i class="fas fa-flag"></i>
                    <span>Квесты</span>
                </a>
                
                <a href="{{ route('admin.reports.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.reports.*') ? 'bg-gray-800' : '' }}">
                    <i class="fas fa-flag"></i>
                    <span>Жалобы</span>
                    @if($pendingReports = \App\Models\Report::where('status', 'pending')->count())
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">
                        {{ $pendingReports }}
                    </span>
                    @endif
                </a>
                
                <a href="{{ route('admin.settings') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition duration-300 {{ request()->routeIs('admin.settings') ? 'bg-gray-800' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Настройки</span>
                </a>
            </nav>
        </div>
        
        <div class="absolute bottom-0 w-full p-6 border-t border-gray-700">
            <a href="{{ route('home') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white transition duration-300">
                <i class="fas fa-arrow-left"></i>
                <span>Вернуться на сайт</span>
            </a>
        </div>
    </div>
    
    <!-- Основной контент -->
    <div class="content min-h-screen">
        <!-- Хедер -->
        <header class="bg-white shadow-sm">
            <div class="px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Дашборд')</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative group">
                            <button class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center text-white font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="text-gray-700 font-medium">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-gray-500"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block z-50">
                                <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-home mr-2"></i>На главную
                                </a>
                                <div class="border-t my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Выйти
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Контент -->
        <main class="p-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Управление выпадающим меню
        $(document).ready(function() {
            $('.group').on('click', function(e) {
                e.stopPropagation();
                $(this).find('.hidden').toggleClass('hidden');
            });
            
            $(document).on('click', function() {
                $('.group .hidden').addClass('hidden');
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>