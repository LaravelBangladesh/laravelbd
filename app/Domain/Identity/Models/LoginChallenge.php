<?php

namespace App\Domain\Identity\Models;

use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $email
 * @property string|null $name
 * @property string $code_hash
 * @property string $token_hash
 * @property Carbon $expires_at
 * @property Carbon|null $consumed_at
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['email', 'name', 'code_hash', 'token_hash', 'expires_at', 'consumed_at', 'ip_address'])]
class LoginChallenge extends Model
{
    use HasUuidPrimaryKey;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }
}
