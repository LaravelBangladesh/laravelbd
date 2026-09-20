<?php

namespace App\Domain\Cfp\Models;

use App\Domain\Cfp\Enums\ProposalKind;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\QueryBuilders\TalkProposalQueryBuilder;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use Database\Factories\TalkProposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property ProposalKind $kind
 * @property ProposalStatus $status
 * @property string $title_en
 * @property string|null $title_bn
 * @property string $abstract_en
 * @property string|null $abstract_bn
 * @property string|null $notes
 * @property string $event_id
 * @property string|null $event_session_id
 * @property string $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static TalkProposalQueryBuilder query()
 */
#[Fillable([
    'kind',
    'status',
    'title_en',
    'title_bn',
    'abstract_en',
    'abstract_bn',
    'notes',
    'event_id',
    'event_session_id',
    'user_id',
])]
class TalkProposal extends Model
{
    /** @use HasFactory<TalkProposalFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ProposalKind::class,
            'status' => ProposalStatus::class,
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
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<EventSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'event_session_id');
    }

    /**
     * @param  QueryBuilder  $query
     */
    public function newEloquentBuilder($query): TalkProposalQueryBuilder
    {
        return new TalkProposalQueryBuilder($query);
    }

    public function isAccepted(): bool
    {
        return $this->status === ProposalStatus::Accepted;
    }
}
