<?php

namespace App\Application\Cfp\ViewModels;

use App\Domain\Cfp\Enums\ProposalKind;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;

class ProposalPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function card(TalkProposal $proposal): array
    {
        return [
            'id' => $proposal->id,
            'title' => $proposal->localized('title'),
            'abstract' => $proposal->localized('abstract'),
            'kind' => $proposal->kind->value,
            'kind_label' => $proposal->kind->label(),
            'status' => $proposal->status->value,
            'status_label' => $proposal->status->label(),
            'event_id' => $proposal->event_id,
            'event' => $proposal->event === null ? null : [
                'slug' => $proposal->event->slug,
                'title' => $proposal->event->localized('title'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function admin(TalkProposal $proposal): array
    {
        return [
            ...self::card($proposal),
            'title_en' => $proposal->title_en,
            'title_bn' => $proposal->title_bn,
            'abstract_en' => $proposal->abstract_en,
            'abstract_bn' => $proposal->abstract_bn,
            'notes' => $proposal->notes,
            'submitter' => [
                'name' => $proposal->submitter?->name,
                'email' => $proposal->submitter?->email,
            ],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function kinds(): array
    {
        return array_map(fn (ProposalKind $kind) => [
            'value' => $kind->value,
            'label' => $kind->label(),
        ], ProposalKind::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statuses(): array
    {
        return array_map(fn (ProposalStatus $status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ], ProposalStatus::cases());
    }
}
