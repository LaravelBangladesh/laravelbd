<?php

namespace App\Domain\Identity\Models;

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\ProfilePhoto;
use App\Domain\Identity\QueryBuilders\UserQueryBuilder;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\UniqueSlug;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;

/**
 * @property string $id
 * @property string $name
 * @property string|null $slug
 * @property string $email
 * @property string|null $pending_email
 * @property string|null $mobile_number
 * @property UserRole $role
 * @property string $locale
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
 * @property DirectoryVisibility $directory_status
 * @property Carbon|null $directory_published_at
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static UserQueryBuilder query()
 */
#[Fillable([
    'name',
    'email',
    'role',
    'locale',
    'slug',
    'mobile_number',
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
    'directory_status',
    'directory_published_at',
])]
#[Hidden(['password', 'remember_token', 'mobile_number'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent, Notifiable, PasskeyAuthenticatable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'directory_status' => 'hidden',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'directory_status' => DirectoryVisibility::class,
            'directory_published_at' => 'datetime',
        ];
    }

    /**
     * @param  QueryBuilder  $query
     */
    public function newEloquentBuilder($query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isModerator(): bool
    {
        return $this->role === UserRole::Moderator;
    }

    public function isListed(): bool
    {
        return $this->directory_status === DirectoryVisibility::Listed;
    }

    public function needsProfile(): bool
    {
        return blank($this->name);
    }

    public function hasCompleteProfile(): bool
    {
        return $this->missingProfileFields() === [];
    }

    /**
     * @return list<string>
     */
    public function missingProfileFields(): array
    {
        return array_keys(array_filter([
            'name' => blank($this->name),
            'photo' => blank($this->photo_path),
            'title' => blank($this->title),
            'company' => blank($this->company),
            'mobile_number' => blank($this->mobile_number),
        ]));
    }

    public function photoUrl(): string
    {
        return resolve(ImageStorage::class)->url($this->photo_path)
            ?? ProfilePhoto::placeholder();
    }

    /**
     * The directory URL follows the name, so a first save or a rename gives
     * the profile a slug no other user or company holds.
     */
    public function refreshSlug(): void
    {
        if ($this->slug === null || $this->isDirty('name')) {
            $this->slug = UniqueSlug::make($this->name, ['users', 'companies'], $this->id);
        }
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * @return HasMany<TalkProposal, $this>
     */
    public function talkProposals(): HasMany
    {
        return $this->hasMany(TalkProposal::class);
    }
}
