<?php

namespace App\Domain\Events;

use App\Domain\Events\Enums\QuestionKind;
use Illuminate\Validation\ValidationException;

/**
 * Validates submitted answers against a list of custom questions, shared by
 * event registration and talk proposals.
 */
final class QuestionAnswers
{
    /**
     * @param  array<int, array{id: string, kind: string, options: list<string>|null, required: bool}>  $questions
     * @param  array<string, mixed>  $answers  keyed by question id
     * @return array<string, string|list<string>> answered values keyed by question id
     *
     * @throws ValidationException
     */
    public static function validate(array $questions, array $answers): array
    {
        $byId = [];

        foreach ($questions as $question) {
            $byId[$question['id']] = $question;
        }

        $errors = [];
        $values = [];

        foreach ($answers as $questionId => $raw) {
            $question = $byId[(string) $questionId] ?? null;

            if ($question === null) {
                $errors['answers.'.$questionId] = [__('events.questions.unknown')];

                continue;
            }

            $value = self::normalise($question, $raw);

            if ($value === null) {
                $errors['answers.'.$questionId] = [__('events.questions.invalid')];

                continue;
            }

            if ($value !== [] && $value !== '') {
                $values[$question['id']] = $value;
            }
        }

        foreach ($byId as $id => $question) {
            if ($question['required'] && ! isset($values[$id])) {
                $errors['answers.'.$id] = [__('events.questions.required')];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $values;
    }

    /**
     * Returns the stored value, `null` when the shape or the choice is invalid.
     *
     * @param  array{id: string, kind: string, options: list<string>|null, required: bool}  $question
     * @return string|list<string>|null
     */
    private static function normalise(array $question, mixed $raw): string|array|null
    {
        $kind = QuestionKind::from($question['kind']);
        $options = $question['options'] ?? [];

        // Empty inputs reach the action as null; treat them as "not answered"
        // so the required check, not the shape check, reports them.
        if ($raw === null) {
            return $kind === QuestionKind::MultipleChoice ? [] : '';
        }

        if ($kind === QuestionKind::MultipleChoice) {
            if (is_string($raw)) {
                $raw = [$raw];
            }

            if (! is_array($raw)) {
                return null;
            }

            $selected = [];

            foreach ($raw as $option) {
                if (! is_string($option) || ! in_array($option, $options, true)) {
                    return null;
                }

                $selected[] = $option;
            }

            return array_values(array_unique($selected));
        }

        if (! is_string($raw)) {
            return null;
        }

        $value = trim($raw);

        if ($kind === QuestionKind::SingleChoice
            && $value !== ''
            && ! in_array($value, $options, true)) {
            return null;
        }

        return $value;
    }
}
