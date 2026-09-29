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
    // --- Google Socialite Login ---

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::updateOrCreate([
                'email' => $googleUser->email,
            ], [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'email_verified_at' => now(), // Auto verify if coming from google
            ]);

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google login failed.');
        }
    }

    // --- Manual Registration with OTP ---

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        // Generate OTP
        $otpCode = rand(100000, 999999);

        // Store OTP
        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(15)
            ]
        );

        // Send OTP via Email
        Mail::to($request->email)->send(new OtpMail($otpCode));

        // In a real app, you would redirect to a view to enter the OTP and password
        // and keep the email and name in session.
        session(['registration_email' => $request->email, 'registration_name' => $request->name]);

        return redirect('/verify-otp')->with('success', 'OTP sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = session('registration_email');
        $name = session('registration_name');

        if (!$email || !$name) {
            return redirect('/register')->with('error', 'Session expired. Please register again.');
        }

        $otpRecord = Otp::where('email', $email)->first();

        if (!$otpRecord || $otpRecord->otp !== $request->otp || $otpRecord->expires_at < now()) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // OTP is valid, create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        // Clear OTP and session
        $otpRecord->delete();
        session()->forget(['registration_email', 'registration_name']);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registration successful!');
    }
}
