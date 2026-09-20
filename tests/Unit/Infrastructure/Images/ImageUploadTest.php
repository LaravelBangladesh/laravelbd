<?php

use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

test('image uploads accept common raster types up to 50 mb', function () {
    $file = UploadedFile::fake()->image('portrait.png', 80, 80)->size(6000);

    $validator = Validator::make(
        ['photo' => $file],
        ['photo' => ImageUpload::rules()],
    );

    expect($validator->passes())->toBeTrue();
});

test('image uploads reject files over 50 mb and non images', function () {
    $tooBig = UploadedFile::fake()->image('huge.jpg')->size(51201);
    $pdf = UploadedFile::fake()->create('notes.pdf', 200, 'application/pdf');

    expect(Validator::make(['photo' => $tooBig], ['photo' => ImageUpload::rules()])->fails())->toBeTrue()
        ->and(Validator::make(['photo' => $pdf], ['photo' => ImageUpload::rules()])->fails())->toBeTrue();
});
