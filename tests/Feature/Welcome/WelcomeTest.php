<?php

use App\Models\User;

it('renders the welcome landing page for guests in English', function () {
    $this->get('/?lang=en')
        ->assertSuccessful()
        ->assertSee(__('messages.landing_hero_title'))
        ->assertSee(__('messages.landing_hero_subtitle'))
        ->assertSee(__('messages.landing_cta_primary'))
        ->assertSee(__('messages.landing_feature_1_title'));
});

it('renders the welcome landing page for guests in Spanish', function () {
    $this->get('/?lang=es')
        ->assertSuccessful()
        ->assertSee(__('messages.landing_hero_title'))
        ->assertSee(__('messages.landing_hero_subtitle'))
        ->assertSee(__('messages.landing_cta_primary'))
        ->assertSee(__('messages.landing_feature_1_title'));
});

it('renders the welcome landing page for authenticated users with dashboard cta', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/?lang=es')
        ->assertSuccessful()
        ->assertSee(__('messages.landing_cta_dashboard'));
});
