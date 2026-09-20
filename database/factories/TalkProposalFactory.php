<?php

namespace Database\Factories;

use App\Domain\Cfp\Enums\ProposalKind;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TalkProposal>
 */
class TalkProposalFactory extends Factory
{
    /**
     * @var class-string<TalkProposal>
     */
    protected $model = TalkProposal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => ProposalKind::Talk,
            'status' => ProposalStatus::Submitted,
            'title_en' => fake()->sentence(6),
            'abstract_en' => fake()->paragraph(),
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::Accepted,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::Rejected,
        ]);
    }
}
