<?php

use Inertia\Testing\AssertableInertia as Assert;

// Must match the `sections` prop in resources/js/pages/{terms,privacy}.tsx.
dataset('legal pages', [
    'terms' => ['terms', 'Terms of Use', 'ব্যবহারের শর্তাবলি', 13],
    'privacy' => ['privacy', 'Privacy Policy', 'গোপনীয়তা নীতি', 12],
]);

test('guests can view the page', function (string $page, string $title) {
    $this->get(route($page))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component($page)
            ->where('json_ld.0.itemListElement.1.item', route($page))
            ->where('translations', fn ($translations) => $translations["{$page}.title"] === $title)
        );
})->with('legal pages');

test('the page uses bangla copy', function (string $page, string $title, string $banglaTitle) {
    $this->post(route('locale.update'), ['locale' => 'bn']);

    $this->get(route($page))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('translations', fn ($translations) => $translations["{$page}.title"] === $banglaTitle)
        );
})->with('legal pages');

test('every section has a title and body in both locales', function (string $page, string $title, string $banglaTitle, int $sections) {
    foreach (['en', 'bn'] as $locale) {
        $translations = json_decode(file_get_contents(lang_path("{$locale}.json")), true);

        foreach (range(1, $sections) as $section) {
            expect($translations)->toHaveKeys(["{$page}.{$section}.title", "{$page}.{$section}.body"]);
        }

        expect($translations)->not->toHaveKey("{$page}.".($sections + 1).'.title');
    }
})->with('legal pages');
