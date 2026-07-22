<?php

use App\Enums\BudgetType;
use App\Enums\Currency;
use App\Models\Budget;

it('lists only the authenticated users budgets for regular users', function () {
    $user = actingAsVerifiedUser();
    $otherUser = createVerifiedUser();

    $ownBudget = Budget::factory()->for($user)->create(['name' => 'My Budget']);
    $otherBudget = Budget::factory()->for($otherUser)->create(['name' => 'Other Budget']);

    $response = $this->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('My Budget')
        ->assertDontSee('Other Budget');

    $response->assertViewHas('budgets', function ($budgets) use ($ownBudget, $otherBudget) {
        return $budgets->contains($ownBudget) && ! $budgets->contains($otherBudget);
    });
});

it('lists all budgets for admin users', function () {
    $admin = createAdminUser();
    $otherUser = createVerifiedUser();

    $adminBudget = Budget::factory()->for($admin)->create(['name' => 'Admin Budget']);
    $userBudget = Budget::factory()->for($otherUser)->create(['name' => 'User Budget']);

    $this->actingAs($admin);

    $response = $this->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('Admin Budget')
        ->assertSee('User Budget');

    $response->assertViewHas('budgets', function ($budgets) use ($adminBudget, $userBudget) {
        return $budgets->count() === 2
            && $budgets->contains($adminBudget)
            && $budgets->contains($userBudget);
    });
});

it('creates a budget for the authenticated user', function () {
    $user = actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'name' => 'Travel Fund',
        'currency' => 'USD',
        'type' => 'goal',
    ]))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.budget_created'));

    $budget = Budget::query()->first();

    expect($budget)->not->toBeNull()
        ->and($budget->user_id)->toBe($user->id)
        ->and($budget->name)->toBe('Travel Fund')
        ->and($budget->currency)->toBe(Currency::USD)
        ->and($budget->type)->toBe(BudgetType::Goal)
        ->and($budget->formattedAmount())->toBe('350.50 $');
});

it('updates an owned budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'currency' => Currency::EUR,
        'type' => BudgetType::General,
    ]);

    $this->put(route('budgets.update', $budget), validBudgetPayload([
        'name' => 'Updated Budget',
        'amount' => 500,
        'currency' => 'USD',
        'type' => 'goal',
    ]))
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHas('status', __('messages.budget_updated'));

    expect($budget->fresh()->name)->toBe('Updated Budget')
        ->and($budget->fresh()->amount)->toBe('500.00')
        ->and($budget->fresh()->currency)->toBe(Currency::USD)
        ->and($budget->fresh()->type)->toBe(BudgetType::Goal)
        ->and($budget->fresh()->formattedAmount())->toBe('500.00 $');
});

it('soft deletes an owned budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->delete(route('budgets.destroy', $budget))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.budget_deleted'));

    $this->assertSoftDeleted($budget);
});

it('validates budget input on create', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'))
        ->assertSessionHasErrors(['name', 'amount', 'currency', 'type']);
});

it('rejects invalid budget currencies', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'currency' => 'JPY',
    ]))->assertSessionHasErrors(['currency']);
});

it('rejects invalid budget types', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'type' => 'invalid-type',
    ]))->assertSessionHasErrors(['type']);
});

it('rejects negative budget amounts', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => -10,
    ]))->assertSessionHasErrors(['amount']);
});

it('shows the create budget form for verified users', function () {
    actingAsVerifiedUser();

    $this->get(route('budgets.create'))
        ->assertSuccessful();
});

it('shows an owned budget detail page', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create(['name' => 'Emergency Fund']);

    $this->get(route('budgets.show', $budget))
        ->assertSuccessful()
        ->assertSee('Emergency Fund');
});

it('displays translated empty state message in Spanish and English when user has no budgets', function () {
    actingAsVerifiedUser();

    app()->setLocale('es');
    $this->withSession(['locale' => 'es'])
        ->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('No hay presupuestos todavía.')
        ->assertSee('Empieza creando tu primer presupuesto');

    app()->setLocale('en');
    $this->withSession(['locale' => 'en'])
        ->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('No budgets yet.')
        ->assertSee('Start by creating your first budget');
});
