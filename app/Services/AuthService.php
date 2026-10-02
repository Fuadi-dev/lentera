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
     * Check if user exists from Google Login. If yes, update and return. If not, return false.
     */
    public function handleExistingGoogleUser($googleUser)
    {
        $user = User::where('email', $googleUser->email)->first();

        if ($user) {
            $user->update([
                'name' => $googleUser->name, // Update name just in case
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);
            return $user;
        }

        return false;
    }

    /**
     * Create a new user from Google data after role selection.
     */
    public function createGoogleUser(array $googleData, string $role)
    {
        $user = User::create([
            'name' => $googleData['name'],
            'email' => $googleData['email'],
            'google_id' => $googleData['id'],
            'google_token' => $googleData['token'],
            'google_refresh_token' => $googleData['refresh_token'],
            'role' => $role,
            'email_verified_at' => now(),
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
        ]);

        \App\Jobs\SendWelcomeEmailJob::dispatch($user);

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

        Mail::to($data['email'])->queue(new OtpMail($otpCode));

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
    public function verifyOtpAndCreateUser(string $otpInput)
    {
        $email = session('registration_email');
        $name = session('registration_name');
        $password = session('registration_password');
        $role = session('registration_role');

        if (!$email || !$name || !$password || !$role) {
            throw new \InvalidArgumentException('Session expired. Please register again.');
        }

        $otpRecord = Otp::where('email', $email)->first();

        if (!$otpRecord || $otpRecord->otp !== $otpInput || $otpRecord->expires_at < now()) {
            throw new \Exception('Invalid or expired OTP.');
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'email_verified_at' => now(),
        ]);

        $otpRecord->delete();
        session()->forget(['registration_email', 'registration_name', 'registration_password', 'registration_role']);

        \App\Jobs\SendWelcomeEmailJob::dispatch($user);

        return $user;
    }
}
