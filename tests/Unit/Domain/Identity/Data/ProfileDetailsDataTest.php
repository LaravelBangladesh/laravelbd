<?php

use App\Domain\Identity\Data\ProfileDetailsData;

test('from validated maps every field', function () {
    $data = ProfileDetailsData::fromValidated([
        'name' => 'Anik Rahman',
        'title' => 'Engineer',
        'company' => 'Acme',
        'city' => 'Dhaka',
        'bio_en' => 'Bio en',
        'bio_bn' => 'Bio bn',
        'website' => 'https://example.com',
        'github' => 'anik',
        'linkedin' => 'https://linkedin.com/in/anik',
        'x' => '@anik',
        'mobile_number' => '+8801712345678',
    ]);

    expect($data->attributes())->toBe([
        'name' => 'Anik Rahman',
        'title' => 'Engineer',
        'company' => 'Acme',
        'city' => 'Dhaka',
        'bio_en' => 'Bio en',
        'bio_bn' => 'Bio bn',
        'website' => 'https://example.com',
        'github' => 'anik',
        'linkedin' => 'https://linkedin.com/in/anik',
        'x' => '@anik',
        'mobile_number' => '+8801712345678',
    ]);
});

test('from validated nulls blank fields', function () {
    $data = ProfileDetailsData::fromValidated([
        'name' => 'Anik',
        'title' => '',
        'mobile_number' => null,
    ]);

    expect($data->title)->toBeNull()
        ->and($data->company)->toBeNull()
        ->and($data->mobileNumber)->toBeNull();
});
