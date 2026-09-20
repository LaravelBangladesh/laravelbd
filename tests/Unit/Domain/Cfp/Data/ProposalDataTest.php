<?php

use App\Domain\Cfp\Data\ProposalData;
use App\Domain\Cfp\Enums\ProposalKind;

test('builds proposal data from validated input', function () {
    $data = ProposalData::fromValidated([
        'title_en' => 'Pest in practice',
        'title_bn' => 'পেস্ট',
        'abstract_en' => 'How we test.',
        'abstract_bn' => null,
        'kind' => ProposalKind::Workshop->value,
    ]);

    expect($data->titleEn)->toBe('Pest in practice')
        ->and($data->titleBn)->toBe('পেস্ট')
        ->and($data->abstractBn)->toBeNull()
        ->and($data->kind)->toBe(ProposalKind::Workshop)
        ->and($data->attributes()['abstract_en'])->toBe('How we test.');
});

test('leaves the optional bangla fields null when they are absent', function () {
    $data = ProposalData::fromValidated([
        'title_en' => 'Talk',
        'abstract_en' => 'Abstract',
        'kind' => ProposalKind::Talk->value,
    ]);

    expect($data->titleBn)->toBeNull()
        ->and($data->abstractBn)->toBeNull();
});
