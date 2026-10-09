<?php

namespace App\Domain\Events\Enums;

enum MailDeliveryStatus: string
{
    case NotSent = 'not_sent';
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NotSent => __('events.mail_status.not_sent'),
            self::Queued => __('events.mail_status.queued'),
            self::Sent => __('events.mail_status.sent'),
            self::Failed => __('events.mail_status.failed'),
        };
    }
}
