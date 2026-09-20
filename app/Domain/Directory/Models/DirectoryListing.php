<?php

namespace App\Domain\Directory\Models;

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\QueryBuilders\DirectoryListingQueryBuilder;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\ProfilePhoto;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use App\Domain\Shared\Contracts\ImageStorage;
use Database\Factories\DirectoryListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $slug
 * @property DirectoryKind $kind
 * @property DirectoryStatus $status
 * @property string $name
 * @property string|null $title
 * @property string|null $company
 * @property string|null $city
 * @property string|null $bio_en
 * @property string|null $bio_bn
 * @property string|null $photo_path
 * @property string|null $website
 * @property string|null $github
 * @property string|null $linkedin
 * @property string|null $x
 * @property Carbon|null $published_at
 * @property string|null $created_by
 * @property string|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static DirectoryListingQueryBuilder query()
 */
#[Fillable([
    'slug',
    'kind',
    'status',
    'name',
    'title',
    'company',
    'city',
    'bio_en',
    'bio_bn',
    'photo_path',
    'website',
    'github',
    'linkedin',
    'x',
    'published_at',
    'user_id',
])]
class DirectoryListing extends Model
{
    /** @use HasFactory<DirectoryListingFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => DirectoryKind::class,
            'status' => DirectoryStatus::class,
            'published_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  QueryBuilder  $query
     */
    public function newEloquentBuilder($query): DirectoryListingQueryBuilder
    {
        return new DirectoryListingQueryBuilder($query);
    }

    public function isPublished(): bool
    {
        return $this->status === DirectoryStatus::Published;
    }

    public function photoUrl(): ?string
    {
        if ($this->photo_path !== null && $this->photo_path !== '') {
            return resolve(ImageStorage::class)->url($this->photo_path);
        }

        return $this->kind === DirectoryKind::Person
            ? ProfilePhoto::placeholder()
            : null;
    }
}
