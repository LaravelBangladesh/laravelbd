<?php

namespace App\Domain\Events\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $role
 * @property int $sort_order
 */
class SpeakerAssignment extends Pivot {}
