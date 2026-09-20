<?php

use Illuminate\Validation\Rules\Password;

test('outside production an eight character password is accepted', function () {
    expect(validator(['password' => 'abcdefgh'], ['password' => Password::default()])->fails())
        ->toBeFalse();
});

test('in production passwords must be twelve characters, mixed case, numbered and symbolled', function () {
    $this->app['env'] = 'production';

    $validator = validator(['password' => 'abcdefgh'], ['password' => Password::default()]);

    expect($validator->fails())->toBeTrue();

    $messages = implode(' ', $validator->errors()->get('password'));

    expect($messages)
        ->toContain('12')
        ->toContain('uppercase and one lowercase')
        ->toContain('one number')
        ->toContain('one symbol');
});
