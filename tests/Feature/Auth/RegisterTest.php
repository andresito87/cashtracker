<?php

use App\Enums\Currency;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('renders the registration form for guests', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee(__('messages.sign_up'));
});

it('renders the registration form for guests in Spanish', function () {
    $this->withSession(['locale' => 'es'])
        ->get(route('register'))
        ->assertSuccessful()
        ->assertSee(__('messages.sign_up'));
});

it('registers a new user and redirects to email verification notice', function () {
    Notification::fake();

    $this->post(route('register.store'), validRegistrationPayload([
        'email' => 'new-user@example.com',
        'currency' => 'USD',
    ]))
        ->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'new-user@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('user')
        ->and($user->currency)->toBe(Currency::USD)
        ->and($user->email_verified_at)->toBeNull();

    $this->assertDatabaseHas('users', [
        'email' => 'new-user@example.com',
        'currency' => 'USD',
    ]);

    $this->assertAuthenticatedAs($user);

    /** @noinspection PhpUnhandledExceptionInspection */
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('dispatches the registered event when a user signs up', function () {
    Event::fake([Registered::class]);
    Notification::fake();

    $this->post(route('register.store'), validRegistrationPayload([
        'email' => 'registered-event@example.com',
    ]));

    Event::assertDispatched(Registered::class, fn (Registered $event) => $event->user instanceof User && $event->user->email === 'registered-event@example.com');
});

it('validates registration input', function () {
    $this->post(route('register.store'))
        ->assertSessionHasErrors(['name', 'email', 'currency', 'password']);
});

it('validates invalid currency during registration', function () {
    $this->post(route('register.store'), validRegistrationPayload([
        'currency' => 'INVALID',
    ]))
        ->assertSessionHasErrors(['currency']);
});

it('requires a unique email during registration', function () {
    createVerifiedUser([
        'email' => 'existing@example.com',
    ]);

    $this->post(route('register.store'), validRegistrationPayload([
        'email' => 'existing@example.com',
    ]))
        ->assertSessionHasErrors(['email']);
});

it('completes the full registration to email verification and dashboard access flow', function () {
    Notification::fake();

    // 1. User registration
    $this->post(route('register.store'), validRegistrationPayload([
        'email' => 'full-flow@example.com',
    ]))->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'full-flow@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();

    // 2. Attempt to access the dashboard while unverified -> Redirects to notice page
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));

    // 3. Retrieve dispatched mail notification and simulate clicking the verification link
    /** @noinspection PhpUnhandledExceptionInspection */
    Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user) {
        $mail = $notification->toMail($user);
        $rendered = (string) $mail->render();

        // Extract the verification URL from the CTA button
        preg_match('/href="([^"]+verify-email[^"]+)"/', $rendered, $matches);
        $verificationUrl = html_entity_decode($matches[1]);

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect(route('dashboard'));

        return true;
    });

    // 4. Verify user becomes verified and successfully accesses the dashboard
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('renders the verification email through the real mail channel on registration', function () {
    config(['mail.default' => 'array']);

    $this->post(route('register.store'), validRegistrationPayload([
        'email' => 'array-channel@example.com',
    ]))->assertRedirect(route('verification.notice'));

    $transport = app('mail.manager')->mailer()->getSymfonyTransport();
    assert($transport instanceof ArrayTransport);
    $messages = $transport->messages();
    expect($messages)->not->toBeEmpty();

    $rendered = str_replace("=\r\n", '', $messages->first()->toString());
    expect($rendered)->toContain('Subject: '.__('messages.email_verify_subject'))
        ->and($rendered)->toContain('To: array-channel@example.com')
        ->and($rendered)->toContain('/verify-email/');
});
