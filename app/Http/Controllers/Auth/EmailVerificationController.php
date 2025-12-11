<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.email-login');
    }

    public function sendCode(Request $request)
    {
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
        
        // Ищем пользователя или создаём нового
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => explode('@', $email)[0],
                'is_verified' => false,
            ]
        );

        // Генерируем и отправляем код
        $code = $user->generateVerificationCode();

        try {
            Mail::to($user->email)->send(new VerificationCodeMail($code));
            
            session(['verification_email' => $email]);
            
            return response()->json([
                'success' => true,
                'message' => 'Код отправлен на вашу почту'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке кода'
            ], 500);
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
                'message' => 'Сессия истекла'
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
            
            return response()->json([
                'success' => true,
                'message' => 'Успешная авторизация',
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