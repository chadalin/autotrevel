@extends('layouts.app')

@section('title', 'Вход - AutoRuta')

@push('styles')
<style>
    .login-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .code-inputs {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin: 20px 0;
    }
    
    .code-input {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        background: white;
        transition: all 0.3s;
    }
    
    .code-input:focus {
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        outline: none;
    }
    
    .code-input.filled {
        border-color: #10b981;
        background-color: #f0fdf4;
    }
    
    .timer {
        font-size: 14px;
        color: #6b7280;
        margin-top: 10px;
    }
    
    .timer.expired {
        color: #ef4444;
    }
</style>
@endpush

@section('content')
<div class="login-container py-12">
    <div class="max-w-md w-full mx-auto">
        <!-- Логотип -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl mb-4">
                <i class="fas fa-route text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Вход в AutoRuta</h1>
            <p class="text-gray-600">Введите email для получения кода подтверждения</p>
        </div>
        
        <!-- Форма входа -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Шаг 1: Ввод email -->
            <div id="step-email" class="transition-all duration-300">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Введите ваш email</h2>
                
                <form id="email-form">
                    @csrf <!-- CSRF токен -->
                    
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email адрес
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-300"
                                   placeholder="ваш@email.com"
                                   required
                                   autocomplete="email"
                                   autofocus>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            На этот email будет отправлен код подтверждения
                        </p>
                    </div>
                    
                    <button type="submit" 
                            id="send-code-btn"
                            class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <span>Получить код</span>
                    </button>
                </form>
            </div>
            
            <!-- Шаг 2: Ввод кода -->
            <div id="step-code" class="hidden transition-all duration-300">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Введите код подтверждения</h2>
                <p class="text-gray-600 mb-6">
                    Код отправлен на <span id="user-email" class="font-semibold text-orange-600"></span>
                </p>
                
                <form id="code-form">
                    @csrf <!-- CSRF токен для проверки кода -->
                    
                    <div class="mb-6">
                        <div class="code-inputs">
                            <input type="text" maxlength="1" class="code-input" data-index="1" inputmode="numeric">
                            <input type="text" maxlength="1" class="code-input" data-index="2" inputmode="numeric">
                            <input type="text" maxlength="1" class="code-input" data-index="3" inputmode="numeric">
                            <input type="text" maxlength="1" class="code-input" data-index="4" inputmode="numeric">
                            <input type="text" maxlength="1" class="code-input" data-index="5" inputmode="numeric">
                            <input type="text" maxlength="1" class="code-input" data-index="6" inputmode="numeric">
                        </div>
                        <input type="hidden" id="full-code" name="code">
                        
                        <div class="timer text-center">
                            <span id="timer">02:00</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" 
                                id="resend-code-btn"
                                class="flex-1 border border-gray-300 text-gray-700 font-medium py-3 px-4 rounded-lg hover:bg-gray-50 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <i class="fas fa-redo mr-2"></i>
                            <span>Отправить снова</span>
                        </button>
                        
                        <button type="submit" 
                                id="verify-code-btn"
                                class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i>
                            <span>Подтвердить</span>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Индикатор загрузки -->
            <div id="loading" class="hidden text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-orange-500"></div>
                <p class="mt-2 text-gray-600">Отправка...</p>
            </div>
            
            <!-- Сообщения об ошибках -->
            <div id="error-message" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <span id="error-text" class="text-red-700"></span>
                </div>
            </div>
            
            <!-- Сообщения об успехе -->
            <div id="success-message" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span id="success-text" class="text-green-700"></span>
                </div>
            </div>
        </div>
        
        <!-- Ссылка на другую страницу -->
        <div class="text-center mt-8">
            <p class="text-gray-600">
                У вас еще нет аккаунта? 
                <a href="{{ route('login') }}" class="text-orange-600 hover:text-orange-700 font-medium">
                    Создайте его сейчас
                </a>
            </p>
        </div>
    </div>
</div>
@endsection



