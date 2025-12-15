<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function showTestPage()
    {
        return view('test-csrf');
    }
    
    public function testCsrf(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'CSRF работает!',
            'csrf_token' => csrf_token(),
            'session_id' => session()->getId(),
            'your_data' => $request->all(),
            'headers' => $request->headers->all(),
        ]);
    }
}