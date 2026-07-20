<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

Route::get('/auth/register', [RegisterController::class, 'index'])->name('register');
Route::post('/auth/register', [RegisterController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('register.store');

Route::get('/auth/login', [LoginController::class, 'index'])->name('login');
Route::post('/auth/login', [LoginController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');
Route::post('/auth/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::get('verify-email/{id}/{hash}', [RegisterController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', [RegisterController::class, 'resendVerification'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})
    // Middleware avoids the user to access this route if they are not authenticated
    ->middleware('auth')->name('verification.notice');

Route::get('/dashboard', function () {
    return view('dashboard');
})
    // Middleware avoids the user to access this route if they are not authenticated and verified his email
    ->middleware(['auth', 'verified'])->name('dashboard');

// Budget routes — requires to be authenticated and email-verified user
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('budgets', BudgetController::class);
});
