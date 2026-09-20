<?php

namespace App\Domain\Events\Models;

use App\Domain\Events\Enums\MediaKind;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use App\Domain\Shared\VideoEmbed;
use Database\Factories\EventMediumFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $event_id
 * @property MediaKind $kind
 * @property string|null $path
 * @property string|null $embed_url
 * @property string|null $caption_en
 * @property string|null $caption_bn
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'event_id',
    'kind',
    'path',
    'embed_url',
    'caption_en',
    'caption_bn',
    'sort_order',
])]
class EventMedium extends Model
{
    /** @use HasFactory<EventMediumFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    protected $table = 'event_media';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => MediaKind::class,
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

    public function isPhoto(): bool
    {
        return $this->kind === MediaKind::Photo && is_string($this->path) && $this->path !== '';
    }

    public function embedSrc(): ?string
    {
        if ($this->kind !== MediaKind::Video || ! is_string($this->embed_url)) {
            return null;
        }

        return VideoEmbed::src($this->embed_url);
    }
}
