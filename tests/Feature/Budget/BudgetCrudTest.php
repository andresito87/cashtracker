<?php

use App\Enums\BudgetType;
use App\Enums\Currency;
use App\Models\Budget;
use Inertia\Testing\AssertableInertia as Assert;

it('lists only the authenticated users budgets for regular users', function () {
    $user = createVerifiedUser();
    $otherUser = createVerifiedUser();

    $ownBudget = Budget::factory()->for($user)->create(['name' => 'My Budget']);
    $otherBudget = Budget::factory()->for($otherUser)->create(['name' => 'Other Budget']);

    $response = $this->actingAs($user)
        ->get(route('budgets.index'))
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

    $response = $this->actingAs($admin)
        ->get(route('budgets.index'))
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
    $user = createVerifiedUser(['currency' => Currency::USD]);

    $this->actingAs($user)
        ->post(route('budgets.store'), validBudgetPayload([
            'name' => 'Travel Fund',
            'type' => 'goal',
        ]))
        ->assertRedirect(route('budgets.show', Budget::query()->first()))
        ->assertSessionHas('status', __('messages.budget_created'));

    $budget = Budget::query()->first();

    expect($budget)->not->toBeNull()
        ->and($budget->user_id)->toBe($user->id)
        ->and($budget->name)->toBe('Travel Fund')
        ->and($budget->type)->toBe(BudgetType::Goal)
        ->and($budget->formattedAmount())->toBe('350.50 $');

    app()->setLocale('es');
    expect($budget->formattedAmount())->toBe('350,50 $');
    app()->setLocale('en');

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'name' => 'Travel Fund',
        'type' => BudgetType::Goal->value,
    ]);
});

it('updates an owned budget', function () {
    $user = createVerifiedUser(['currency' => Currency::USD]);
    $budget = Budget::factory()->for($user)->create([
        'type' => BudgetType::General,
    ]);

    $this->actingAs($user)
        ->put(route('budgets.update', $budget), validBudgetPayload([
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

    app()->setLocale('es');
    expect($budget->fresh()->formattedAmount())->toBe('500,00 $');
    app()->setLocale('en');

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'name' => 'Updated Budget',
        'amount' => 500,
        'type' => BudgetType::Goal->value,
    ]);
});

it('soft deletes an owned budget', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('budgets.destroy', $budget))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.budget_deleted'));

    $this->assertSoftDeleted($budget);
    $this->assertSoftDeleted('budgets', [
        'id' => $budget->id,
    ]);
});

it('shows the create budget form for verified users', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)
        ->get(route('budgets.create'))
        ->assertSuccessful();
});

it('shows an owned budget detail page', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create(['name' => 'Emergency Fund']);

    $this->actingAs($user)
        ->get(route('budgets.show', $budget))
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
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'name' => 'Viaje a las Vegas',
        'amount' => 1000,
    ]);

    $this->actingAs($user)
        ->get(route('budgets.edit', $budget))
        ->assertSuccessful()
        ->assertSee('Viaje a las Vegas')
        ->assertSee('1000');
});

it('displays translated empty state message in Spanish and English when user has no budgets', function () {
    $user = createVerifiedUser();

    app()->setLocale('es');
    $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('No hay presupuestos todavía.')
        ->assertSee('Empieza creando tu primer presupuesto');

    app()->setLocale('en');
    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('No budgets yet.')
        ->assertSee('Start by creating your first budget');
});

it('creates and updates a budget with optional description', function () {
    $user = createVerifiedUser();

    // Create with description
    $this->actingAs($user)
        ->post(route('budgets.store'), validBudgetPayload([
            'name' => 'Vacation Fund',
            'description' => 'Saving up for summer trip',
        ]))->assertRedirect();

    $budget = Budget::query()->where('name', 'Vacation Fund')->first();
    expect($budget->description)->toBe('Saving up for summer trip');

    // Update description
    $this->actingAs($user)
        ->put(route('budgets.update', $budget), validBudgetPayload([
            'name' => 'Vacation Fund Updated',
            'description' => 'Saving up for winter trip',
        ]))->assertRedirect();

    expect($budget->fresh()->description)->toBe('Saving up for winter trip');
});

it('excludes soft deleted budgets from budget index listing', function () {
    $user = createVerifiedUser();
    $activeBudget = Budget::factory()->for($user)->create(['name' => 'Active Budget']);
    $deletedBudget = Budget::factory()->for($user)->create(['name' => 'Deleted Budget']);

    $deletedBudget->delete();

    $this->actingAs($user)
        ->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('Active Budget')
        ->assertDontSee('Deleted Budget');

    $this->assertSoftDeleted('budgets', [
        'id' => $deletedBudget->id,
    ]);

    $this->assertDatabaseHas('budgets', [
        'id' => $activeBudget->id,
        'deleted_at' => null,
    ]);
});
