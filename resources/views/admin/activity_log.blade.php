
@extends('layouts.admin')

@section('title', 'Лог действий - Админ панель')
@section('page-title', 'Лог действий')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-6">История действий</h3>
    
    @if($activities->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-history text-4xl mb-4"></i>
            <p>Нет записей в логе действий</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Объект</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($activities as $activity)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($activity->created_at)->format('d.m.Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                                    @if($activity->name)
                                        <span class="text-gray-600 text-sm font-medium">{{ substr($activity->name, 0, 1) }}</span>
                                    @else
                                        <i class="fas fa-user text-gray-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $activity->name ?? 'Система' }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $activity->description ?? 'Не указано' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $activity->log_name ?? 'default' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            @if($activity->subject_type)
                                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection