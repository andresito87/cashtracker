<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    /**
     * Render the reset password form for a validly-signed URL.
     *
     * The `signed` route middleware rejects unsigned, tampered, or expired
     * links with HTTP 403 before this method runs, so the email and token here
     * are guaranteed to come from an integrity-checked URL.
     */
    public function index(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email', ''),
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Validate the reset payload and update the password through the broker.
     *
     * On success the user is signed in and the session is regenerated. Any
     * broker error (invalid/expired/consumed token, unknown user) flashes the
     * same generic error, so the response never leaks account existence.
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($request) {
                // The broker does NOT persist the password itself — the callback
                // owns to write. The User's `hashed` cast hashes the plain
                // value exactly once on save.
                $user->forceFill(['password' => $password])
                    ->setRememberToken(Str::random(60));
                $user->save();

                Auth::login($user);
                $request->session()->regenerate();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('dashboard')->with([
                'status' => __('messages.passwords.reset_success'),
                'status_type' => 'success',
            ])
            : back()->withErrors(['email' => __('messages.passwords.reset_failed')]);
    }
}
