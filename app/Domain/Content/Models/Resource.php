<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\QueryBuilders\ResourceQueryBuilder;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $slug
 * @property ResourceKind $kind
 * @property ResourceStatus $status
 * @property string $title_en
 * @property string|null $title_bn
 * @property string|null $excerpt_en
 * @property string|null $excerpt_bn
 * @property string|null $description_en
 * @property string|null $description_bn
 * @property string|null $url
 * @property string|null $embed_url
 * @property string|null $event_id
 * @property string|null $speaker_id
 * @property Carbon|null $published_at
 * @property string|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static ResourceQueryBuilder query()
 */
#[Fillable([
    'slug',
    'kind',
    'status',
    'title_en',
    'title_bn',
    'excerpt_en',
    'excerpt_bn',
    'description_en',
    'description_bn',
    'url',
    'embed_url',
    'event_id',
    'speaker_id',
    'published_at',
])]
class Resource extends Model
{
    /** @use HasFactory<ResourceFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ResourceKind::class,
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
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
     * @return BelongsTo<Speaker, $this>
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  QueryBuilder  $query
     */
    public function newEloquentBuilder($query): ResourceQueryBuilder
    {
        return new ResourceQueryBuilder($query);
    }

    public function isPublished(): bool
    {
        return $this->status === ResourceStatus::Published;
    }
}
