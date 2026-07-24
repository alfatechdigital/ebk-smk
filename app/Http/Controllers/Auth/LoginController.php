<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginIdentifier = $credentials['email'];

        // 1. Try to find user by email first
        $user = User::where('email', $loginIdentifier)->first();

        if ($user && $user->role === 'siswa') {
            // Student is trying to log in using email - DENIED!
            throw ValidationException::withMessages([
                'email' => 'Siswa wajib login menggunakan NIS, bukan email.',
            ]);
        }

        // 2. If not found by email, try to find student by NIS
        if (!$user) {
            $student = \App\Models\Student::where('nis', $loginIdentifier)->first();
            if ($student) {
                $user = $student->user;
            }
        }

        if (!$user || !Auth::attempt(['email' => $user->email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'NIS/Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
