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
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


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

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $this->authService->initiateRegistration($data);

        return redirect()->route('auth.verify.otp.show')->with('success', 'OTP sent to your email.');
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $request->validated();

        try {
            $user = $this->authService->verifyOtpAndCreateUser($request->otp);
            Auth::login($user);
            return redirect('/dashboard')->with('success', 'Registration successful!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('register')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }
}
