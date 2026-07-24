<?php

use App\Enums\BudgetType;
use App\Enums\Currency;
use App\Models\Budget;
use Inertia\Testing\AssertableInertia as Assert;

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
    $user = actingAsVerifiedUser(['currency' => Currency::USD]);

    $this->post(route('budgets.store'), validBudgetPayload([
        'name' => 'Travel Fund',
        'type' => 'goal',
    ]))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.budget_created'));

    $budget = Budget::query()->first();

    expect($budget)->not->toBeNull()
        ->and($budget->user_id)->toBe($user->id)
        ->and($budget->name)->toBe('Travel Fund')
        ->and($budget->type)->toBe(BudgetType::Goal)
        ->and($budget->formattedAmount())->toBe('350.50 $');

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'name' => 'Travel Fund',
        'type' => BudgetType::Goal->value,
    ]);
});

it('updates an owned budget', function () {
    $user = actingAsVerifiedUser(['currency' => Currency::USD]);
    $budget = Budget::factory()->for($user)->create([
        'type' => BudgetType::General,
    ]);

    $this->put(route('budgets.update', $budget), validBudgetPayload([
        'name' => 'Updated Budget',
        'amount' => 500,
        'type' => 'goal',
    ]))
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHas('status', __('messages.budget_updated'));

    expect($budget->fresh()->name)->toBe('Updated Budget')
        ->and($budget->fresh()->amount)->toBe('500.00')
        ->and($budget->fresh()->type)->toBe(BudgetType::Goal)
        ->and($budget->fresh()->formattedAmount())->toBe('500.00 $');

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'name' => 'Updated Budget',
        'amount' => 500,
        'type' => BudgetType::Goal->value,
    ]);
});

it('soft deletes an owned budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->delete(route('budgets.destroy', $budget))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.budget_deleted'));

    $this->assertSoftDeleted($budget);
    $this->assertSoftDeleted('budgets', [
        'id' => $budget->id,
    ]);
});

it('validates budget input on create', function () {
    actingAsVerifiedUser();

    $this->from(route('budgets.create'))
        ->post(route('budgets.store'))
        ->assertRedirect(route('budgets.create'))
        ->assertSessionHasErrors(['name', 'amount', 'type']);
});

it('validates budget input on update', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->from(route('budgets.edit', $budget))
        ->put(route('budgets.update', $budget), [
            'name' => '',
            'amount' => '',
            'type' => '',
        ])
        ->assertRedirect(route('budgets.edit', $budget))
        ->assertSessionHasErrors(['name', 'amount', 'type']);
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Budgets/Show')
            ->has('budget', fn (Assert $page) => $page
                ->where('id', $budget->id)
                ->where('name', 'Emergency Fund')
                ->etc()
            )
        );
});

it('shows the edit budget form for the owner', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'name' => 'Viaje a las Vegas',
        'amount' => 1000,
    ]);

    $this->get(route('budgets.edit', $budget))
        ->assertSuccessful()
        ->assertSee('Viaje a las Vegas')
        ->assertSee('1000');
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
