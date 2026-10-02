<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    /**
     * Handle the Google callback and create/update the user.
     */
    public function handleGoogleLogin()
    {
        $googleUser = Socialite::driver('google')->user();
        
        $user = User::updateOrCreate([
            'email' => $googleUser->email,
        ], [
            'name' => $googleUser->name,
            'google_id' => $googleUser->id,
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
            'email_verified_at' => now(),
        ]);

        return $user;
    }

    /**
     * Generate OTP, save it, and send the email.
     */
    public function initiateRegistration(array $data)
    {
        $otpCode = rand(100000, 999999);

        Otp::updateOrCreate(
            ['email' => $data['email']],
            [
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(15)
            ]
        );

        Mail::to($data['email'])->send(new OtpMail($otpCode));

        session([
            'registration_email' => $data['email'],
            'registration_name' => $data['name'],
            'registration_password' => Hash::make($data['password']),
            'registration_role' => $data['role'],
        ]);
    }

    /**
     * Verify OTP and create the user if valid.
     */
    public function verifyOtpAndCreateUser(string $otpInput, array $sessionData)
    {
        $otpRecord = Otp::where('email', $sessionData['email'])->first();

        if (!$otpRecord || $otpRecord->otp !== $otpInput || $otpRecord->expires_at < now()) {
            return false;
        }

        $user = User::create([
            'name' => $sessionData['name'],
            'email' => $sessionData['email'],
            'password' => $sessionData['password'],
            'role' => $sessionData['role'],
            'email_verified_at' => now(),
        ]);

        $otpRecord->delete();
        session()->forget(['registration_email', 'registration_name', 'registration_password', 'registration_role']);

        return $user;
    }
}
