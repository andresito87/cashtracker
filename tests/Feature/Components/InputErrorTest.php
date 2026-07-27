<?php

use App\Models\Budget;
use Inertia\Testing\AssertableInertia as Assert;

it('renders blade input error component with error messages', function () {
    $view = $this->blade('<x-input-error :messages="$messages" />', [
        'messages' => ['El campo nombre es obligatorio.', 'El nombre es demasiado corto.'],
    ]);

    $view->assertSee('text-red-500 text-sm mt-1', false)
        ->assertSee('El campo nombre es obligatorio.')
        ->assertSee('El nombre es demasiado corto.');
});

it('renders blade input error component when given a single string message', function () {
    $view = $this->blade('<x-input-error :messages="$messages" />', [
        'messages' => 'El monto debe ser un número válido.',
    ]);

    $view->assertSee('text-red-500 text-sm mt-1', false)
        ->assertSee('El monto debe ser un número válido.');
});

it('does not render blade input error HTML when messages are empty', function () {
    $view = $this->blade('<x-input-error :messages="$messages" />', [
        'messages' => null,
    ]);

    $view->assertDontSee('text-red-500');
});

it('uses blade input error component in blade views like login and register', function () {
    $loginContent = file_get_contents(resource_path('views/auth/login.blade.php'));
    $registerContent = file_get_contents(resource_path('views/auth/register.blade.php'));
    $budgetFormContent = file_get_contents(resource_path('views/components/budget-form.blade.php'));

    expect($loginContent)->toContain('<x-input-error')
        ->and($registerContent)->toContain('<x-input-error')
        ->and($budgetFormContent)->toContain('<x-input-error');
});

it('uses inertia react page and frontend input error component for expense modal', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $response = $this->get(route('budgets.show', $budget));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('Budgets/Show'));

    $expenseFormJs = file_get_contents(resource_path('js/Components/ExpenseForm.tsx'));
    expect($expenseFormJs)->toContain("import {InputError} from '@/Components/InputError'")
        ->and($expenseFormJs)->toContain('<InputError message={errors.name}')
        ->and($expenseFormJs)->toContain('<InputError message={errors.amount}')
        ->and($expenseFormJs)->toContain('<InputError message={errors.category}');
});
