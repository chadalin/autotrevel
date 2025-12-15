<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestLoginController extends Controller
{
    /**
     * Показывает форму для тестового входа
     */
    public function showLoginForm()
    {
        return view('auth.test-login');
    }

    /**
     * Обрабатывает тестовый вход
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        
        // Создаем или находим пользователя
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => explode('@', $email)[0],
                'password' => Hash::make('password123'), // временный пароль
                'is_verified' => true,
                'level' => 1,
                'experience' => 0,
                'role' => 'user',
            ]
        );

        // Генерируем тестовый код (6 цифр)
        $testCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Сохраняем код в сессии
        session(['test_verification_code' => $testCode]);
        session(['test_verification_email' => $email]);

        // Авторизуем пользователя сразу
        Auth::login($user);

        // Показываем код на экране
        return view('auth.show-code', [
            'code' => $testCode,
            'email' => $email,
            'user' => $user,
        ]);
    }

    /**
     * Прямой вход по email (без кода)
     */
    public function directLogin($email)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => explode('@', $email)[0],
                'password' => Hash::make('password123'),
                'is_verified' => true,
                'level' => 1,
                'experience' => 0,
                'role' => 'user',
            ]
        );

        Auth::login($user);

        return redirect('/')->with('success', 'Вы вошли как ' . $user->email);
    }

    /**
     * Проверка кода (если нужно)
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $storedCode = session('test_verification_code');
        $email = session('test_verification_email');

        if ($request->code === $storedCode) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                Auth::login($user);
                session()->forget(['test_verification_code', 'test_verification_email']);
                
                return redirect('/')->with('success', 'Добро пожаловать!');
            }
        }

        return back()->with('error', 'Неверный код');
    }
}