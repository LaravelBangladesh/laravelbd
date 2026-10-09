<?php

use App\Domain\Events\Mail\EventReminderMail;
use App\Domain\Events\Mail\RegistrationConfirmationMail;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('attendee mails', [
    'confirmation' => [RegistrationConfirmationMail::class, 'events.mail.confirmation'],
    'reminder' => [EventReminderMail::class, 'events.mail.reminder'],
]);

/**
 * @param  class-string<RegistrationConfirmationMail|EventReminderMail>  $mailable
 */
function attendeeMail(string $mailable, array $event = [], array $user = []): RegistrationConfirmationMail|EventReminderMail
{
    $registration = EventRegistration::factory()
        ->for(Event::factory()->published()->create([
            'title_en' => 'Laravel Dhaka Meetup',
            'title_bn' => 'লারাভেল ঢাকা মিটআপ',
            'slug' => 'laravel-dhaka-meetup',
            'starts_at' => '2026-11-14 12:00:00',
            'ends_at' => '2026-11-14 15:00:00',
            'venue_name' => 'BASIS Auditorium',
            'venue_address' => 'Kawran Bazar, Dhaka',
            'venue_map_url' => 'https://maps.example.com/basis',
            'online_url' => null,
            ...$event,
        ]))
        ->for(User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'locale' => 'en', ...$user]))
        ->create();

    return new $mailable($registration);
}

test('attendee mails carry the event details and a link to the event', function (string $mailable, string $key) {
    $mail = attendeeMail($mailable);

    $mail->assertHasSubject(__("{$key}.subject", ['event' => 'Laravel Dhaka Meetup']));
    $mail->assertSeeInHtml(__("{$key}.heading"), false);
    $mail->assertSeeInHtml('Jane Doe', false);
    $mail->assertSeeInHtml('Saturday, 14 Nov 2026, 18:00 - 21:00', false);
    $mail->assertSeeInHtml('BASIS Auditorium, Kawran Bazar, Dhaka', false);
    $mail->assertSeeInHtml('https://maps.example.com/basis', false);
    $mail->assertSeeInHtml(route('events.show', 'laravel-dhaka-meetup'), false);
    $mail->assertSeeInHtml('class="brand-bar"', false);
    $mail->assertSeeInHtml(
        __('events.mail.notice', ['email' => 'jane@example.com', 'event' => 'Laravel Dhaka Meetup', 'app' => config('app.name')]),
        false,
    );
    $mail->assertDontSeeInHtml(__('mail.footer.notice', ['email' => 'jane@example.com', 'app' => config('app.name')]), false);
})->with('attendee mails');

test('an online event shows the join link instead of a venue', function () {
    $mail = attendeeMail(EventReminderMail::class, ['venue_name' => null, 'venue_address' => null, 'venue_map_url' => null, 'online_url' => 'https://meet.example.com/abc']);

    $mail->assertSeeInHtml('https://meet.example.com/abc', false);
    $mail->assertDontSeeInHtml(__('events.venue'), false);
});

test('the time range shows the full end date when the event spans days', function () {
    attendeeMail(EventReminderMail::class, ['ends_at' => '2026-11-15 12:00:00'])
        ->assertSeeInText('Saturday, 14 Nov 2026, 18:00 - Sunday, 15 Nov 2026, 18:00');
});

test('attendee mails are written in the attendee locale', function (string $mailable, string $key) {
    $mail = attendeeMail($mailable, user: ['locale' => 'bn']);

    expect($mail->locale)->toBe('bn');
    $mail->assertSeeInHtml('লারাভেল ঢাকা মিটআপ', false);
})->with('attendee mails');
