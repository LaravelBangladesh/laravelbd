import { useState } from 'react';
import { AdminSection } from '@/components/admin-page-header';
import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { FieldError } from '@/components/field-error';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';

export type ResourceFormValues = {
    title_en?: string;
    title_bn?: string | null;
    excerpt_en?: string | null;
    excerpt_bn?: string | null;
    description_en?: string | null;
    description_bn?: string | null;
    kind?: string;
    status?: string;
    url?: string | null;
    embed_url?: string | null;
    event_id?: string | null;
    speaker_id?: string | null;
};

export function ResourceFormFields({
    resource,
    kinds,
    statuses,
    events,
    speakers,
    errors,
}: {
    resource?: ResourceFormValues;
    kinds: FieldOption[];
    statuses: FieldOption[];
    events: FieldOption[];
    speakers: FieldOption[];
    errors?: Record<string, string>;
}) {
    const t = useTrans();
    const [kind, setKind] = useState(resource?.kind ?? 'link');
    const none = { value: '', label: t('admin.none') };

    return (
        <>
            <AdminSection title={t('admin.section.content')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label required>{t('admin.title_en')}</Label>
                        <Input
                            name="title_en"
                            defaultValue={resource?.title_en ?? ''}
                            required
                        />
                        <FieldError error={errors?.title_en} />
                    </Field>
                    <Field>
                        <Label>{t('admin.title_bn')}</Label>
                        <Input
                            name="title_bn"
                            defaultValue={resource?.title_bn ?? ''}
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.excerpt_en')}</Label>
                        <Textarea
                            name="excerpt_en"
                            defaultValue={resource?.excerpt_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.excerpt_bn')}</Label>
                        <Textarea
                            name="excerpt_bn"
                            defaultValue={resource?.excerpt_bn ?? ''}
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.description_en')}</Label>
                        <Textarea
                            name="description_en"
                            rows={5}
                            defaultValue={resource?.description_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.description_bn')}</Label>
                        <Textarea
                            name="description_bn"
                            rows={5}
                            defaultValue={resource?.description_bn ?? ''}
                        />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.link')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label required>{t('resources.kind')}</Label>
                        <FieldSelect
                            name="kind"
                            options={kinds}
                            defaultValue={kind}
                            onChange={setKind}
                            required
                        />
                    </Field>
                    <Field>
                        <Label required>{t('admin.status')}</Label>
                        <FieldSelect
                            name="status"
                            options={statuses}
                            defaultValue={resource?.status ?? 'draft'}
                            required
                        />
                    </Field>
                </div>
                {kind === 'video' ? (
                    <Field>
                        <Label required>{t('admin.youtube_url')}</Label>
                        <Input
                            name="embed_url"
                            type="url"
                            defaultValue={resource?.embed_url ?? ''}
                            required
                        />
                        <FieldError error={errors?.embed_url} />
                    </Field>
                ) : (
                    <Field>
                        <Label required>{t('admin.resource_url')}</Label>
                        <Input
                            name="url"
                            type="url"
                            defaultValue={resource?.url ?? ''}
                            required
                        />
                        <FieldError error={errors?.url} />
                    </Field>
                )}
            </AdminSection>

            <AdminSection title={t('admin.section.related')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.events')}</Label>
                        <FieldSelect
                            name="event_id"
                            options={[none, ...events]}
                            defaultValue={resource?.event_id ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.speakers')}</Label>
                        <FieldSelect
                            name="speaker_id"
                            options={[none, ...speakers]}
                            defaultValue={resource?.speaker_id ?? ''}
                        />
                    </Field>
                </div>
            </AdminSection>
        </>
    );
}
