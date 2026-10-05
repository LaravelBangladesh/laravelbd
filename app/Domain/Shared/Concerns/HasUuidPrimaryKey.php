<?php

namespace App\Domain\Shared\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

trait HasUuidPrimaryKey
{
    use HasUuids;

    public static function isValidUuidKey(mixed $value): bool
    {
        return is_string($value) && Str::isUuid($value);
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        if (! $this->isBindableKey($value, $field)) {
            return null;
        }

        /** @var static|null */
        return parent::resolveRouteBinding($value, $field);
    }

    public function resolveSoftDeletableRouteBinding($value, $field = null): ?static
    {
        if (! $this->isBindableKey($value, $field)) {
            return null;
        }

        /** @var static|null */
        return parent::resolveSoftDeletableRouteBinding($value, $field);
    }

    private function isBindableKey(mixed $value, ?string $field): bool
    {
        $column = $field ?? $this->getRouteKeyName();

        return $column !== $this->getKeyName() || static::isValidUuidKey(is_scalar($value) ? (string) $value : null);
    }
}
