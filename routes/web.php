<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseProgressController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\Subject;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    if (auth()->user()->role === 'parent') {
        return app(ParentDashboardController::class)->index(request());
    }

    return Inertia::render('Dashboard', [
        'courses' => Subject::select('id', 'title', 'description', 'thumbnail')->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/dashboard/courses/{course}/classes/{class}', [CourseController::class, 'learningPath'])->name('courses.learning-path');
    Route::get('/dashboard/courses/{course}/classes/{class}/materials/{material}', [CourseController::class, 'material'])->name('courses.material');
    Route::post('/dashboard/progress/units/{unit}/complete', [CourseProgressController::class, 'completeUnit'])->name('progress.units.complete');
    Route::post('/dashboard/progress/streak', [CourseProgressController::class, 'recordStreak'])->name('progress.streak.record');
    Route::post('/dashboard/progress/chapters/{chapter}/complete', [CourseProgressController::class, 'completeChapter'])->name('progress.chapters.complete');

    Route::post('/parent/children/pair', [ParentDashboardController::class, 'pairChild'])->name('parent.children.pair');
    Route::delete('/parent/children/{student}/unpair', [ParentDashboardController::class, 'unpairChild'])->name('parent.children.unpair');

    Route::post('/ai/chat/sessions', [AiChatController::class, 'storeSession'])->name('ai.chat.sessions.store');
    Route::post('/ai/chat/sessions/{session}/messages', [AiChatController::class, 'storeMessage'])->name('ai.chat.messages.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/pairing-code/regenerate', [ProfileController::class, 'regeneratePairingCode'])->name('profile.pairing-code.regenerate');
});

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/google/role', [AuthController::class, 'showGoogleRoleSelection'])->name('auth.google.role.show');
Route::post('/auth/google/role', [AuthController::class, 'completeGoogleRegistration'])->name('auth.google.role.complete');

Route::get('/auth/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('auth.verify.otp.show');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify.otp');

require __DIR__.'/auth.php';
