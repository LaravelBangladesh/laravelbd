<?php

namespace App\Domain\Events\Enums;

enum QuestionKind: string
{
    case ShortText = 'short_text';
    case LongText = 'long_text';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';

    public function label(): string
    {
        return match ($this) {
            self::ShortText => __('events.questions.kinds.short_text'),
            self::LongText => __('events.questions.kinds.long_text'),
            self::SingleChoice => __('events.questions.kinds.single_choice'),
            self::MultipleChoice => __('events.questions.kinds.multiple_choice'),
        };
    }

    public function isChoice(): bool
    {
        return $this === self::SingleChoice || $this === self::MultipleChoice;
    }
}
