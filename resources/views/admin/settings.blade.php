@extends('layouts.admin')

@section('title', 'Настройки - Админ панель')
@section('page-title', 'Настройки системы')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-6">Настройки сайта</h3>
    
    <form method="POST" action="{{ url('/admin/settings') }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Основные настройки -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название сайта</label>
                    <input type="text" 
                           name="site_name" 
                           value="{{ $settings['site_name'] ?? config('app.name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email для связи</label>
                    <input type="email" 
                           name="contact_email" 
                           value="{{ $settings['contact_email'] ?? 'admin@example.com' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Максимальная длина маршрута (км)</label>
                    <input type="number" 
                           name="max_route_length" 
                           value="{{ $settings['max_route_length'] ?? 1000 }}"
                           min="10" 
                           step="10"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <!-- Переключатели -->
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="auto_approve_routes" 
                           name="auto_approve_routes" 
                           {{ ($settings['auto_approve_routes'] ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_approve_routes" class="ml-2 block text-sm text-gray-700">
                        Автоматическое одобрение маршрутов
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="enable_registration" 
                           name="enable_registration" 
                           {{ ($settings['enable_registration'] ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="enable_registration" class="ml-2 block text-sm text-gray-700">
                        Разрешить регистрацию новых пользователей
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Максимальный размер фото (MB)</label>
                    <select name="max_file_size_photo" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="2" {{ ($settings['max_file_size_photo'] ?? 2) == 2 ? 'selected' : '' }}>2 MB</option>
                        <option value="5" {{ ($settings['max_file_size_photo'] ?? 2) == 5 ? 'selected' : '' }}>5 MB</option>
                        <option value="10" {{ ($settings['max_file_size_photo'] ?? 2) == 10 ? 'selected' : '' }}>10 MB</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Кнопки -->
        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-4">
            <button type="reset" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Сбросить
            </button>
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Сохранить настройки
            </button>
        </div>
    </form>
</div>
@endsection