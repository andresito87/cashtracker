<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
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
            'current_password.required' => __('messages.validation_current_password_required'),
            'current_password.current_password' => __('messages.validation_current_password_invalid'),
            'password.required' => __('messages.validation_password_required'),
            'password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.letters' => __('validation.password.letters', ['attribute' => __('validation.attributes.password')]),
            'password.mixed' => __('validation.password.mixed', ['attribute' => __('validation.attributes.password')]),
            'password.symbols' => __('validation.password.symbols', ['attribute' => __('validation.attributes.password')]),
            'password.numbers' => __('validation.password.numbers', ['attribute' => __('validation.attributes.password')]),
            'password.uncompromised' => __('validation.password.uncompromised', ['attribute' => __('validation.attributes.password')]),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('validation.attributes.password')]),
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
            'current_password' => ['required', 'current_password'],
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
        ];
    }
}
