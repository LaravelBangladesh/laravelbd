<?php

namespace App\Domain\Events\Models;

use App\Domain\Events\Enums\SessionKind;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use Database\Factories\EventSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $event_id
 * @property string $title_en
 * @property string|null $title_bn
 * @property string|null $description_en
 * @property string|null $description_bn
 * @property SessionKind $kind
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property string|null $room
 * @property string|null $recording_url
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'event_id',
    'title_en',
    'title_bn',
    'description_en',
    'description_bn',
    'kind',
    'starts_at',
    'ends_at',
    'room',
    'recording_url',
    'sort_order',
])]
class EventSession extends Model
{
    /** @use HasFactory<EventSessionFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    protected $table = 'event_sessions';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => SessionKind::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
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
     * @return BelongsToMany<Speaker, $this, SpeakerAssignment>
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class, 'session_speaker', 'session_id', 'speaker_id')
            ->using(SpeakerAssignment::class)
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
