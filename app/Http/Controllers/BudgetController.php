<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategory;
use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    /**
     * Display a listing of the budgets (all budgets for admins, owned budgets for regular users).
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Admins can view all budgets across the application, while regular users see only their own list.
        $budgets = $user->isAdmin()
            ? Budget::with('user')->latest()->get()
            : $user->budgets()->with('user')->latest()->get();

        return Inertia::render('Dashboard', [
            'budgets' => $budgets->map(fn ($budget) => array_merge($budget->toArray(), [
                'formatted_amount' => $budget->formattedAmount(),
                'currency_symbol' => $budget->user?->currency?->symbol() ?? auth()->user()?->currency?->symbol() ?? '€',
            ])),
        ]);
    }

    /**
     * Show the form for creating a new budget.
     */
    public function create(): View
    {
        Gate::authorize('create', Budget::class);

        return view('budgets.create');
    }

    /**
     * Store a newly created budget in storage.
     */
    public function store(BudgetRequest $request): RedirectResponse
    {
        Gate::authorize('create', Budget::class);

        // Use chained method to create a budget for the authenticated user
        // Via Eloquent relationships, this approach automatically sets the user_id field in the budgets table
        // to the ID of the authenticated user.
        $budget = auth()->user()->budgets()->create($request->validated());

        return redirect()
            ->route('budgets.show', $budget)
            ->with([
                'status' => __('messages.budget_created'),
                'status_type' => 'success',
            ]);
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget): Response
    {
        Gate::authorize('view', $budget);

        // Load the expenses relationship with the latest expenses first, so that the most recent expenses
        // are displayed at the top of the list. We can access the expenses via $budget->expenses in the view.
        $budget->load(['expenses' => fn ($query) => $query->latest(), 'user']);

        return Inertia::render('Budgets/Show', [
            'budget' => array_merge($budget->toArray(), [
                'formatted_amount' => $budget->formattedAmount(),
                'currency_symbol' => $budget->user?->currency?->symbol() ?? auth()->user()?->currency?->symbol() ?? '€',
            ]),
            'categories' => collect(ExpenseCategory::cases())->map(fn ($category) => [
                'value' => $category->value,
                'label' => $category->label(),
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified budget.
     */
    public function edit(Budget $budget): View
    {
        Gate::authorize('update', $budget);

        return view('budgets.edit', compact('budget'));
    }

    /**
     * Update the specified budget in storage.
     */
    public function update(BudgetRequest $request, Budget $budget): RedirectResponse
    {
        Gate::authorize('update', $budget);

        $budget->update($request->validated());

        return redirect()
            ->route('budgets.show', $budget)
            ->with([
                'status' => __('messages.budget_updated'),
                'status_type' => 'success',
            ]);
    }

    /**
     * Remove the specified budget from storage.
     */
    public function destroy(Budget $budget): RedirectResponse
    {
        Gate::authorize('delete', $budget);

        $budget->delete();

        return redirect()
            ->route('dashboard')
            ->with([
                'status' => __('messages.budget_deleted'),
                'status_type' => 'success',
            ]);
    }
}
