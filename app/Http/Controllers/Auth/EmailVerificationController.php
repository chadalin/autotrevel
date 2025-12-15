<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.email-login');
    }

    public function sendCode(Request $request)
    {
        \Log::info('=== НАЧАЛО ОТПРАВКИ КОДА ===');
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        
        try {
            \Log::info('Обработка email: ' . $email);
            
            // Ищем пользователя или создаём нового
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => explode('@', $email)[0],
                    'is_verified' => false,
                    'level' => 1,
                    'experience' => 0,
                    'role' => 'user',
                ]
            );

            \Log::info('Пользователь найден/создан: ' . $user->id);
            
            // Генерируем код
            $code = $user->generateVerificationCode();
            
            \Log::info('Код сгенерирован: ' . $code);
            
            // Сохраняем email в сессии
            session(['verification_email' => $email]);
            
            // Отправляем письмо
            \Log::info('Попытка отправки письма на: ' . $email);
            
            Mail::to($email)->send(new VerificationCodeMail($code));
            
            \Log::info('Письмо отправлено успешно');
            
            return response()->json([
                'success' => true,
                'message' => 'Код отправлен на вашу почту! Проверьте папку "Входящие" и "Спам".',
                'email' => $email,
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('ОШИБКА отправки кода: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            // Если ошибка SMTP, показываем код для тестирования
            $debugInfo = [
                'success' => false,
                'message' => 'Ошибка при отправке письма. Попробуйте позже.',
                'email' => $email ?? null
            ];
            
            // В режиме отладки показываем больше информации
            if (config('app.debug')) {
                $debugInfo['debug_error'] = $e->getMessage();
                $debugInfo['debug_code'] = $code ?? 'не сгенерирован';
                $debugInfo['debug_user'] = $user->id ?? 'не создан';
            }
            
            return response()->json($debugInfo, 500);
        }
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $email = session('verification_email');
        
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Сессия истекла. Пожалуйста, запросите код заново.'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }

        if ($user->verifyCode($request->code)) {
            Auth::login($user);
            session()->forget('verification_email');
            
            \Log::info('Успешная авторизация пользователя', [
                'user_id' => $user->id,
                'email' => $email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Успешная авторизация!',
                'redirect' => route('home')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Неверный или просроченный код'
        ], 400);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}