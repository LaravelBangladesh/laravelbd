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
        $column = $field ?? $this->getRouteKeyName();

        if ($column === $this->getKeyName() && ! static::isValidUuidKey(is_scalar($value) ? (string) $value : null)) {
            return null;
        }

        /** @var static|null */
        return parent::resolveRouteBinding($value, $field);
    }
}
