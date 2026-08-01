<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Note: Domain-level model authorization (resource ownership & permissions) is intentionally
     * decoupled from HTTP FormRequests and handled via Gate::authorize() inside Controllers using Policies.
     * Returning `true` here ensures this FormRequest acts strictly as a reusable Data Validation Object (DTO).
     * Use this method for request-level security checks (e.g. payload restrictions or HMAC headers).
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
        $budget = $this->route('budget') ?? $this->route('expense')?->budget;
        $editingExpense = $this->route('expense');

        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:99999999.99',
                function (string $attribute, mixed $value, Closure $fail) use ($budget, $editingExpense) {
                    if (! $budget || ! is_numeric($value)) {
                        return;
                    }

                    $budgetAmount = (float) $budget->amount;
                    $currentTotalSpent = (float) $budget->expenses()
                        ->when($editingExpense, fn ($query) => $query->where('id', '!=', $editingExpense->id))
                        ->sum('amount');

                    $availableBalance = max(0, $budgetAmount - $currentTotalSpent);

                    if ((float) $value > $availableBalance) {
                        $fail(__('messages.validation_amount_exceeds_balance'));
                    }
                },
            ],
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
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
            'name.required' => __('messages.validation_expense_name_required'),
            'amount.required' => __('messages.validation_amount_required'),
            'amount.numeric' => __('messages.validation_amount_numeric'),
            'amount.decimal' => __('messages.validation_amount_decimal'),
            'amount.min' => __('messages.validation_amount_min'),
            'category.required' => __('messages.validation_category_required'),
            'category.enum' => __('messages.validation_category_enum'),
        ];
    }
}
