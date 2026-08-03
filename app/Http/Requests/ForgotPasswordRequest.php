<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('messages.passwords.validation.email_required'),
            'email.email' => __('messages.passwords.validation.email_email'),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The `exists:users,email` rule is intentionally omitted to avoid leaking
     * which email addresses are registered through the validation layer. The
     * controller flashes the same generic status regardless of whether a user
     * was found, so enumeration is not possible through this endpoint.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
