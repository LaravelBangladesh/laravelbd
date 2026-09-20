<?php

use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;

test('profile links resolve handles and absolute urls', function () {
    $listing = new DirectoryListing([
        'slug' => 'ada-lovelace',
        'kind' => DirectoryKind::Person,
        'status' => DirectoryStatus::Published,
        'name' => 'Ada Lovelace',
        'website' => 'ada.dev',
        'github' => 'ada',
        'linkedin' => 'https://www.linkedin.com/in/ada',
        'x' => '@ada',
    ]);

    expect(DirectoryPresenter::links($listing))->toBe([
        ['key' => 'website', 'url' => 'https://ada.dev'],
        ['key' => 'github', 'url' => 'https://github.com/ada'],
        ['key' => 'linkedin', 'url' => 'https://www.linkedin.com/in/ada'],
        ['key' => 'x', 'url' => 'https://x.com/ada'],
    ]);
});

test('empty profile fields are omitted from links', function () {
    $listing = new DirectoryListing([
        'slug' => 'ada-lovelace',
        'kind' => DirectoryKind::Person,
        'status' => DirectoryStatus::Published,
        'name' => 'Ada Lovelace',
    ]);

    expect(DirectoryPresenter::links($listing))->toBe([]);
});

test('handles already given as full urls are kept as is', function () {
    $listing = new DirectoryListing([
        'slug' => 'ada-lovelace',
        'kind' => DirectoryKind::Person,
        'status' => DirectoryStatus::Published,
        'name' => 'Ada Lovelace',
        'github' => 'http://github.com/ada',
        'x' => 'https://x.com/ada',
    ]);

    expect(DirectoryPresenter::links($listing))->toBe([
        ['key' => 'github', 'url' => 'http://github.com/ada'],
        ['key' => 'x', 'url' => 'https://x.com/ada'],
    ]);
});
