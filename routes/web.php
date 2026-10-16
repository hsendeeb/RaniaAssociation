<?php

use App\Http\Controllers\Site\CourseController;
use App\Http\Controllers\Site\DashboardController;
use App\Http\Controllers\Site\EnrollmentController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\InstructorController;
use App\Http\Controllers\Site\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site (جمعية رانيا)
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->name('instructors.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])->name('contact.send');

/*
|--------------------------------------------------------------------------
| Student area (requires an account)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/courses/{course:slug}/enroll', [EnrollmentController::class, 'create'])
        ->name('enrollments.create');
    Route::post('/courses/{course:slug}/enroll', [EnrollmentController::class, 'store'])
        ->name('enrollments.store');
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])
        ->name('enrollments.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
