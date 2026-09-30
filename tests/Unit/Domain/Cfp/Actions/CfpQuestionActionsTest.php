<?php

use App\Domain\Cfp\Actions\CreateCfpQuestion;
use App\Domain\Cfp\Actions\DeleteCfpQuestion;
use App\Domain\Cfp\Actions\ReorderCfpQuestions;
use App\Domain\Cfp\Actions\UpdateCfpQuestion;
use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function cfpQuestionData(array $overrides = []): EventQuestionData
{
    return EventQuestionData::fromValidated([
        'kind' => 'short_text',
        'label_en' => 'Company',
        ...$overrides,
    ]);
}

test('appends questions with generated uuids in order', function () {
    $event = Event::factory()->create();
    $create = app(CreateCfpQuestion::class);

    $create($event, cfpQuestionData());
    $create($event, cfpQuestionData([
        'kind' => 'single_choice',
        'label_en' => 'Level',
        'label_bn' => 'স্তর',
        'help_en' => 'Pick one.',
        'help_bn' => 'একটি বাছুন।',
        'options' => ['Beginner', 'Advanced'],
        'required' => true,
    ]));

    $questions = $event->refresh()->cfp_questions;

    expect($questions)->toHaveCount(2)
        ->and(Str::isUuid($questions[0]['id']))->toBeTrue()
        ->and($questions[0]['options'])->toBeNull()
        ->and($questions[0]['required'])->toBeFalse()
        ->and($questions[1])->toBe([
            'id' => $questions[1]['id'],
            'kind' => 'single_choice',
            'label_en' => 'Level',
            'label_bn' => 'স্তর',
            'help_en' => 'Pick one.',
            'help_bn' => 'একটি বাছুন।',
            'options' => ['Beginner', 'Advanced'],
            'required' => true,
        ]);
});

test('updates a question in place and keeps its id', function () {
    $event = Event::factory()->create();
    app(CreateCfpQuestion::class)($event, cfpQuestionData());
    app(CreateCfpQuestion::class)($event, cfpQuestionData(['label_en' => 'Other']));
    [$first, $second] = $event->refresh()->cfp_questions;

    app(UpdateCfpQuestion::class)($event, $first['id'], cfpQuestionData([
        'kind' => 'long_text',
        'label_en' => 'Bio',
    ]));

    $questions = $event->refresh()->cfp_questions;

    expect($questions[0]['id'])->toBe($first['id'])
        ->and($questions[0]['kind'])->toBe('long_text')
        ->and($questions[0]['label_en'])->toBe('Bio')
        ->and($questions[1])->toBe($second);
});

test('deletes a question', function () {
    $event = Event::factory()->create();
    app(CreateCfpQuestion::class)($event, cfpQuestionData());
    app(CreateCfpQuestion::class)($event, cfpQuestionData(['label_en' => 'Other']));
    [$first, $second] = $event->refresh()->cfp_questions;

    app(DeleteCfpQuestion::class)($event, $first['id']);

    expect($event->refresh()->cfp_questions)->toBe([$second]);
});

test('reorders questions and ignores unknown ids', function () {
    $event = Event::factory()->create();
    app(CreateCfpQuestion::class)($event, cfpQuestionData());
    app(CreateCfpQuestion::class)($event, cfpQuestionData(['label_en' => 'Other']));
    [$first, $second] = $event->refresh()->cfp_questions;

    app(ReorderCfpQuestions::class)($event, [$second['id'], 'unknown', $first['id']]);

    expect($event->refresh()->cfp_questions)->toBe([$second, $first]);
});

test('actions tolerate an event without questions', function () {
    $event = Event::factory()->create();

    app(UpdateCfpQuestion::class)($event, 'missing', cfpQuestionData());
    app(DeleteCfpQuestion::class)($event, 'missing');
    app(ReorderCfpQuestions::class)($event, ['missing']);

    expect($event->refresh()->cfp_questions)->toBe([]);
});
