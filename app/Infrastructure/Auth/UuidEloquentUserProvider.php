<?php

namespace App\Infrastructure\Auth;

use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class UuidEloquentUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        if (! HasUuidPrimaryKey::isValidUuidKey($identifier)) {
            return null;
        }

        return parent::retrieveById($identifier);
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        if (! HasUuidPrimaryKey::isValidUuidKey($identifier)) {
            return null;
        }

        return parent::retrieveByToken($identifier, $token);
    }
}
