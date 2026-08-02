<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UpdatePasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Settings/UpdatePassword');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'password' => $request->validated()['password'],
        ]);

        return back()->with('status', __('messages.password_updated'));
    }
}
