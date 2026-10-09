<?php

namespace App\Domain\Events\Mail;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Shared\DhakaTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Shared shape of the emails sent to an event's registered attendees,
 * written in the attendee's own locale.
 */
abstract class EventRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EventRegistration $registration)
    {
        $this->locale($registration->user->locale);
    }

    /** Translation key prefix, e.g. "events.mail.reminder". */
    abstract protected function key(): string;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __($this->key().'.subject', ['event' => $this->event()->localized('title')]),
        );
    }

    public function content(): Content
    {
        $event = $this->event();
        $endsSameDay = DhakaTime::format($event->starts_at, 'Y-m-d') === DhakaTime::format($event->ends_at, 'Y-m-d');

        return new Content(
            markdown: 'mail.events.registration',
            with: [
                'key' => $this->key(),
                'name' => $this->registration->user->name,
                'email' => $this->registration->user->email,
                'title' => $event->localized('title'),
                'when' => DhakaTime::display($event->starts_at, 'l, d M Y, H:i')
                    .' - '.DhakaTime::display($event->ends_at, $endsSameDay ? 'H:i' : 'l, d M Y, H:i'),
                'venueName' => $event->venue_name,
                'venueAddress' => $event->venue_address,
                'venueMapUrl' => $event->venue_map_url,
                'onlineUrl' => $event->online_url,
                'eventUrl' => route('events.show', $event->slug),
            ],
        );
    }

    private function event(): Event
    {
        return $this->registration->event;
    }
}
