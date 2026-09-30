<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the old global cfp page is gone', function () {
    $this->get('/cfp')->assertNotFound();
});

test('members see the proposal form for an event accepting proposals', function () {
    $event = Event::factory()->acceptingProposals()->create(['title_en' => 'April meetup']);
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->get(route('events.cfp.create', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/cfp')
            ->where('event.slug', $event->slug)
            ->where('event.title', 'April meetup')
            ->has('kinds', 3));
});

test('guests are sent to log in before proposing', function () {
    $event = Event::factory()->acceptingProposals()->create();

    $this->get(route('events.cfp.create', $event))->assertRedirect(route('login'));
});

test('members cannot open the form for an event that is not accepting proposals', function () {
    $event = Event::factory()->cfpClosed()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->get(route('events.cfp.create', $event))
        ->assertForbidden();
});

test('members can submit a proposal while the window is open', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'title_bn' => '',
            'abstract_en' => 'How we test Laravel apps.',
            'abstract_bn' => '',
            'kind' => 'talk',
        ])
        ->assertRedirect(route('account.edit'));

    $proposal = TalkProposal::query()->where('title_en', 'Testing HTTP')->first();

    expect($proposal)->not->toBeNull()
        ->and($proposal?->user_id)->toBe($member->id)
        ->and($proposal?->event_id)->toBe($event->id)
        ->and($proposal?->title_bn)->toBeNull()
        ->and($proposal?->abstract_bn)->toBeNull()
        ->and($proposal?->status->value)->toBe('submitted');
});

test('members cannot submit once the window has closed', function () {
    $event = Event::factory()->cfpClosed()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Too late',
            'abstract_en' => 'The window closed.',
            'kind' => 'talk',
        ])
        ->assertForbidden();

    expect(TalkProposal::query()->count())->toBe(0);
});

test('guests cannot submit a proposal', function () {
    $event = Event::factory()->acceptingProposals()->create();

    $this->post(route('events.cfp.store', $event), [
        'title_en' => 'Testing HTTP',
        'abstract_en' => 'How we test Laravel apps.',
        'kind' => 'talk',
    ])->assertRedirect(route('login'));
});

test('a proposal needs a title an abstract and a kind', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [])
        ->assertSessionHasErrors(['title_en', 'abstract_en', 'kind']);

    expect(TalkProposal::query()->count())->toBe(0);
});

function proposalQuestions(): array
{
    return [
        [
            'id' => 'b1f0a3c2-0000-4000-8000-000000000001',
            'kind' => 'short_text',
            'label_en' => 'Company',
            'label_bn' => 'কোম্পানি',
            'help_en' => 'Where you work.',
            'help_bn' => null,
            'options' => null,
            'required' => true,
        ],
        [
            'id' => 'b1f0a3c2-0000-4000-8000-000000000002',
            'kind' => 'multiple_choice',
            'label_en' => 'Topics',
            'label_bn' => null,
            'help_en' => null,
            'help_bn' => null,
            'options' => ['APIs', 'Queues'],
            'required' => false,
        ],
        [
            'id' => 'b1f0a3c2-0000-4000-8000-000000000003',
            'kind' => 'long_text',
            'label_en' => 'Anything else?',
            'label_bn' => null,
            'help_en' => null,
            'help_bn' => null,
            'options' => null,
            'required' => false,
        ],
    ];
}

test('the proposal form lists the event cfp questions in the viewer locale', function () {
    $event = Event::factory()->acceptingProposals()->create(['cfp_questions' => proposalQuestions()]);
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->withSession(['locale' => 'bn'])
        ->get(route('events.cfp.create', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('questions', 3)
            ->where('questions.0.id', proposalQuestions()[0]['id'])
            ->where('questions.0.label', 'কোম্পানি')
            ->where('questions.0.help', 'Where you work.')
            ->where('questions.0.options', [])
            ->where('questions.0.required', true)
            ->where('questions.1.label', 'Topics')
            ->where('questions.1.options', ['APIs', 'Queues']));
});

test('an event without cfp questions sends an empty list', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->get(route('events.cfp.create', $event))
        ->assertInertia(fn (Assert $page) => $page->has('questions', 0));
});

test('answers are stored as snapshots of the questions asked', function () {
    [$company, $topics] = proposalQuestions();
    $event = Event::factory()->acceptingProposals()->create(['cfp_questions' => proposalQuestions()]);
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'abstract_en' => 'How we test Laravel apps.',
            'kind' => 'talk',
            'answers' => [
                $company['id'] => ' Cefalo ',
                $topics['id'] => ['Queues'],
                proposalQuestions()[2]['id'] => '',
            ],
        ])
        ->assertRedirect(route('account.edit'));

    $expected = [
        [...$company, 'value' => 'Cefalo'],
        [...$topics, 'value' => ['Queues']],
    ];

    expect(TalkProposal::query()->firstOrFail()->answers)->toBe($expected);

    // Editing or removing the event's questions leaves recorded answers alone.
    $event->forceFill(['cfp_questions' => [[...$company, 'label_en' => 'Employer']]])->save();

    expect(TalkProposal::query()->firstOrFail()->answers)->toBe($expected);
});

test('a missing required answer is reported against its question', function () {
    [$company] = proposalQuestions();
    $event = Event::factory()->acceptingProposals()->create(['cfp_questions' => proposalQuestions()]);
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'abstract_en' => 'How we test Laravel apps.',
            'kind' => 'talk',
            'answers' => ['not-a-question' => 'x'],
        ])
        ->assertSessionHasErrors(['answers.'.$company['id'], 'answers.not-a-question']);

    expect(TalkProposal::query()->count())->toBe(0);
});

test('answers must be strings or lists of strings', function () {
    [$company] = proposalQuestions();
    $event = Event::factory()->acceptingProposals()->create(['cfp_questions' => proposalQuestions()]);
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'abstract_en' => 'How we test Laravel apps.',
            'kind' => 'talk',
            'answers' => [$company['id'] => [['nested']]],
        ])
        ->assertSessionHasErrors('answers.'.$company['id'].'.0');
});

test('a proposal without questions stores an empty answer list', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'abstract_en' => 'How we test Laravel apps.',
            'kind' => 'talk',
        ])
        ->assertRedirect(route('account.edit'));

    expect(TalkProposal::query()->firstOrFail()->answers)->toBe([]);
});
