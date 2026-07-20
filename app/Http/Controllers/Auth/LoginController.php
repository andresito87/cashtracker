<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignInRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function store(SignInRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with([
                    'status' => __('messages.login_success'),
                    'status_type' => 'success',
                ]);
        }
        return back()->withErrors([
            'login' => __('validation.custom.email.invalid_credentials'),
        ])
			// Keep the email input in the form after a failed login attempt to improve user experience
            ->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $locale = session('locale');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($locale) {
            session(['locale' => $locale]);
        }

        return redirect()->route('login');
    }
}
