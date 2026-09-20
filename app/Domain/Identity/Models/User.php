<?php

namespace App\Domain\Identity\Models;

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\ProfilePhoto;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string|null $pending_email
 * @property UserRole $role
 * @property string $locale
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'role', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuidPrimaryKey, Notifiable, PasskeyAuthenticatable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
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
        $this->loadMissing('directoryListing');

        $listing = $this->directoryListing;

        return array_keys(array_filter([
            'name' => blank($this->name),
            'photo' => blank($listing?->photo_path),
            'title' => blank($listing?->title),
            'company' => blank($listing?->company),
        ]));
    }

    public function photoUrl(): string
    {
        $this->loadMissing('directoryListing');

        return $this->directoryListing?->photoUrl()
            ?? ProfilePhoto::placeholder();
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

    /**
     * @return HasOne<DirectoryListing, $this>
     */
    public function directoryListing(): HasOne
    {
        return $this->hasOne(DirectoryListing::class);
    }
}
