<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
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
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/dashboard/courses/{course}/classes/{class}', [CourseController::class, 'learningPath'])->name('courses.learning-path');
    Route::get('/dashboard/courses/{course}/classes/{class}/materials/{material}', [CourseController::class, 'material'])->name('courses.material');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/google/role', [AuthController::class, 'showGoogleRoleSelection'])->name('auth.google.role.show');
Route::post('/auth/google/role', [AuthController::class, 'completeGoogleRegistration'])->name('auth.google.role.complete');

Route::get('/auth/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('auth.verify.otp.show');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify.otp');

require __DIR__ . '/auth.php';
