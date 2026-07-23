<?php

namespace App\View\Components;

use App\Enums\BudgetType;
use App\Models\Budget;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BudgetForm extends Component
{
    public ?Budget $budget = null;

    public string $action = '';

    public string $method = 'POST';

    public ?string $methodOverride = null;

    public bool $isEdit = false;

    public string $submitText = '';

    public string $loadingText = '';

    public string $currentType = 'general';

    public string $userCurrencySymbol = '€';

    public ?string $name = null;

    public ?string $amount = null;

    public ?string $description = null;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?Budget $budget = null,
        string $action = '',
        string $method = 'POST'
    ) {
        $this->budget = $budget;
        $this->action = $action;
        $this->method = strtoupper($method);
        // Allows for method override in the form if the method is not GET or POST (e.g., PUT, PATCH, DELETE)
        $this->methodOverride = ! in_array($this->method, ['GET', 'POST']) ? $this->method : null;
        $this->isEdit = $this->budget && $this->budget->exists;
        $this->submitText = $this->isEdit ? __('messages.update_budget') : __('messages.save_budget');
        $this->loadingText = $this->isEdit ? __('messages.updating_budget') : __('messages.saving_budget');
        $this->currentType = (string) old('type', $this->budget?->type?->value ?? $this->budget?->type ?? BudgetType::General->value);
        $this->userCurrencySymbol = auth()->user()?->currency?->symbol() ?? '€';
        $this->name = old('name', $this->budget?->name);
        $this->amount = old('amount', $this->budget?->amount);
        $this->description = old('description', $this->budget?->description);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.budget-form');
    }
}
