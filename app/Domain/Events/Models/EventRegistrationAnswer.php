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
 * @property array{id: string, kind: string, label_en: string, label_bn: string|null, help_en: string|null, help_bn: string|null, options: list<string>|null, required: bool} $question
 * @property string|list<string> $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['event_registration_id', 'question', 'value'])]
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
            'question' => 'array',
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
     * @return list<string>
     */
    public function values(): array
    {
        $value = $this->value;

        return is_array($value) ? $value : [(string) $value];
    }
}
