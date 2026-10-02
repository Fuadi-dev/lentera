<?php

use App\Http\Controllers\ProfileController;
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
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard/courses/{course}', function (string $course) {
    $courses = json_decode(
        file_get_contents(base_path('data/courses.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $selectedCourse = collect($courses)->first(
        fn(array $item): bool => (string) $item['id'] === $course
    );

    abort_if($selectedCourse === null, 404);

    return Inertia::render('Course/Index', [
        'course' => $selectedCourse,
    ]);
})->middleware(['auth', 'verified'])->name('courses.show');

Route::get('/dashboard/courses/{course}/classes/{class}', function (string $course, int $class) {
    $courses = json_decode(
        file_get_contents(base_path('data/courses.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $selectedCourse = collect($courses)->first(
        fn (array $item): bool => (string) $item['id'] === $course
    );

    abort_if($selectedCourse === null, 404);
    abort_if(
        collect($selectedCourse['classes'] ?? [])->doesntContain(
            fn (array $item): bool => (int) $item['number'] === $class
        ),
        404
    );

    return Inertia::render('Course/LearningPath', [
        'course' => $selectedCourse,
        'classNumber' => $class,
    ]);
})->middleware(['auth', 'verified'])->name('courses.learning-path');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\AuthController;

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/google/role', [AuthController::class, 'showGoogleRoleSelection'])->name('auth.google.role.show');
Route::post('/auth/google/role', [AuthController::class, 'completeGoogleRegistration'])->name('auth.google.role.complete');

Route::get('/auth/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('auth.verify.otp.show');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify.otp');

require __DIR__ . '/auth.php';
