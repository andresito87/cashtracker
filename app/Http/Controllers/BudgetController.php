<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Display a listing of the authenticated user's budgets.
     */
    public function index(): View
    {
        // Gate::authorize('viewAny', Budget::class) would restrict this to admins.
        // Instead we scope the query directly — every user sees only their own list.
        $budgets = auth()->user()->budgets()->latest()->get();

        return view('budgets.index', compact('budgets'));
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
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Budget::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        auth()->user()->budgets()->create($data);

        return redirect()
            ->route('budgets.index')
            ->with([
                'status' => __('messages.budget_created'),
                'status_type' => 'success',
            ]);
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget): View
    {
        Gate::authorize('view', $budget);

        return view('budgets.show', compact('budget'));
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
    public function update(Request $request, Budget $budget): RedirectResponse
    {
        Gate::authorize('update', $budget);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $budget->update($data);

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
            ->route('budgets.index')
            ->with([
                'status' => __('messages.budget_deleted'),
                'status_type' => 'success',
            ]);
    }
}
