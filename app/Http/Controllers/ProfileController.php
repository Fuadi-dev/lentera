<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Student;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $pairingCode = null;
        if ($request->user()->role === 'child') {
            $student = $request->user()->student;
            if ($student && empty($student->pairing_code)) {
                $student->pairing_code = Student::generateUniquePairingCode();
                $student->save();
            }
            $pairingCode = $student?->pairing_code;
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'pairingCode' => $pairingCode,
        ]);
    }

    /**
     * Regenerate pairing code for student.
     */
    public function regeneratePairingCode(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'child', 403);

        $student = $request->user()->student;
        if ($student) {
            $student->pairing_code = Student::generateUniquePairingCode();
            $student->save();
        }

        return back()->with('status', 'pairing-code-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
