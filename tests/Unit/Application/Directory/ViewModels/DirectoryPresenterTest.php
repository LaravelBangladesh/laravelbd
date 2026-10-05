<?php

use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;

test('profile links resolve handles and absolute urls', function () {
    $user = new User([
        'slug' => 'ada-lovelace',
        'name' => 'Ada Lovelace',
        'website' => 'ada.dev',
        'github' => 'ada',
        'linkedin' => 'https://www.linkedin.com/in/ada',
        'x' => '@ada',
    ]);

    expect(DirectoryPresenter::links($user))->toBe([
        ['key' => 'website', 'url' => 'https://ada.dev'],
        ['key' => 'github', 'url' => 'https://github.com/ada'],
        ['key' => 'linkedin', 'url' => 'https://www.linkedin.com/in/ada'],
        ['key' => 'x', 'url' => 'https://x.com/ada'],
    ]);
});

test('empty profile fields are omitted from links', function () {
    expect(DirectoryPresenter::links(new User(['name' => 'Ada Lovelace'])))->toBe([]);
});

test('handles already given as full urls are kept as is', function () {
    $company = new Company([
        'slug' => 'engines',
        'name' => 'Engines',
        'github' => 'http://github.com/engines',
        'x' => 'https://x.com/engines',
    ]);

    expect(DirectoryPresenter::links($company))->toBe([
        ['key' => 'github', 'url' => 'http://github.com/engines'],
        ['key' => 'x', 'url' => 'https://x.com/engines'],
    ]);
});

test('cards describe people and companies with the same shape', function () {
    $person = DirectoryPresenter::card(new User([
        'slug' => 'ada-lovelace',
        'name' => 'Ada Lovelace',
        'title' => 'Mathematician',
        'company' => 'Analytical Co',
        'city' => 'Dhaka',
        'mobile_number' => '+8801712345678',
    ]));
    $company = DirectoryPresenter::card(new Company([
        'slug' => 'engines',
        'name' => 'Engines',
        'title' => 'Software studio',
    ]));

    expect(array_keys($person))->toBe(['id', 'slug', 'name', 'title', 'company', 'city', 'kind', 'kind_label', 'photo_url'])
        ->and($person['kind'])->toBe('person')
        ->and($person['company'])->toBe('Analytical Co')
        ->and(json_encode($person))->not->toContain('1712345678')
        ->and(array_keys($company))->toBe(array_keys($person))
        ->and($company['kind'])->toBe('company')
        ->and($company['company'])->toBeNull()
        ->and($company['photo_url'])->toBeNull();
});
