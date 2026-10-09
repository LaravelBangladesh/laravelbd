<?php

use App\Domain\Events\Enums\AttendeeMail;
use App\Domain\Events\Enums\MailDeliveryStatus;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Jobs\SendAttendeeMail;
use App\Domain\Events\Mail\EventReminderMail;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

function reminderEvent(array $attributes = []): Event
{
    return Event::factory()->published()->create(['title_en' => 'October Meetup', ...$attributes]);
}

test('staff preview the reminder addressed to themselves in the language they pick', function () {
    $moderator = User::factory()->moderator()->create(['name' => 'Mod Erator', 'locale' => 'en']);
    $event = reminderEvent(['title_bn' => 'অক্টোবর মিটআপ']);

    $this->actingAs($moderator)
        ->get(route('admin.events.reminders.preview', $event))
        ->assertOk()
        ->assertSee('Mod Erator')
        ->assertSee(__('events.mail.reminder.heading'));

    $this->actingAs($moderator)
        ->get(route('admin.events.reminders.preview', [$event, 'locale' => 'bn']))
        ->assertOk()
        ->assertSee('অক্টোবর মিটআপ');

    expect($moderator->fresh()->locale)->toBe('en');
});

test('the preview rejects an unknown language', function () {
    $this->actingAs(User::factory()->moderator()->create())
        ->getJson(route('admin.events.reminders.preview', [reminderEvent(), 'locale' => 'fr']))
        ->assertUnprocessable();
});

test('members cannot preview or send reminders', function () {
    $member = User::factory()->create();
    $event = reminderEvent();
    $registration = EventRegistration::factory()->for($event)->create();

    $this->actingAs($member)->get(route('admin.events.reminders.preview', $event))->assertForbidden();
    $this->actingAs($member)->post(route('admin.events.reminders.store', $event))->assertForbidden();
    $this->actingAs($member)->post(route('admin.events.registrations.reminder', [$event, $registration]))->assertForbidden();
});

test('sending reminders queues them for registered attendees not yet reminded', function () {
    Queue::fake();
    $event = reminderEvent();
    $pending = EventRegistration::factory()->for($event)->create();
    $failed = EventRegistration::factory()->for($event)->create();
    $failed->forceFill(['reminder_status' => MailDeliveryStatus::Failed])->save();
    $sent = EventRegistration::factory()->for($event)->create();
    $sent->forceFill(['reminder_status' => MailDeliveryStatus::Sent, 'reminder_sent_at' => now()])->save();
    EventRegistration::factory()->for($event)->cancelled()->create();
    EventRegistration::factory()->for($event)->waitlisted()->create();
    EventRegistration::factory()->create();

    $this->actingAs(User::factory()->moderator()->create())
        ->from(route('admin.events.attendees.index', $event))
        ->post(route('admin.events.reminders.store', $event))
        ->assertRedirect(route('admin.events.attendees.index', $event))
        ->assertInertiaFlash('toast.message', __('admin.reminders_queued', ['count' => '2']));

    Queue::assertPushed(SendAttendeeMail::class, 2);
    expect($pending->fresh()->reminder_status)->toBe(MailDeliveryStatus::Queued)
        ->and($failed->fresh()->reminder_status)->toBe(MailDeliveryStatus::Queued)
        ->and($sent->fresh()->reminder_status)->toBe(MailDeliveryStatus::Sent);
});

test('reminders cannot be sent once the event has started', function () {
    Queue::fake();
    $event = reminderEvent(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
    $registration = EventRegistration::factory()->for($event)->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.reminders.store', $event))
        ->assertSessionHasErrors('event');

    $this->actingAs($moderator)
        ->post(route('admin.events.registrations.reminder', [$event, $registration]))
        ->assertSessionHasErrors('registration');

    Queue::assertNothingPushed();
});

test('staff resend the reminder to one registered attendee', function () {
    Mail::fake();
    $event = reminderEvent();
    $registration = EventRegistration::factory()->for($event)
        ->for(User::factory()->create(['name' => 'Grace Hopper']))
        ->create();
    $registration->forceFill(['reminder_status' => MailDeliveryStatus::Sent, 'reminder_sent_at' => now()->subDay()])->save();

    $this->actingAs(User::factory()->moderator()->create())
        ->post(route('admin.events.registrations.reminder', [$event, $registration]))
        ->assertRedirect()
        ->assertInertiaFlash('toast.message', __('admin.reminder_queued', ['name' => 'Grace Hopper']));

    Mail::assertSent(EventReminderMail::class, fn (EventReminderMail $mail) => $mail->registration->is($registration));
    expect($registration->fresh())
        ->reminder_status->toBe(MailDeliveryStatus::Sent)
        ->reminder_sent_at->not->toBeNull();
});

test('a cancelled attendee cannot be sent the reminder', function () {
    $event = reminderEvent();
    $registration = EventRegistration::factory()->for($event)->cancelled()->create();

    $this->actingAs(User::factory()->moderator()->create())
        ->post(route('admin.events.registrations.reminder', [$event, $registration]))
        ->assertSessionHasErrors('registration');
});

test('a registration from another event is not found', function () {
    $this->actingAs(User::factory()->moderator()->create())
        ->post(route('admin.events.registrations.reminder', [reminderEvent(), EventRegistration::factory()->create()]))
        ->assertNotFound();
});

test('the attendee list shows both email statuses and filters by reminder', function () {
    $event = reminderEvent();
    $reminded = EventRegistration::factory()->for($event)->for(User::factory()->create(['name' => 'Grace Hopper']))->create();
    $reminded->forceFill([
        'confirmation_status' => MailDeliveryStatus::Sent,
        'confirmation_sent_at' => '2026-10-01 12:00:00',
        'reminder_status' => MailDeliveryStatus::Sent,
        'reminder_sent_at' => '2026-10-02 12:00:00',
    ])->save();
    EventRegistration::factory()->for($event)->for(User::factory()->create(['name' => 'Alan Turing']))->create();

    $this->actingAs(User::factory()->moderator()->create())
        ->get(route('admin.events.attendees.index', [$event, 'reminder' => 'sent']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('attendees.data', 1)
            ->where('attendees.data.0.name', 'Grace Hopper')
            ->where('attendees.data.0.confirmation', ['status' => 'sent', 'label' => __('events.mail_status.sent'), 'sent_at' => '01 Oct 2026, 18:00'])
            ->where('attendees.data.0.reminder.sent_at', '02 Oct 2026, 18:00')
            ->where('filters.reminder', 'sent')
            ->where('reminder', ['available' => true, 'recipients' => 1])
            ->has('mailStatuses', 4));
});

test('the reminder status falls back when the attendee is no longer registered at send time', function () {
    Mail::fake();
    $registration = EventRegistration::factory()->for(reminderEvent())->create();
    $job = new SendAttendeeMail($registration, AttendeeMail::Reminder);
    $registration->update(['status' => RegistrationStatus::Cancelled]);

    $job->handle();

    Mail::assertNothingSent();
    expect($registration->fresh()->reminder_status)->toBe(MailDeliveryStatus::NotSent);
});

test('a reminder that keeps failing is marked failed', function () {
    $registration = EventRegistration::factory()->for(reminderEvent())->create();

    (new SendAttendeeMail($registration, AttendeeMail::Reminder))->failed(new RuntimeException('smtp down'));

    expect($registration->fresh()->reminder_status)->toBe(MailDeliveryStatus::Failed);
});
