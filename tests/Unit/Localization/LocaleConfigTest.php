<?php

it('exposes the application locale whitelist under config(app.available_locales)', function () {
    expect(config('app.available_locales'))->toBe(['en', 'es']);
});

it('keeps the whitelist literal only inside config/app.php', function () {
    $sources = collect([
        app_path('Http/Middleware/SetLocale.php'),
        app_path('Http/Controllers/LanguageController.php'),
        base_path('routes/web.php'),
    ])
        ->filter(fn (string $path) => is_file($path))
        ->mapWithKeys(fn (string $path) => [$path => file_get_contents($path)])
        ->implode('');

    expect($sources)->not->toContain("['en', 'es']")
        ->and($sources)->not->toContain("['en','es']");
});
