<?php

use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage questions', function () {
    $event = Event::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('admin.events.questions.store', $event), [
            'kind' => 'short_text',
            'label_en' => 'Company',
        ])
        ->assertForbidden();

    expect(EventQuestion::query()->count())->toBe(0);
});

test('staff can add a text question', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.questions.store', $event), [
            'kind' => 'short_text',
            'label_en' => 'Company / role',
            'label_bn' => 'কোম্পানি',
            'help_en' => 'For your badge.',
            'required' => '1',
        ])
        ->assertRedirect();

    $question = EventQuestion::query()->first();

    expect($question?->kind)->toBe(QuestionKind::ShortText)
        ->and($question?->label_en)->toBe('Company / role')
        ->and($question?->label_bn)->toBe('কোম্পানি')
        ->and($question?->required)->toBeTrue()
        ->and($question?->options)->toBeNull()
        ->and($question?->position)->toBe(1);
});

test('staff can add a choice question with options from a textarea', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.questions.store', $event), [
            'kind' => 'single_choice',
            'label_en' => 'T-shirt size',
            'options' => "S\nM\n\nL\n",
        ])
        ->assertRedirect();

    expect(EventQuestion::query()->first()?->optionList())->toBe(['S', 'M', 'L']);
});

test('a choice question needs at least two distinct options', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.questions.store', $event), [
            'kind' => 'single_choice',
            'label_en' => 'T-shirt size',
            'options' => "M\nM",
        ])
        ->assertSessionHasErrors('options');

    expect(EventQuestion::query()->count())->toBe(0);
});

test('a text question may not carry options', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.questions.store', $event), [
            'kind' => 'long_text',
            'label_en' => 'Anything else?',
            'options' => "A\nB",
        ])
        ->assertSessionHasErrors('options');
});

test('a missing label is rejected', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.questions.store', $event), ['kind' => 'short_text'])
        ->assertSessionHasErrors('label_en');
});

test('staff can update a question', function () {
    $event = Event::factory()->create();
    $question = EventQuestion::factory()->create([
        'event_id' => $event->id,
        'position' => 3,
    ]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.questions.update', [$event, $question]), [
            'kind' => 'multiple_choice',
            'label_en' => 'Topics',
            'options' => ['Queues', 'Testing'],
            'required' => '1',
        ])
        ->assertRedirect();

    $question->refresh();

    expect($question->kind)->toBe(QuestionKind::MultipleChoice)
        ->and($question->optionList())->toBe(['Queues', 'Testing'])
        ->and($question->required)->toBeTrue()
        ->and($question->position)->toBe(3);
});

test('a question from another event cannot be updated or deleted', function () {
    $event = Event::factory()->create();
    $other = EventQuestion::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.questions.update', [$event, $other]), [
            'kind' => 'short_text',
            'label_en' => 'Hijacked',
        ])
        ->assertNotFound();

    $this->actingAs($moderator)
        ->delete(route('admin.events.questions.destroy', [$event, $other]))
        ->assertNotFound();
});

test('staff can delete a question', function () {
    $event = Event::factory()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->delete(route('admin.events.questions.destroy', [$event, $question]))
        ->assertRedirect();

    expect(EventQuestion::query()->count())->toBe(0);
});

test('staff can reorder questions', function () {
    $event = Event::factory()->create();
    $first = EventQuestion::factory()->create(['event_id' => $event->id, 'position' => 0]);
    $second = EventQuestion::factory()->create(['event_id' => $event->id, 'position' => 1]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.questions.reorder', $event), [
            'question_ids' => [$second->id, $first->id],
        ])
        ->assertRedirect();

    expect($second->refresh()->position)->toBe(0)
        ->and($first->refresh()->position)->toBe(1);
});

test('a partial reorder is rejected', function () {
    $event = Event::factory()->create();
    $first = EventQuestion::factory()->create(['event_id' => $event->id]);
    EventQuestion::factory()->create(['event_id' => $event->id]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.questions.reorder', $event), [
            'question_ids' => [$first->id],
        ])
        ->assertSessionHasErrors('question_ids');
});

test('a reorder for a foreign question is rejected', function () {
    $event = Event::factory()->create();
    EventQuestion::factory()->create(['event_id' => $event->id]);
    $foreign = EventQuestion::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.questions.reorder', $event), [
            'question_ids' => [$foreign->id],
        ])
        ->assertSessionHasErrors('question_ids.0');
});

test('members cannot reorder questions', function () {
    $event = Event::factory()->create();
    $question = EventQuestion::factory()->create(['event_id' => $event->id]);
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('admin.events.questions.reorder', $event), [
            'question_ids' => [$question->id],
        ])
        ->assertForbidden();
});

test('the manage page exposes questions and question kinds', function () {
    $event = Event::factory()->create();
    $question = EventQuestion::factory()->singleChoice()->required()->create([
        'event_id' => $event->id,
    ]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('event.questions', 1)
            ->where('event.questions.0.id', $question->id)
            ->where('event.questions.0.required', true)
            ->where('event.questions.0.options', ['S', 'M', 'L'])
            ->has('questionKinds', 4));
});
