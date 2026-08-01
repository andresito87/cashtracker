<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BudgetChatController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TicketScanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Localization Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');

    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', [RegisterController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [RegisterController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [BudgetController::class, 'index'])->name('dashboard');

    Route::get('/settings', function () {
        return redirect()->route('dashboard');
    })->name('settings');

    Route::get('/password/change', function () {
        return redirect()->route('dashboard');
    })->name('password.edit');

    Route::get('/admin', function () {
        return redirect()->route('dashboard');
    })->name('admin.dashboard');

    Route::resource('budgets', BudgetController::class);

    Route::post('/budgets/{budget}/expenses', [ExpenseController::class, 'store'])->name('budgets.expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::post('/budgets/{budget}/chat', [BudgetChatController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('budgets.chat');
    Route::post('/budgets/{budget}/scan-ticket', [TicketScanController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('budgets.scan-ticket');

    Route::post('/subscription-checkout/{plan}',
        [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription', [SubscriptionController::class, 'manage'])
        ->name('subscription.manage');
    Route::get('/plans', [SubscriptionController::class, 'manage'])
        ->name('plans');
    Route::post('/subscription/swap/{plan}', [SubscriptionController::class, 'swap'])
        ->name('subscription.swap')
        ->whereIn('plan', ['monthly', 'yearly']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])
        ->name('subscription.resume');

    Route::get('/billing/success', [SubscriptionController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [SubscriptionController::class, 'cancelUrl'])->name('billing.cancel');

});
