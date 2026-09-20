<?php

use App\Domain\Events\Data\SpeakerData;

test('builds speaker data from validated input', function () {
    $data = SpeakerData::fromValidated([
        'name' => 'Grace Hopper',
        'title' => 'Rear Admiral',
        'company' => 'Navy',
        'bio_en' => 'Compilers',
        'bio_bn' => null,
        'website' => 'https://example.test',
        'github' => 'gracehopper',
        'linkedin' => 'https://linkedin.test/in/grace',
        'x' => 'grace',
    ]);

    expect($data->name)->toBe('Grace Hopper')
        ->and($data->bioBn)->toBeNull()
        ->and($data->attributes())->toBe([
            'name' => 'Grace Hopper',
            'title' => 'Rear Admiral',
            'company' => 'Navy',
            'bio_en' => 'Compilers',
            'bio_bn' => null,
            'website' => 'https://example.test',
            'github' => 'gracehopper',
            'linkedin' => 'https://linkedin.test/in/grace',
            'x' => 'grace',
        ]);
});

test('defaults every optional speaker field to null', function () {
    $data = SpeakerData::fromValidated(['name' => 'Ada Lovelace']);

    expect($data->title)->toBeNull()
        ->and($data->company)->toBeNull()
        ->and($data->website)->toBeNull()
        ->and($data->x)->toBeNull();
});
