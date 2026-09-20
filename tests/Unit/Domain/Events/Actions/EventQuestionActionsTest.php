<?php

use App\Domain\Events\Actions\CreateEventQuestion;
use App\Domain\Events\Actions\DeleteEventQuestion;
use App\Domain\Events\Actions\RegisterForEvent;
use App\Domain\Events\Actions\ReorderEventQuestions;
use App\Domain\Events\Actions\UpdateEventQuestion;
use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('creates questions with increasing positions', function () {
    $event = Event::factory()->create();
    $create = app(CreateEventQuestion::class);

    $first = $create($event, EventQuestionData::fromValidated([
        'kind' => 'short_text',
        'label_en' => 'Company',
    ]));
    $second = $create($event, EventQuestionData::fromValidated([
        'kind' => 'long_text',
        'label_en' => 'Notes',
        'help_en' => 'Optional.',
        'help_bn' => 'ঐচ্ছিক।',
        'label_bn' => 'নোট',
        'required' => true,
    ]));

    expect($first->position)->toBe(1)
        ->and($second->position)->toBe(2)
        ->and($second->required)->toBeTrue()
        ->and($second->label_bn)->toBe('নোট')
        ->and($second->help_bn)->toBe('ঐচ্ছিক।')
        ->and($first->options)->toBeNull();
});

test('choice options are trimmed and text options dropped', function () {
    $choice = EventQuestionData::fromValidated([
        'kind' => 'single_choice',
        'label_en' => 'Size',
        'options' => [' S ', 'M'],
    ]);
    $text = EventQuestionData::fromValidated([
        'kind' => 'short_text',
        'label_en' => 'Company',
        'options' => ['ignored'],
    ]);

    expect($choice->options)->toBe(['S', 'M'])
        ->and($choice->kind)->toBe(QuestionKind::SingleChoice)
        ->and($text->options)->toBeNull();
});

test('non array options fall back to an empty list', function () {
    $data = EventQuestionData::fromValidated([
        'kind' => 'multiple_choice',
        'label_en' => 'Topics',
        'options' => 'not-an-array',
    ]);

    expect($data->options)->toBe([]);
});

test('updates a question and keeps its position', function () {
    $question = EventQuestion::factory()->create(['position' => 5]);

    $updated = app(UpdateEventQuestion::class)($question, EventQuestionData::fromValidated([
        'kind' => 'multiple_choice',
        'label_en' => 'Topics',
        'options' => ['Queues', 'Testing'],
    ]));

    expect($updated->position)->toBe(5)
        ->and($updated->kind)->toBe(QuestionKind::MultipleChoice)
        ->and($updated->optionList())->toBe(['Queues', 'Testing']);
});

test('deletes a question', function () {
    $question = EventQuestion::factory()->create();

    app(DeleteEventQuestion::class)($question);

    expect(EventQuestion::query()->count())->toBe(0);
});

test('reorders questions and ignores foreign ids', function () {
    $event = Event::factory()->create();
    $first = EventQuestion::factory()->create(['event_id' => $event->id, 'position' => 0]);
    $second = EventQuestion::factory()->create(['event_id' => $event->id, 'position' => 1]);
    $foreign = EventQuestion::factory()->create(['position' => 9]);

    app(ReorderEventQuestions::class)($event, [$second->id, $foreign->id, $first->id]);

    expect($second->refresh()->position)->toBe(0)
        ->and($first->refresh()->position)->toBe(2)
        ->and($foreign->refresh()->position)->toBe(9);
});

test('a closed event rejects registration', function () {
    $event = Event::factory()->published()->registrationClosed()->create();
    $user = User::factory()->withCompleteProfile()->create();

    expect(fn () => app(RegisterForEvent::class)($event, $user))
        ->toThrow(ValidationException::class, __('events.rsvp.closed'));

    expect(EventRegistration::query()->count())->toBe(0);
});

test('a non string answer for a text question is rejected', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $user = User::factory()->withCompleteProfile()->create();

    expect(fn () => app(RegisterForEvent::class)($event, $user, [$question->id => 42]))
        ->toThrow(ValidationException::class, __('events.questions.invalid'));
});

test('a non string member of a multiple choice answer is rejected', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = User::factory()->withCompleteProfile()->create();

    expect(fn () => app(RegisterForEvent::class)($event, $user, [$question->id => [42]]))
        ->toThrow(ValidationException::class, __('events.questions.invalid'));
});

test('a non array non string multiple choice answer is rejected', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = User::factory()->withCompleteProfile()->create();

    expect(fn () => app(RegisterForEvent::class)($event, $user, [$question->id => 7]))
        ->toThrow(ValidationException::class, __('events.questions.invalid'));
});

test('null answers count as unanswered', function () {
    $event = Event::factory()->published()->create();
    $text = EventQuestion::factory()->create(['event_id' => $event->id]);
    $multi = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = User::factory()->withCompleteProfile()->create();

    $registration = app(RegisterForEvent::class)($event, $user, [
        $text->id => null,
        $multi->id => null,
    ]);

    expect($registration->answers()->count())->toBe(0);
});

test('duplicate multiple choice values are collapsed', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = User::factory()->withCompleteProfile()->create();

    $registration = app(RegisterForEvent::class)($event, $user, [
        $question->id => ['APIs', 'APIs'],
    ]);

    expect($registration->answers()->firstOrFail()->values())->toBe(['APIs']);
});

test('an answer model exposes scalar values as a list', function () {
    $answer = EventQuestion::factory()->create();
    $registration = EventRegistration::factory()->create(['event_id' => $answer->event_id]);
    $row = $registration->answers()->create([
        'event_question_id' => $answer->id,
        'value' => 'Cefalo',
    ]);

    expect($row->values())->toBe(['Cefalo'])
        ->and($row->question?->is($answer))->toBeTrue()
        ->and($row->registration?->is($registration))->toBeTrue();
});

test('a question belongs to its event', function () {
    $event = Event::factory()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);

    expect($question->event?->is($event))->toBeTrue()
        ->and($question->optionList())->toBe([]);
});
