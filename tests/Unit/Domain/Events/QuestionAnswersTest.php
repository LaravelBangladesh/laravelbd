<?php

use App\Domain\Events\QuestionAnswers;
use Illuminate\Validation\ValidationException;

function answerQuestions(): array
{
    return [
        ['id' => 'text', 'kind' => 'short_text', 'options' => null, 'required' => true],
        ['id' => 'size', 'kind' => 'single_choice', 'options' => ['S', 'M'], 'required' => false],
        ['id' => 'topics', 'kind' => 'multiple_choice', 'options' => ['APIs', 'Queues'], 'required' => false],
    ];
}

function answerErrors(array $answers): array
{
    try {
        QuestionAnswers::validate(answerQuestions(), $answers);
    } catch (ValidationException $exception) {
        return $exception->errors();
    }

    return [];
}

test('returns trimmed answered values keyed by question id', function () {
    expect(QuestionAnswers::validate(answerQuestions(), [
        'text' => '  Cefalo ',
        'size' => 'M',
        'topics' => 'APIs',
    ]))->toBe([
        'text' => 'Cefalo',
        'size' => 'M',
        'topics' => ['APIs'],
    ]);
});

test('leaves blank optional answers out', function () {
    expect(QuestionAnswers::validate(answerQuestions(), [
        'text' => 'Cefalo',
        'size' => '',
        'topics' => null,
    ]))->toBe(['text' => 'Cefalo']);
});

test('reports unknown, invalid and missing required answers per question', function () {
    expect(answerErrors([
        'nope' => 'x',
        'size' => 'XL',
        'topics' => ['APIs', 'Testing'],
    ]))->toBe([
        'answers.nope' => [__('events.questions.unknown')],
        'answers.size' => [__('events.questions.invalid')],
        'answers.topics' => [__('events.questions.invalid')],
        'answers.text' => [__('events.questions.required')],
    ]);
});

test('an empty question list accepts no answers', function () {
    expect(QuestionAnswers::validate([], []))->toBe([]);
});