@push('scripts')
<script>
$(document).ready(function() {
    console.log('Страница входа загружена');
    
    let timerInterval;
    let timeLeft = 120;
    let userEmail = '';
    
    // ===== ОБРАБОТЧИК ОТПРАВКИ EMAIL =====
    $('#email-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Отправка формы email');
        
        const email = $('#email').val().trim();
        userEmail = email;
        
        if (!email) {
            showError('Пожалуйста, введите email');
            return;
        }
        
        showLoading();
        hideError();
        hideSuccess();
        
        // Отправляем запрос
        $.ajax({
            url: '/send-code',
            method: 'POST',
            data: {
                email: email,
                _token: getCsrfToken()
            },
            success: function(response) {
                console.log('Успешный ответ:', response);
                hideLoading();
                
                if (response.success) {
                    // Переходим к шагу ввода кода
                    $('#step-email').hide();
                    $('#step-code').removeClass('hidden');
                    $('#user-email').text(email);
                    
                    // Запускаем таймер
                    startTimer();
                    
                    // Показываем сообщение
                    showSuccess(response.message || 'Код отправлен!');
                    
                    // Фокус на первый input кода
                    setTimeout(() => {
                        $('.code-input[data-index="1"]').focus();
                    }, 100);
                    
                } else {
                    showError(response.message || 'Ошибка при отправке кода');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX:', xhr.status, error);
                hideLoading();
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    const errorMsg = Object.values(errors)[0][0];
                    showError(errorMsg);
                } else if (xhr.status === 419) {
                    showError('Ошибка CSRF токена. Обновляем страницу...');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    const error = xhr.responseJSON || {};
                    showError(error.message || 'Ошибка сервера. Попробуйте позже.');
                }
            }
        });
    });
    
    // ===== ОБРАБОТЧИК ПОВТОРНОЙ ОТПРАВКИ КОДА =====
    $('#resend-code-btn').on('click', function() {
        if (!userEmail) return;
        
        resetTimer();
        startTimer();
        
        $.ajax({
            url: '/send-code',
            method: 'POST',
            data: {
                email: userEmail,
                _token: getCsrfToken()
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message || 'Новый код отправлен!');
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON || {};
                showError(error.message || 'Ошибка при повторной отправке');
            }
        });
    });
    
    // ===== ОБРАБОТЧИК ВВОДА КОДА =====
    $('.code-input').on('input', function() {
        const value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
        
        const index = parseInt($(this).data('index'));
        
        if (value.length === 1) {
            $(this).addClass('filled');
            
            // Переходим к следующему input
            if (index < 6) {
                $('.code-input[data-index="' + (index + 1) + '"]').focus();
            }
        } else {
            $(this).removeClass('filled');
        }
        
        // Собираем полный код
        updateFullCode();
        
        // Активируем кнопку подтверждения
        const fullCode = $('#full-code').val();
        $('#verify-code-btn').prop('disabled', fullCode.length !== 6);
    });
    
    // Обработчик клавиш для кода
    $('.code-input').on('keydown', function(e) {
        const index = parseInt($(this).data('index'));
        
        // Backspace
        if (e.key === 'Backspace' && $(this).val() === '' && index > 1) {
            $('.code-input[data-index="' + (index - 1) + '"]').focus().val('').removeClass('filled');
            updateFullCode();
        }
        
        // Стрелки
        if (e.key === 'ArrowLeft' && index > 1) {
            $('.code-input[data-index="' + (index - 1) + '"]').focus();
        }
        
        if (e.key === 'ArrowRight' && index < 6) {
            $('.code-input[data-index="' + (index + 1) + '"]').focus();
        }
    });
    
    // ===== ОБРАБОТЧИК ПРОВЕРКИ КОДА =====
    $('#code-form').on('submit', function(e) {
        e.preventDefault();
        
        const code = $('#full-code').val();
        
        if (code.length !== 6) {
            showError('Введите полный код из 6 цифр');
            return;
        }
        
        showLoading();
        hideError();
        
        $.ajax({
            url: '/verify-code',
            method: 'POST',
            data: {
                code: code,
                _token: getCsrfToken()
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showSuccess('Успешный вход! Перенаправление...');
                    
                    setTimeout(() => {
                        window.location.href = response.redirect || '/';
                    }, 1000);
                } else {
                    showError(response.message || 'Неверный код');
                    
                    // Сбрасываем код
                    $('.code-input').val('').removeClass('filled');
                    $('.code-input[data-index="1"]').focus();
                    $('#full-code').val('');
                    $('#verify-code-btn').prop('disabled', true);
                }
            },
            error: function(xhr) {
                hideLoading();
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    const errorMsg = Object.values(errors)[0][0];
                    showError(errorMsg);
                } else if (xhr.status === 419) {
                    showError('Ошибка безопасности. Обновляем страницу...');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    const error = xhr.responseJSON || {};
                    showError(error.message || 'Ошибка проверки кода');
                }
                
                // Сбрасываем код
                $('.code-input').val('').removeClass('filled');
                $('.code-input[data-index="1"]').focus();
                $('#full-code').val('');
                $('#verify-code-btn').prop('disabled', true);
            }
        });
    });
    
    // ===== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =====
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }
    
    function showLoading() {
        $('#loading').removeClass('hidden');
        $('#send-code-btn, #resend-code-btn, #verify-code-btn').prop('disabled', true);
    }
    
    function hideLoading() {
        $('#loading').addClass('hidden');
        $('#send-code-btn, #verify-code-btn').prop('disabled', false);
    }
    
    function showError(message) {
        $('#error-text').text(message);
        $('#error-message').removeClass('hidden');
        $('#success-message').addClass('hidden');
    }
    
    function hideError() {
        $('#error-message').addClass('hidden');
    }
    
    function showSuccess(message) {
        $('#success-text').text(message);
        $('#success-message').removeClass('hidden');
        $('#error-message').addClass('hidden');
    }
    
    function hideSuccess() {
        $('#success-message').addClass('hidden');
    }
    
    function updateFullCode() {
        let code = '';
        $('.code-input').each(function() {
            code += $(this).val();
        });
        $('#full-code').val(code);
    }
    
    function startTimer() {
        timeLeft = 120;
        updateTimerDisplay();
        $('#resend-code-btn').prop('disabled', true);
        
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                $('#resend-code-btn').prop('disabled', false);
            }
        }, 1000);
    }
    
    function resetTimer() {
        clearInterval(timerInterval);
        timeLeft = 120;
        updateTimerDisplay();
        $('#resend-code-btn').prop('disabled', true);
        startTimer();
    }
    
    function updateTimerDisplay() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        $('#timer').text(`${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`);
        
        if (timeLeft <= 30) {
            $('#timer').addClass('expired');
        } else {
            $('#timer').removeClass('expired');
        }
    }
    
    // Инициализация
    $('#email').focus();
});
</script>
@endpush