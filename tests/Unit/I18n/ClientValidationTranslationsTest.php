<?php

test('client validation messages exist in both locales', function () {
    $keys = [
        'validation.required',
        'validation.email',
        'validation.url',
        'validation.invalid',
        'validation.pattern',
        'validation.code',
        'validation.min_length',
        'validation.max_length',
        'validation.min_value',
        'validation.max_value',
    ];

    foreach (['en', 'bn'] as $locale) {
        $translations = json_decode(
            (string) file_get_contents(lang_path("{$locale}.json")),
            true,
        );

        expect($translations)->toBeArray()->toHaveKeys($keys);

        foreach ($keys as $key) {
            expect($translations[$key])->toBeString()->not->toBeEmpty();
        }
    }
});
