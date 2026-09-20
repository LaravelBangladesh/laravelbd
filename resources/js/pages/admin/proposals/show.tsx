import { Seo } from '@/components/seo';
import { AdminPageHeader, AdminSection } from '@/components/admin-page-header';
import { ValidatedForm } from '@/components/validated-form';
import { actionRowClass, Button, Surface } from '@/components/design';
import { Field, Label } from '@/components/catalyst/fieldset';
import { FieldError } from '@/components/field-error';
import { StatusChip } from '@/components/status-chip';
import { Textarea } from '@/components/catalyst/textarea';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';

type ProposalDetail = {
    id: string;
    title_en: string;
    title_bn: string | null;
    abstract_en: string;
    abstract_bn: string | null;
    kind_label: string;
    status: string;
    status_label: string;
    notes: string | null;
    submitter: { name: string | null; email: string | null };
    event_id: string | null;
    event: { slug: string; title: string } | null;
};

export default function AdminProposalShow({
    proposal,
    statuses,
    events,
}: {
    proposal: ProposalDetail;
    statuses: FieldOption[];
    events: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={proposal.title_en}
                description={proposal.title_en}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.proposals_title')}
                title={proposal.title_en}
                description={[
                    proposal.submitter.name,
                    proposal.submitter.email,
                    proposal.kind_label,
                ]
                    .filter(Boolean)
                    .join(' · ')}
                actions={
                    <StatusChip
                        status={proposal.status}
                        label={proposal.status_label}
                    />
                }
            />
            <Surface className="mt-8 max-w-3xl p-5 sm:p-6">
                {proposal.event && (
                    <p className="text-ink-muted text-sm">
                        {proposal.event.title}
                    </p>
                )}
                <p className="text-ink-muted mt-3 text-[15px] leading-7 whitespace-pre-line">
                    {proposal.abstract_en}
                </p>
                {proposal.abstract_bn && (
                    <p className="text-ink-muted mt-4 text-[15px] leading-7 whitespace-pre-line">
                        {proposal.abstract_bn}
                    </p>
                )}
            </Surface>
            <ValidatedForm
                action={`/admin/proposals/${proposal.id}`}
                method="put"
                className="mt-6 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <AdminSection title={t('admin.section.review')}>
                            <Field>
                                <Label required>{t('cfp.status')}</Label>
                                <FieldSelect
                                    name="status"
                                    options={statuses}
                                    defaultValue={proposal.status}
                                    required
                                />
                            </Field>
                            <Field>
                                <Label
                                    required={proposal.status === 'accepted'}
                                >
                                    {t('cfp.event')}
                                </Label>
                                <FieldSelect
                                    name="event_id"
                                    options={[
                                        {
                                            value: '',
                                            label: t('admin.none'),
                                        },
                                        ...events,
                                    ]}
                                    defaultValue={proposal.event_id ?? ''}
                                />
                                <p className="text-ink-muted mt-2 text-sm">
                                    {t('cfp.event_accept_help')}
                                </p>
                                <FieldError error={errors.event_id} />
                            </Field>
                            <Field>
                                <Label>{t('cfp.notes')}</Label>
                                <Textarea
                                    name="notes"
                                    rows={4}
                                    defaultValue={proposal.notes ?? ''}
                                />
                                <FieldError error={errors.notes} />
                            </Field>
                        </AdminSection>
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.save')}
                            </Button>
                            <Button href="/admin/proposals" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
