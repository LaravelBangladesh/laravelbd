<?php

namespace App\Domain\Shared\Rules;

use App\Domain\Shared\VideoEmbed;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YouTubeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || VideoEmbed::id($value) === null) {
            $fail(__('events.media.youtube_invalid'));
        }
    }
}
