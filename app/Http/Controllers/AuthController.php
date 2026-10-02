<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(\App\Services\AuthService $authService)
    {
        $this->authService = $authService;
    }

    // --- Google Socialite Login ---

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = $this->authService->handleGoogleLogin();
            Auth::login($user);
            return redirect('/dashboard');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Login Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Google login failed: ' . $e->getMessage());
        }
    }

    // --- Manual Registration with OTP ---

    public function showRegister()
    {
        return \Inertia\Inertia::render('Auth/Register');
    }

    public function showVerifyOtp()
    {
        if (!session('registration_email')) {
            return redirect()->route('register');
        }
        return \Inertia\Inertia::render('Auth/VerifyOtp', [
            'email' => session('registration_email'),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:parent,child',
        ]);

        $this->authService->initiateRegistration($data);

        return redirect()->route('auth.verify.otp.show')->with('success', 'OTP sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $sessionData = [
            'email' => session('registration_email'),
            'name' => session('registration_name'),
            'password' => session('registration_password'),
            'role' => session('registration_role'),
        ];

        if (!$sessionData['email'] || !$sessionData['name'] || !$sessionData['password'] || !$sessionData['role']) {
            return redirect()->route('register')->with('error', 'Session expired. Please register again.');
        }

        $user = $this->authService->verifyOtpAndCreateUser($request->otp, $sessionData);

        if (!$user) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registration successful!');
    }
}
