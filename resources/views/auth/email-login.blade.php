@extends('layouts.app')

@section('title', 'Вход - AutoRuta')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-route text-white text-xl"></i>
                    </div>
                    <span class="text-gray-900 font-bold text-2xl tracking-tight">AutoRuta</span>
                </a>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Вход в аккаунт
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Введите email для получения кода подтверждения
            </p>
        </div>
        
        <div class="mt-8 bg-white py-8 px-4 shadow-lg rounded-xl sm:px-10">
            <!-- Форма ввода email -->
            <div id="email-form">
                <form id="send-code-form" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email адрес
                        </label>
                        <div class="mt-1 relative">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="your@email.com">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                        <div id="email-error" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>

                    <div>
                        <button type="submit" id="send-code-btn"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-300">
                            <span id="send-code-text">Отправить код</span>
                            <div id="send-code-spinner" class="ml-2 hidden">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </button>
                    </div>
                </form>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">
                                Или вернуться на главную
                            </span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('home') }}" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i class="fas fa-arrow-left mr-2"></i> На главную
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Форма ввода кода -->
            <div id="code-form" class="space-y-6 hidden">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <i class="fas fa-mail-bulk text-green-600 text-xl"></i>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">Проверьте вашу почту</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Мы отправили 6-значный код на <span id="user-email" class="font-semibold"></span>
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Код действителен в течение 15 минут
                    </p>
                </div>
                
                <form id="verify-code-form" class="space-y-6">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">
                            Код подтверждения
                        </label>
                        <div class="mt-4">
                            <div class="flex justify-center space-x-3">
                                <input type="text" name="code1" maxlength="1" 
                                       class="w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition"
                                       oninput="moveToNext(this, 'code2')">
                                <input type="text" name="code2" maxlength="1" 
                                       class="w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition"
                                       oninput="moveToNext(this, 'code3')">
                                <input type="text" name="code3" maxlength="1" 
                                       class="w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition"
                                       oninput="moveToNext(this, 'code4')">
                                <input type="text" name="code4" maxlength="1" 
                                       class="w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition"
                                       oninput="moveToNext(this, 'code5')">
                                <input type="text" name="code5" maxlength="1" 
                                       class="w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition"
                                       oninput="moveToNext(this, 'code6')">
                                <input type="text" name="code6" maxlength="1" 
                                       class="w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition">
                            </div>
                            <input type="hidden" id="full-code" name="code">
                            <div id="code-error" class="mt-2 text-sm text-red-600 text-center hidden"></div>
                        </div>
                    </div>
                    
                    <div class="text-sm text-center">
                        <a href="#" id="resend-code" class="font-medium text-orange-600 hover:text-orange-500">
                            Отправить код повторно
                        </a>
                        <span id="timer" class="text-gray-500 ml-2">(1:00)</span>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" id="back-to-email"
                                class="flex-1 py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i class="fas fa-arrow-left mr-2"></i> Назад
                        </button>
                        <button type="submit" id="verify-code-btn"
                                class="flex-1 flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <span id="verify-code-text">Подтвердить</span>
                            <div id="verify-code-spinner" class="ml-2 hidden">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function moveToNext(current, nextFieldName) {
        if (current.value.length === 1) {
            const nextField = document.getElementsByName(nextFieldName)[0];
            if (nextField) {
                nextField.focus();
            }
        }
        updateFullCode();
    }
    
    function updateFullCode() {
        let fullCode = '';
        for (let i = 1; i <= 6; i++) {
            const field = document.getElementsByName('code' + i)[0];
            if (field) {
                fullCode += field.value;
            }
        }
        document.getElementById('full-code').value = fullCode;
    }
    
    // Таймер для повторной отправки кода
    let timerInterval;
    let timeLeft = 60;
    
    function startTimer() {
        const timerElement = document.getElementById('timer');
        timerElement.textContent = `(${Math.floor(timeLeft / 60)}:${String(timeLeft % 60).padStart(2, '0')})`;
        
        timerInterval = setInterval(() => {
            timeLeft--;
            timerElement.textContent = `(${Math.floor(timeLeft / 60)}:${String(timeLeft % 60).padStart(2, '0')})`;
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById('resend-code').classList.remove('text-gray-400', 'cursor-not-allowed');
                document.getElementById('resend-code').classList.add('text-orange-600', 'hover:text-orange-500');
                timerElement.textContent = '';
            }
        }, 1000);
    }
    
    $(document).ready(function() {
        // Отправка кода
        $('#send-code-form').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#email').val();
            const btn = $('#send-code-btn');
            const btnText = $('#send-code-text');
            const spinner = $('#send-code-spinner');
            
            // Валидация email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $('#email-error').text('Введите корректный email адрес').removeClass('hidden');
                return;
            }
            
            btn.prop('disabled', true);
            btnText.text('Отправка...');
            spinner.removeClass('hidden');
            
            $.ajax({
                url: "{{ route('send.code') }}",
                method: 'POST',
                data: { email: email },
                success: function(response) {
                    if (response.success) {
                        // Показываем форму ввода кода
                        $('#user-email').text(email);
                        $('#email-form').hide();
                        $('#code-form').removeClass('hidden');
                        
                        // Устанавливаем таймер
                        timeLeft = 60;
                        startTimer();
                        
                        // Фокусируемся на первом поле кода
                        $('input[name="code1"]').focus();
                    } else {
                        $('#email-error').text(response.message || 'Ошибка отправки кода').removeClass('hidden');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $('#email-error').text(errors.email ? errors.email[0] : 'Ошибка валидации').removeClass('hidden');
                    } else {
                        $('#email-error').text('Ошибка сервера. Попробуйте позже.').removeClass('hidden');
                    }
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btnText.text('Отправить код');
                    spinner.addClass('hidden');
                }
            });
        });
        
        // Повторная отправка кода
        $('#resend-code').on('click', function(e) {
            e.preventDefault();
            
            if (timeLeft > 0) return;
            
            const email = $('#email').val();
            
            $.ajax({
                url: "{{ route('send.code') }}",
                method: 'POST',
                data: { email: email },
                success: function(response) {
                    if (response.success) {
                        // Сбрасываем таймер
                        clearInterval(timerInterval);
                        timeLeft = 60;
                        startTimer();
                        
                        // Блокируем кнопку
                        $(this).addClass('text-gray-400 cursor-not-allowed').removeClass('text-orange-600 hover:text-orange-500');
                        
                        // Показываем уведомление
                        showNotification('Код отправлен повторно', 'success');
                    }
                }
            });
        });
        
        // Подтверждение кода
        $('#verify-code-form').on('submit', function(e) {
            e.preventDefault();
            
            const code = $('#full-code').val();
            
            if (code.length !== 6) {
                $('#code-error').text('Введите все 6 цифр кода').removeClass('hidden');
                return;
            }
            
            const btn = $('#verify-code-btn');
            const btnText = $('#verify-code-text');
            const spinner = $('#verify-code-spinner');
            
            btn.prop('disabled', true);
            btnText.text('Проверка...');
            spinner.removeClass('hidden');
            
            $.ajax({
                url: "{{ route('verify.code') }}",
                method: 'POST',
                data: { code: code },
                success: function(response) {
                    if (response.success) {
                        showNotification('Успешный вход!', 'success');
                        setTimeout(() => {
                            window.location.href = response.redirect || "{{ route('home') }}";
                        }, 1000);
                    } else {
                        $('#code-error').text(response.message || 'Неверный код').removeClass('hidden');
                        
                        // Сбрасываем поля кода при ошибке
                        for (let i = 1; i <= 6; i++) {
                            $(`input[name="code${i}"]`).val('');
                        }
                        $('input[name="code1"]').focus();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $('#code-error').text(errors.code ? errors.code[0] : 'Ошибка валидации').removeClass('hidden');
                    } else {
                        $('#code-error').text('Ошибка сервера. Попробуйте позже.').removeClass('hidden');
                    }
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btnText.text('Подтвердить');
                    spinner.addClass('hidden');
                }
            });
        });
        
        // Возврат к форме email
        $('#back-to-email').on('click', function() {
            $('#code-form').addClass('hidden');
            $('#email-form').show();
            clearInterval(timerInterval);
        });
        
        // Очистка ошибок при вводе
        $('#email').on('input', function() {
            $('#email-error').addClass('hidden');
        });
        
        $('input[name^="code"]').on('input', function() {
            $('#code-error').addClass('hidden');
            updateFullCode();
        });
        
        // Обработка клавиш в полях кода
        $('input[name^="code"]').on('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value) {
                const prevField = $(this).prev('input');
                if (prevField.length) {
                    prevField.focus();
                }
            }
        });
    });
    
    function showNotification(message, type = 'info') {
        // Удаляем старые уведомления
        $('.notification-toast').remove();
        
        const bgColor = type === 'success' ? 'bg-green-500' : 
                       type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        const toast = $(`
            <div class="notification-toast fixed top-4 right-4 z-50">
                <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-3"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
</script>
@endpush