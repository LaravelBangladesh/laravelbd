<?php

namespace App\Domain\Events\Models;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use Database\Factories\EventRegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $event_id
 * @property string $user_id
 * @property RegistrationStatus $status
 * @property Carbon|null $registered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['event_id', 'user_id', 'status', 'registered_at'])]
class EventRegistration extends Model
{
    /** @use HasFactory<EventRegistrationFactory> */
    use HasFactory, HasUuidPrimaryKey;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'registered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<EventRegistrationAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(EventRegistrationAnswer::class, 'event_registration_id');
    }

    public function isActive(): bool
    {
        return $this->status !== RegistrationStatus::Cancelled;
    }
}
