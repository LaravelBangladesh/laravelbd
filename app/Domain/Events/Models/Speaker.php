<?php

namespace App\Domain\Events\Models;

use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use Database\Factories\SpeakerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $title
 * @property string|null $company
 * @property string|null $bio_en
 * @property string|null $bio_bn
 * @property string|null $photo_path
 * @property string|null $website
 * @property string|null $github
 * @property string|null $linkedin
 * @property string|null $x
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SpeakerAssignment|null $pivot Set when loaded through an event or session.
 */
#[Fillable([
    'name',
    'slug',
    'title',
    'company',
    'bio_en',
    'bio_bn',
    'photo_path',
    'website',
    'github',
    'linkedin',
    'x',
])]
class Speaker extends Model
{
    /** @use HasFactory<SpeakerFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    /**
     * @return BelongsToMany<Event, $this, SpeakerAssignment>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->using(SpeakerAssignment::class)
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<EventSession, $this, SpeakerAssignment>
     */
    public function eventSessions(): BelongsToMany
    {
        return $this->belongsToMany(EventSession::class, 'session_speaker', 'speaker_id', 'session_id')
            ->using(SpeakerAssignment::class)
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }
}
