<?php

use App\Application\Shared\ViewModels\Localized;

test('picks the current locale and falls back to english', function () {
    app()->setLocale('bn');

    expect(Localized::pick(['label_en' => 'Company', 'label_bn' => 'কোম্পানি'], 'label'))->toBe('কোম্পানি')
        ->and(Localized::pick(['label_en' => 'Company', 'label_bn' => ''], 'label'))->toBe('Company')
        ->and(Localized::pick(['label_en' => 'Company', 'label_bn' => null], 'label'))->toBe('Company')
        ->and(Localized::pick(['help_en' => null], 'help'))->toBe('');
});
