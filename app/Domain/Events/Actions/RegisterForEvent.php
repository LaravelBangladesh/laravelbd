<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\QuestionAnswers;
use App\Domain\Identity\Models\User;
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

            $values = QuestionAnswers::validate(
                $event->questions()->get()
                    ->map(fn (EventQuestion $question) => [
                        'id' => $question->id,
                        'kind' => $question->kind->value,
                        'options' => $question->options,
                        'required' => $question->required,
                    ])
                    ->values()
                    ->all(),
                $answers,
            );

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
