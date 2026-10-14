<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

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
            $googleUser = Socialite::driver('google')->user();

            $existingUser = $this->authService->handleExistingGoogleUser($googleUser);

            if ($existingUser) {
                Auth::login($existingUser);

                return redirect('/dashboard');
            }

            // New user, store data in session and redirect to role selection
            session([
                'pending_google_user' => [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'id' => $googleUser->id,
                    'token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                ],
            ]);

            return redirect()->route('auth.google.role.show');
        } catch (\Exception $e) {
            Log::error('Google Login Error: '.$e->getMessage());

            return redirect('/login')->with('error', 'Google login failed: '.$e->getMessage());
        }
    }

    public function showGoogleRoleSelection()
    {
        if (! session('pending_google_user')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/CompleteGoogle', [
            'name' => session('pending_google_user')['name'],
            'email' => session('pending_google_user')['email'],
        ]);
    }

    public function completeGoogleRegistration(Request $request)
    {
        $request->validate([
            'role' => 'required|in:parent,child',
        ]);

        $googleData = session('pending_google_user');

        if (! $googleData) {
            return redirect()->route('login')->with('error', 'Session expired. Please try Google login again.');
        }

        $user = $this->authService->createGoogleUser($googleData, $request->role);

        session()->forget('pending_google_user');

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registration successful!');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function showVerifyOtp()
    {
        if (! session('registration_email')) {
            return redirect()->route('register');
        }

        return Inertia::render('Auth/VerifyOtp', [
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
