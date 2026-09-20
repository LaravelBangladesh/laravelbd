<?php

namespace App\Domain\Events\Models;

use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use App\Domain\Shared\Concerns\LocalizesContent;
use Database\Factories\EventQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $event_id
 * @property QuestionKind $kind
 * @property string $label_en
 * @property string|null $label_bn
 * @property string|null $help_en
 * @property string|null $help_bn
 * @property list<string>|null $options
 * @property bool $required
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'event_id',
    'kind',
    'label_en',
    'label_bn',
    'help_en',
    'help_bn',
    'options',
    'required',
    'position',
])]
class EventQuestion extends Model
{
    /** @use HasFactory<EventQuestionFactory> */
    use HasFactory, HasUuidPrimaryKey, LocalizesContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => QuestionKind::class,
            'options' => 'array',
            'required' => 'boolean',
            'position' => 'integer',
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
     * @return list<string>
     */
    public function optionList(): array
    {
        return $this->options ?? [];
    }
}
