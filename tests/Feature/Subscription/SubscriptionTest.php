<?php

use App\Enums\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Cashier\Subscription;
use Mockery\MockInterface;

test('guest cannot access plans page', function () {
    $response = $this->get(route('plans'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can access plans page', function () {
    actingAsVerifiedUser();

    $response = $this->get(route('plans'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Subscriptions/Manage')
            ->has('subscription')
        );
});

test('authenticated user can access subscription manage page', function () {
    actingAsVerifiedUser();

    $response = $this->get(route('subscription.manage'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Subscriptions/Manage')
            ->has('subscription')
        );
});

test('checkout route aborts with 404 for invalid plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.checkout', ['plan' => 'super_ultra']));

    $response->assertNotFound();
});

test('checkout route redirects for valid monthly plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.checkout', ['plan' => 'monthly']));

    $response->assertRedirect();
});

test('checkout route redirects for valid yearly plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.checkout', ['plan' => 'yearly']));

    $response->assertRedirect();
});

test('user with USD currency uses USD price configuration', function () {
    $user = actingAsVerifiedUser(['currency' => Currency::USD]);

    expect($user->currency)->toBe(Currency::USD);

    $response = $this->post(route('subscription.checkout', ['plan' => 'monthly']));

    $response->assertRedirect();
});

test('swap route aborts 404 for invalid plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.swap', ['plan' => 'unknown']));

    $response->assertNotFound();
});

test('user without active subscription swapping plan handles gracefully', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.swap', ['plan' => 'yearly']));

    $response->assertRedirect();
});

test('user canceling non-existent subscription handles gracefully', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.cancel'));

    $response->assertRedirect();
});

test('user resuming non-existent subscription handles gracefully', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.resume'));

    $response->assertRedirect();
});

test('user on yearly plan cannot swap directly to monthly plan', function () {
    $user = actingAsVerifiedUser();

    $user->subscriptions()->forceCreate([
        'type' => 'default',
        'stripe_id' => 'sub_yearly_test',
        'stripe_status' => 'active',
        'stripe_price' => config('services.stripe.price_ai_yearly') ?? 'price_yearly',
    ]);

    $response = $this->post(route('subscription.swap', ['plan' => 'monthly']));

    $response->assertSessionHas('error');
});

test('user on grace period resuming and swapping plan calls resume and swap', function () {
    $user = actingAsVerifiedUser();

    $fakeSub = new class extends Subscription
    {
        public bool $resumed = false;

        public ?string $swappedTo = null;

        public function onGracePeriod(): bool
        {
            return true;
        }

        public function resume(): static
        {
            $this->resumed = true;

            return $this;
        }

        public function swap($prices, array $options = []): static
        {
            $this->swappedTo = is_array($prices) ? json_encode($prices) : (string) $prices;

            return $this;
        }

        public function getKey(): int
        {
            return 1;
        }
    };
    $fakeSub->forceFill([
        'type' => 'default',
        'stripe_status' => 'active',
    ]);

    $user->setRelation('subscriptions', new Collection([$fakeSub]));

    $response = $this->actingAs($user)->post(route('subscription.swap', ['plan' => 'yearly']));

    $response->assertRedirect();
    expect($fakeSub->resumed)->toBeTrue()
        ->and($fakeSub->swappedTo)->not->toBeNull();
});

test('guest cannot access billing portal route', function () {
    $response = $this->get(route('billing'));

    $response->assertRedirect(route('login'));
});

test('authenticated user accessing billing portal calls redirectToBillingPortal', function () {
    $user = actingAsVerifiedUser();

    /** @var User|MockInterface $userMock */
    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('redirectToBillingPortal')
        ->once()
        ->with(route('subscription.manage'))
        ->andReturn(redirect('https://billing.stripe.com/session/test_123'));

    $this->be($userMock);

    $response = $this->get(route('billing'));

    $response->assertRedirect('https://billing.stripe.com/session/test_123');
});
