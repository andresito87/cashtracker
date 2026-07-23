<?php

namespace App\Http\Requests;

use App\Enums\BudgetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
            'type' => ['required', Rule::enum(BudgetType::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.validation_name_required'),
            'amount.required' => __('messages.validation_amount_required'),
            'amount.numeric' => __('messages.validation_amount_numeric'),
            'amount.decimal' => __('messages.validation_amount_decimal'),
            'amount.min' => __('messages.validation_amount_min'),
            'type.required' => __('messages.validation_type_required'),
            'type.enum' => __('messages.validation_type_enum'),
        ];
    }
}
