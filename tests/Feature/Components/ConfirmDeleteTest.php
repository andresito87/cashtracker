<?php

use App\Models\Budget;
use App\View\Components\ConfirmDelete;

it('renders the confirm delete component with default properties', function () {
    $view = $this->component(ConfirmDelete::class, [
        'id' => 'test-delete-dialog',
        'action' => '/budgets/1',
    ]);

    $view->assertSee('test-delete-dialog')
        ->assertSee('/budgets/1')
        ->assertSee(__('messages.confirm_delete_title'))
        ->assertSee(__('messages.confirm_delete_message'))
        ->assertSee(__('messages.delete'))
        ->assertSee(__('messages.cancel'))
        ->assertSee('command="close"', false)
        ->assertSee('commandfor="test-delete-dialog"', false);
});

it('renders the confirm delete component with custom title, message and action buttons', function () {
    $view = $this->component(ConfirmDelete::class, [
        'id' => 'custom-dialog-id',
        'action' => route('budgets.destroy', 42),
        'title' => 'Eliminar Presupuesto: Vacaciones',
        'message' => '¿Estás completamente seguro de borrar este presupuesto?',
        'confirmText' => 'Sí, Eliminar',
        'cancelText' => 'No, Cancelar',
    ]);

    $view->assertSee('custom-dialog-id')
        ->assertSee(route('budgets.destroy', 42))
        ->assertSee('Eliminar Presupuesto: Vacaciones')
        ->assertSee('¿Estás completamente seguro de borrar este presupuesto?')
        ->assertSee('Sí, Eliminar')
        ->assertSee('No, Cancelar')
        ->assertSee('command="close"', false)
        ->assertSee('commandfor="custom-dialog-id"', false);
});

it('renders custom slot content when provided to the confirm delete component', function () {
    $view = $this->blade(
        '<x-confirm-delete id="slot-modal" action="/delete-item" title="Atención"><p class="custom-warning">Esta operación no se puede revertir bajo ningún concepto.</p></x-confirm-delete>'
    );

    $view->assertSee('slot-modal')
        ->assertSee('/delete-item')
        ->assertSee('Atención')
        ->assertSee('Esta operación no se puede revertir bajo ningún concepto.', false);
});

use Inertia\Testing\AssertableInertia as Assert;

it('displays the budget detail page via inertia', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'name' => 'Vacaciones en París',
    ]);

    $response = $this->get(route('budgets.show', $budget));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Budgets/Show')
            ->has('budget', fn (Assert $page) => $page
                ->where('id', $budget->id)
                ->where('name', 'Vacaciones en París')
                ->etc()
            )
        );
});
