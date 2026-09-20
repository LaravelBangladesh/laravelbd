<?php

namespace App\Domain\Shared\Concerns;

trait LocalizesContent
{
    public function localized(string $attribute): string
    {
        $locale = app()->getLocale();
        $value = $this->getAttribute("{$attribute}_{$locale}");

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return (string) ($this->getAttribute("{$attribute}_en") ?? '');
    }
}
