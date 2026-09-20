<?php

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function answeringMember(): User
{
    return User::factory()->withCompleteProfile()->create();
}

test('the show page exposes the questions', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->singleChoice()->create([
        'event_id' => $event->id,
        'label_en' => 'T-shirt size',
        'help_en' => 'Pick one.',
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('event.questions', 1)
            ->where('event.questions.0.id', $question->id)
            ->where('event.questions.0.label', 'T-shirt size')
            ->where('event.questions.0.help', 'Pick one.')
            ->where('event.questions.0.options', ['S', 'M', 'L']));
});

test('registration without questions is unchanged', function () {
    $event = Event::factory()->published()->create();
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(EventRegistration::query()->where('user_id', $user->id)->first()?->status)
        ->toBe(RegistrationStatus::Registered);
});

test('a missing required answer fails and stores no registration', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->required()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), ['answers' => [$question->id => '  ']])
        ->assertSessionHasErrors("answers.{$question->id}");

    expect(EventRegistration::query()->count())->toBe(0);
});

test('an omitted required answer fails', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->required()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event))
        ->assertSessionHasErrors("answers.{$question->id}");

    expect(EventRegistration::query()->count())->toBe(0);
});

test('answers are stored for every kind', function () {
    $event = Event::factory()->published()->create();
    $short = EventQuestion::factory()->required()->create(['event_id' => $event->id]);
    $long = EventQuestion::factory()->longText()->create(['event_id' => $event->id]);
    $single = EventQuestion::factory()->singleChoice()->create(['event_id' => $event->id]);
    $multi = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), [
            'answers' => [
                $short->id => 'Engineer at Cefalo',
                $long->id => 'Looking forward to it.',
                $single->id => 'M',
                $multi->id => ['Testing', 'Queues'],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $registration = EventRegistration::query()->where('user_id', $user->id)->firstOrFail();
    $answers = $registration->answers()->get()->keyBy('event_question_id');

    expect($answers)->toHaveCount(4)
        ->and($answers[$short->id]->value)->toBe('Engineer at Cefalo')
        ->and($answers[$long->id]->value)->toBe('Looking forward to it.')
        ->and($answers[$single->id]->value)->toBe('M')
        ->and($answers[$multi->id]->value)->toBe(['Testing', 'Queues']);
});

test('a blank optional answer is not stored', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), ['answers' => [$question->id => '']])
        ->assertSessionHasNoErrors();

    expect(EventRegistration::query()->where('user_id', $user->id)->firstOrFail()->answers()->count())
        ->toBe(0);
});

test('an unknown choice value is rejected', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->singleChoice()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), ['answers' => [$question->id => 'XXL']])
        ->assertSessionHasErrors("answers.{$question->id}");

    expect(EventRegistration::query()->count())->toBe(0);
});

test('an unknown multiple choice value is rejected', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), ['answers' => [$question->id => ['Nope']]])
        ->assertSessionHasErrors("answers.{$question->id}");
});

test('a single multiple choice value posted as a string is accepted', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), ['answers' => [$question->id => 'APIs']])
        ->assertSessionHasNoErrors();

    expect(EventRegistration::query()->where('user_id', $user->id)->firstOrFail()
        ->answers()->firstOrFail()->value)->toBe(['APIs']);
});

test('a question from another event is rejected', function () {
    $event = Event::factory()->published()->create();
    $foreign = EventQuestion::factory()->create();
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), ['answers' => [$foreign->id => 'Hello']])
        ->assertSessionHasErrors("answers.{$foreign->id}");

    expect(EventRegistration::query()->count())->toBe(0);
});

test('reviving a cancelled registration replaces its answers', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)->post(route('events.rsvp.store', $event), [
        'answers' => [$question->id => 'First answer'],
    ]);
    $this->actingAs($user)->delete(route('events.rsvp.destroy', $event));
    $this->actingAs($user)->post(route('events.rsvp.store', $event), [
        'answers' => [$question->id => 'Second answer'],
    ]);

    $registration = EventRegistration::query()->where('user_id', $user->id)->firstOrFail();

    expect($registration->status)->toBe(RegistrationStatus::Registered)
        ->and($registration->answers()->count())->toBe(1)
        ->and($registration->answers()->firstOrFail()->value)->toBe('Second answer');
});

test('an existing active registration keeps its answers', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)->post(route('events.rsvp.store', $event), [
        'answers' => [$question->id => 'Kept'],
    ]);
    $this->actingAs($user)->post(route('events.rsvp.store', $event), [
        'answers' => [$question->id => 'Ignored'],
    ]);

    $registration = EventRegistration::query()->where('user_id', $user->id)->firstOrFail();

    expect($registration->answers()->count())->toBe(1)
        ->and($registration->answers()->firstOrFail()->value)->toBe('Kept');
});

test('an over long answer is rejected by the request', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $user = answeringMember();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event), [
            'answers' => [$question->id => str_repeat('a', 2001)],
        ])
        ->assertSessionHasErrors("answers.{$question->id}");
});

test('the admin attendee list carries the answers', function () {
    $event = Event::factory()->published()->create();
    $question = EventQuestion::factory()->create([
        'event_id' => $event->id,
        'label_en' => 'Company',
    ]);
    $user = answeringMember();
    $this->actingAs($user)->post(route('events.rsvp.store', $event), [
        'answers' => [$question->id => 'Cefalo'],
    ]);

    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('event.attendees', 1)
            ->has('event.attendees.0.answers', 1)
            ->where('event.attendees.0.answers.0.question_id', $question->id)
            ->where('event.attendees.0.answers.0.label', 'Company')
            ->where('event.attendees.0.answers.0.value', 'Cefalo'));
});
