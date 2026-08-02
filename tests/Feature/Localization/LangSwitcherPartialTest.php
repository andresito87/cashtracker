<?php

it('renders a single toggle link switching to the other locale', function () {
    $response = $this->get(route('welcome'));

    // Default locale is 'en', so the toggle should point to 'es'
    $response->assertSee('?lang=es', false);
    $response->assertDontSee('?lang=en', false);

    // Switch to es and verify the toggle now points to en
    $response = $this->get(route('welcome', ['lang' => 'es']));
    $response->assertSee('?lang=en', false);
    $response->assertDontSee('?lang=es', false);
});

it('renders the language switcher through a single shared Blade partial on every layout', function () {
    $welcome = $this->get(route('welcome'))->getContent();

    // Each layout must @include('components.lang-switcher') rather than inlining
    // the switcher markup with a direct fullUrlWithQuery /?lang= link.
    foreach ([
        base_path('resources/views/layouts/base.blade.php'),
        base_path('resources/views/layouts/app.blade.php'),
        base_path('resources/views/layouts/inertia.blade.php'),
    ] as $layoutPath) {
        $layout = file_get_contents($layoutPath);

        expect($layout)->toContain("@include('components.lang-switcher')")
            ->and($layout)->not->toContain("fullUrlWithQuery(['lang' => ");
    }

    expect($welcome)->toContain('?lang=');
});
