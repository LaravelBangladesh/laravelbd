<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\AttendeeMail;
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
    public function __construct(private readonly QueueAttendeeMail $queueMail) {}

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

            $questions = $event->questions()->get()
                ->map(fn (EventQuestion $question) => $question->snapshot())
                ->values()
                ->all();

            $values = QuestionAnswers::validate($questions, $answers);

            $existing = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null && $existing->status !== RegistrationStatus::Cancelled) {
                return $existing;
            }

            $status = $event->seatStatus();

            if ($existing !== null) {
                $existing->forceFill([
                    'status' => $status,
                    'registered_at' => now(),
                ])->save();

                $existing->answers()->delete();
                $this->storeAnswers($existing, $questions, $values);
                $this->confirm($existing);

                return $existing;
            }

            $registration = EventRegistration::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $status,
                'registered_at' => now(),
            ]);

            $this->storeAnswers($registration, $questions, $values);
            $this->confirm($registration);

            return $registration;
        });
    }

    /**
     * Only a confirmed seat gets the confirmation email. The job waits for the
     * transaction to commit.
     */
    private function confirm(EventRegistration $registration): void
    {
        if ($registration->status === RegistrationStatus::Registered) {
            ($this->queueMail)($registration, AttendeeMail::Confirmation);
        }
    }

    /**
     * Snapshot each answered question so later edits to the event's questions
     * never change what the attendee was asked.
     *
     * @param  array<int, array{id: string, kind: string, label_en: string, label_bn: string|null, help_en: string|null, help_bn: string|null, options: list<string>|null, required: bool}>  $questions
     * @param  array<string, string|list<string>>  $values
     */
    private function storeAnswers(EventRegistration $registration, array $questions, array $values): void
    {
        foreach ($questions as $question) {
            if (isset($values[$question['id']])) {
                $registration->answers()->create([
                    'question' => $question,
                    'value' => $values[$question['id']],
                ]);
            }
        }
    }
}
