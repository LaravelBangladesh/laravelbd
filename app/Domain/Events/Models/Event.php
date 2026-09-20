<?php

namespace App\Domain\Events\Models;

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\QueryBuilders\EventQueryBuilder;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $slug
 * @property EventType $type
 * @property EventStatus $status
 * @property string $title_en
 * @property string|null $title_bn
 * @property string|null $excerpt_en
 * @property string|null $excerpt_bn
 * @property string|null $description_en
 * @property string|null $description_bn
 * @property string|null $venue_name
 * @property string|null $venue_address
 * @property string|null $venue_map_url
 * @property string|null $online_url
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property int|null $capacity
 * @property bool $registration_enabled
 * @property bool $cfp_enabled
 * @property Carbon|null $cfp_opens_at
 * @property Carbon|null $cfp_closes_at
 * @property string|null $cover_path
 * @property Carbon|null $published_at
 * @property string|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static EventQueryBuilder query()
 */
#[Fillable([
    'slug',
    'type',
    'status',
    'title_en',
    'title_bn',
    'excerpt_en',
    'excerpt_bn',
    'description_en',
    'description_bn',
    'venue_name',
    'venue_address',
    'venue_map_url',
    'online_url',
    'starts_at',
    'ends_at',
    'capacity',
    'registration_enabled',
    'cfp_enabled',
    'cfp_opens_at',
    'cfp_closes_at',
    'cover_path',
    'published_at',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'status' => EventStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'capacity' => 'integer',
            'registration_enabled' => 'boolean',
            'cfp_enabled' => 'boolean',
            'cfp_opens_at' => 'datetime',
            'cfp_closes_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<EventSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class)->orderBy('sort_order')->orderBy('starts_at');
    }

    /**
     * @return BelongsToMany<Speaker, $this, SpeakerAssignment>
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class)
            ->using(SpeakerAssignment::class)
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * @return HasMany<EventMedium, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(EventMedium::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<TalkProposal, $this>
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(TalkProposal::class);
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * @return HasMany<EventQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(EventQuestion::class)->orderBy('position');
    }

    /**
     * @param  QueryBuilder  $query
     */
    public function newEloquentBuilder($query): EventQueryBuilder
    {
        return new EventQueryBuilder($query);
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::Published;
    }

    public function isUpcoming(): bool
    {
        return $this->ends_at->isFuture();
    }

    public function isAcceptingProposals(): bool
    {
        return $this->isPublished()
            && $this->cfp_enabled
            && ($this->cfp_opens_at === null || ! $this->cfp_opens_at->isFuture())
            && ($this->cfp_closes_at === null || $this->cfp_closes_at->isFuture());
    }

    public function registeredCount(): int
    {
        return $this->registrations()
            ->where('status', RegistrationStatus::Registered)
            ->count();
    }

    public function acceptsRegistrations(): bool
    {
        return $this->isPublished() && $this->isUpcoming() && $this->registration_enabled;
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->registeredCount() >= $this->capacity;
    }

    public function registrationFor(?User $user): ?EventRegistration
    {
        if ($user === null) {
            return null;
        }

        return $this->registrations->first(
            fn (EventRegistration $registration) => $registration->user_id === $user->id
                && $registration->status !== RegistrationStatus::Cancelled,
        );
    }
}
