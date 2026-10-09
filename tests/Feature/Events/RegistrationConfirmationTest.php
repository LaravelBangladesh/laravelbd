<?php

use App\Domain\Events\Actions\ReinstateRegistration;
use App\Domain\Events\Enums\MailDeliveryStatus;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Mail\RegistrationConfirmationMail;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;

test('registering sends the confirmation and marks it sent', function () {
    Mail::fake();
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event))->assertRedirect();

    $registration = EventRegistration::query()->where('user_id', $user->id)->sole();
    Mail::assertSent(RegistrationConfirmationMail::class, fn (RegistrationConfirmationMail $mail) => $mail->hasTo($user->email) && $mail->registration->is($registration));
    expect($registration)
        ->confirmation_status->toBe(MailDeliveryStatus::Sent)
        ->confirmation_sent_at->not->toBeNull()
        ->reminder_status->toBe(MailDeliveryStatus::NotSent);
});

test('registering again after cancelling sends a fresh confirmation', function () {
    Mail::fake();
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();
    EventRegistration::factory()->for($event)->for($user)->cancelled()->create()
        ->forceFill(['confirmation_status' => MailDeliveryStatus::Sent, 'confirmation_sent_at' => now()->subWeek()])->save();

    $this->actingAs($user)->post(route('events.rsvp.store', $event))->assertRedirect();

    Mail::assertSent(RegistrationConfirmationMail::class, 1);
    expect(EventRegistration::query()->where('user_id', $user->id)->sole()->confirmation_sent_at->isToday())->toBeTrue();
});

test('an attendee already registered is not emailed twice', function () {
    Mail::fake();
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();
    EventRegistration::factory()->for($event)->for($user)->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event))->assertRedirect();

    Mail::assertNothingSent();
});

test('a waitlisted registration gets no confirmation', function () {
    Mail::fake();
    $event = Event::factory()->published()->create(['capacity' => 1]);
    EventRegistration::factory()->for($event)->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event))->assertRedirect();

    Mail::assertNothingSent();
    expect(EventRegistration::query()->where('user_id', $user->id)->sole())
        ->status->toBe(RegistrationStatus::Waitlisted)
        ->confirmation_status->toBe(MailDeliveryStatus::NotSent);
});

test('staff registering someone again sends no confirmation', function () {
    Mail::fake();
    $registration = EventRegistration::factory()->for(Event::factory()->published())->cancelled()->create();

    app(ReinstateRegistration::class)($registration);

    Mail::assertNothingSent();
});
