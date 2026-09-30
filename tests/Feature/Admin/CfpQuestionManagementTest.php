<?php

use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function cfpQuestion(array $overrides = []): array
{
    return [
        'id' => fake()->uuid(),
        'kind' => 'short_text',
        'label_en' => 'Company',
        'label_bn' => null,
        'help_en' => null,
        'help_bn' => null,
        'options' => null,
        'required' => false,
        ...$overrides,
    ];
}

test('members cannot manage cfp questions', function () {
    $event = Event::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('admin.events.cfp-questions.store', $event), [
            'kind' => 'short_text',
            'label_en' => 'Company',
        ])
        ->assertForbidden();

    expect($event->refresh()->cfp_questions)->toBeNull();
});

test('staff can add a choice cfp question with options from a textarea', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.cfp-questions.store', $event), [
            'kind' => 'single_choice',
            'label_en' => 'Level',
            'label_bn' => 'স্তর',
            'options' => "Beginner\n\nAdvanced\n",
            'required' => '1',
        ])
        ->assertRedirect();

    $question = $event->refresh()->cfp_questions[0];

    expect($question['kind'])->toBe('single_choice')
        ->and($question['label_bn'])->toBe('স্তর')
        ->and($question['options'])->toBe(['Beginner', 'Advanced'])
        ->and($question['required'])->toBeTrue();
});

test('cfp questions share the event question validation rules', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.cfp-questions.store', $event), [
            'kind' => 'single_choice',
            'label_en' => 'Level',
            'options' => "Only\nOnly",
        ])
        ->assertSessionHasErrors('options');

    $this->actingAs($moderator)
        ->post(route('admin.events.cfp-questions.store', $event), [
            'kind' => 'long_text',
            'options' => "A\nB",
        ])
        ->assertSessionHasErrors(['label_en']);

    expect($event->refresh()->cfp_questions)->toBeNull();
});

test('staff can update a cfp question', function () {
    $question = cfpQuestion();
    $event = Event::factory()->create(['cfp_questions' => [$question]]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.cfp-questions.update', [$event, $question['id']]), [
            'kind' => 'multiple_choice',
            'label_en' => 'Topics',
            'options' => ['Queues', 'Testing'],
        ])
        ->assertRedirect();

    $updated = $event->refresh()->cfp_questions[0];

    expect($updated['id'])->toBe($question['id'])
        ->and($updated['kind'])->toBe('multiple_choice')
        ->and($updated['options'])->toBe(['Queues', 'Testing']);
});

test('an unknown cfp question cannot be updated or deleted', function () {
    $event = Event::factory()->create(['cfp_questions' => [cfpQuestion()]]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.cfp-questions.update', [$event, fake()->uuid()]), [
            'kind' => 'short_text',
            'label_en' => 'Hijacked',
        ])
        ->assertNotFound();

    $this->actingAs($moderator)
        ->delete(route('admin.events.cfp-questions.destroy', [$event, fake()->uuid()]))
        ->assertNotFound();
});

test('staff can delete a cfp question', function () {
    $question = cfpQuestion();
    $kept = cfpQuestion(['label_en' => 'Kept']);
    $event = Event::factory()->create(['cfp_questions' => [$question, $kept]]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->delete(route('admin.events.cfp-questions.destroy', [$event, $question['id']]))
        ->assertRedirect();

    expect($event->refresh()->cfp_questions)->toBe([$kept]);
});

test('staff can reorder cfp questions', function () {
    $first = cfpQuestion();
    $second = cfpQuestion(['label_en' => 'Second']);
    $event = Event::factory()->create(['cfp_questions' => [$first, $second]]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.cfp-questions.reorder', $event), [
            'question_ids' => [$second['id'], $first['id']],
        ])
        ->assertRedirect();

    expect($event->refresh()->cfp_questions)->toBe([$second, $first]);
});

test('a partial or foreign cfp reorder is rejected', function () {
    $first = cfpQuestion();
    $event = Event::factory()->create(['cfp_questions' => [$first, cfpQuestion()]]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.cfp-questions.reorder', $event), [
            'question_ids' => [$first['id']],
        ])
        ->assertSessionHasErrors('question_ids');

    $this->actingAs($moderator)
        ->patch(route('admin.events.cfp-questions.reorder', $event), [
            'question_ids' => [$first['id'], $first['id']],
        ])
        ->assertSessionHasErrors('question_ids.0');
});

test('members cannot reorder cfp questions', function () {
    $question = cfpQuestion();
    $event = Event::factory()->create(['cfp_questions' => [$question]]);
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('admin.events.cfp-questions.reorder', $event), [
            'question_ids' => [$question['id']],
        ])
        ->assertForbidden();
});

test('the manage page exposes cfp questions with kind labels', function () {
    $question = cfpQuestion(['kind' => 'single_choice', 'options' => ['A', 'B'], 'required' => true]);
    $event = Event::factory()->create(['cfp_questions' => [$question]]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('event.cfp_questions', 1)
            ->where('event.cfp_questions.0.id', $question['id'])
            ->where('event.cfp_questions.0.kind_label', __('events.questions.kinds.single_choice'))
            ->where('event.cfp_questions.0.options', ['A', 'B'])
            ->where('event.cfp_questions.0.required', true));
});
