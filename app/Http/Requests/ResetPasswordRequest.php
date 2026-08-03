<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'email.required' => __('messages.passwords.validation.email_required'),
            'email.email' => __('messages.passwords.validation.email_email'),
            'password.required' => __('messages.passwords.validation.password_required'),
            'password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.letters' => __('validation.password.letters', ['attribute' => __('validation.attributes.password')]),
            'password.mixed' => __('validation.password.mixed', ['attribute' => __('validation.attributes.password')]),
            'password.symbols' => __('validation.password.symbols', ['attribute' => __('validation.attributes.password')]),
            'password.numbers' => __('validation.password.numbers', ['attribute' => __('validation.attributes.password')]),
            'password.uncompromised' => __('validation.password.uncompromised', ['attribute' => __('validation.attributes.password')]),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('validation.attributes.password')]),
            'password_confirmation.required' => __('messages.passwords.validation.password_confirmation_required'),
            'token.required' => __('messages.passwords.validation.token_required'),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->symbols()
                    ->numbers()
                    ->uncompromised(),
            ],
            'password_confirmation' => ['required'],
            'token' => ['required', 'string'],
        ];
    }
}
