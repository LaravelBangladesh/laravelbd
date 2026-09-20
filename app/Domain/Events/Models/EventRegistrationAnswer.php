<?php

namespace App\Domain\Events\Models;

use App\Domain\Shared\Concerns\HasUuidPrimaryKey;
use Database\Factories\EventRegistrationAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $event_registration_id
 * @property string $event_question_id
 * @property string|list<string> $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['event_registration_id', 'event_question_id', 'value'])]
class EventRegistrationAnswer extends Model
{
    /** @use HasFactory<EventRegistrationAnswerFactory> */
    use HasFactory, HasUuidPrimaryKey;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * @return BelongsTo<EventRegistration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    /**
     * @return BelongsTo<EventQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(EventQuestion::class, 'event_question_id');
    }

    /**
     * @return list<string>
     */
    public function values(): array
    {
        $value = $this->value;

        return is_array($value) ? $value : [(string) $value];
    }
}
