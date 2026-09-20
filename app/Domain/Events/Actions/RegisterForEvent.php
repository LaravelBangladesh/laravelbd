<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RegisterForEvent
{
    /**
     * @param  array<string, mixed>  $answers  keyed by question id
     */
    public function __invoke(Event $event, User $user, array $answers = []): EventRegistration
    {
        if (! $event->isPublished() || ! $event->isUpcoming()) {
            throw ValidationException::withMessages([
                'event' => __('events.rsvp.unavailable'),
            ]);
        }

        if (! $event->registration_enabled) {
            throw ValidationException::withMessages([
                'event' => __('events.rsvp.closed'),
            ]);
        }

        if (! $user->hasCompleteProfile()) {
            throw ValidationException::withMessages([
                'profile' => __('profile.incomplete'),
            ]);
        }

        return DB::transaction(function () use ($event, $user, $answers) {
            Event::query()->whereKey($event->id)->lockForUpdate()->first();

            /** @var Collection<string, EventQuestion> $questions */
            $questions = $event->questions()->get()->keyBy('id');
            $values = $this->validateAnswers($questions, $answers);

            $existing = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null && $existing->status !== RegistrationStatus::Cancelled) {
                return $existing;
            }

            $registeredCount = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('status', RegistrationStatus::Registered)
                ->count();

            $status = $event->capacity !== null && $registeredCount >= $event->capacity
                ? RegistrationStatus::Waitlisted
                : RegistrationStatus::Registered;

            if ($existing !== null) {
                $existing->forceFill([
                    'status' => $status,
                    'registered_at' => now(),
                ])->save();

                $existing->answers()->delete();
                $this->storeAnswers($existing, $values);

                return $existing;
            }

            $registration = EventRegistration::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $status,
                'registered_at' => now(),
            ]);

            $this->storeAnswers($registration, $values);

            return $registration;
        });
    }

    /**
     * @param  Collection<string, EventQuestion>  $questions
     * @param  array<string, mixed>  $answers
     * @return array<string, string|list<string>>
     */
    private function validateAnswers($questions, array $answers): array
    {
        $errors = [];
        $values = [];

        foreach ($answers as $questionId => $raw) {
            $question = $questions->get((string) $questionId);

            if (! $question instanceof EventQuestion) {
                $errors['answers.'.$questionId] = [__('events.questions.unknown')];

                continue;
            }

            $value = $this->normalise($question, $raw);

            if ($value === null) {
                $errors['answers.'.$questionId] = [__('events.questions.invalid')];

                continue;
            }

            if ($value !== [] && $value !== '') {
                $values[$question->id] = $value;
            }
        }

        foreach ($questions as $question) {
            if ($question->required && ! isset($values[$question->id])) {
                $errors['answers.'.$question->id] = [__('events.questions.required')];
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
     * @return string|list<string>|null
     */
    private function normalise(EventQuestion $question, mixed $raw): string|array|null
    {
        // Empty inputs reach the action as null; treat them as "not answered"
        // so the required check, not the shape check, reports them.
        if ($raw === null) {
            return $question->kind === QuestionKind::MultipleChoice ? [] : '';
        }

        if ($question->kind === QuestionKind::MultipleChoice) {
            if (is_string($raw)) {
                $raw = [$raw];
            }

            if (! is_array($raw)) {
                return null;
            }

            $selected = [];

            foreach ($raw as $option) {
                if (! is_string($option) || ! in_array($option, $question->optionList(), true)) {
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

        if ($question->kind === QuestionKind::SingleChoice
            && $value !== ''
            && ! in_array($value, $question->optionList(), true)) {
            return null;
        }

        return $value;
    }

    /**
     * @param  array<string, string|list<string>>  $values
     */
    private function storeAnswers(EventRegistration $registration, array $values): void
    {
        foreach ($values as $questionId => $value) {
            $registration->answers()->create([
                'event_question_id' => $questionId,
                'value' => $value,
            ]);
        }
    }
}
