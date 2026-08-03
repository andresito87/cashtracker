<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password request form.
     */
    public function index()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the requested email.
     *
     * The same generic success status is flashed whether the email
     * belongs to a registered account, so the response never leaks which
     * addresses exist in the system.
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('messages.passwords.sent'));
    }
}
