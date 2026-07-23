<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SignUpRequest extends FormRequest
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
            'name.required' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name.min' => __('validation.min.string', ['attribute' => __('validation.attributes.name'), 'min' => 2]),
            'name.max' => __('validation.max.string', ['attribute' => __('validation.attributes.name'), 'max' => 255]),
            'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.unique' => __('validation.unique', ['attribute' => __('validation.attributes.email')]),
            'currency.required' => __('messages.validation_currency_required'),
            'currency.enum' => __('messages.validation_currency_enum'),
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.max' => __('validation.max.string', ['attribute' => __('validation.attributes.password'), 'max' => 100]),
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users'],
            'currency' => ['required', Rule::enum(Currency::class)],
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
