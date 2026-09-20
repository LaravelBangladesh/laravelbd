import { AdminSection } from '@/components/admin-page-header';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { DateTimeField } from '@/components/datetime-field';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { FieldError } from '@/components/field-error';
import { FieldToggle } from '@/components/field-toggle';
import { ImageUploader } from '@/components/image-uploader';
import { useTrans } from '@/lib/i18n';

export type EventFormValues = {
    title_en?: string;
    title_bn?: string | null;
    excerpt_en?: string | null;
    excerpt_bn?: string | null;
    description_en?: string | null;
    description_bn?: string | null;
    type?: string;
    status?: string;
    starts_at?: string | null;
    ends_at?: string | null;
    venue_name?: string | null;
    venue_address?: string | null;
    venue_map_url?: string | null;
    online_url?: string | null;
    capacity?: number | null;
    registration_enabled?: boolean;
    cfp_enabled?: boolean;
    cfp_opens_at?: string | null;
    cfp_closes_at?: string | null;
    cover_url?: string | null;
};

export function EventFormFields({
    event,
    types,
    statuses,
    errors,
}: {
    event?: EventFormValues;
    types: FieldOption[];
    statuses: FieldOption[];
    errors?: Record<string, string>;
}) {
    const t = useTrans();

    return (
        <>
            <AdminSection title={t('admin.section.content')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label required>{t('admin.title_en')}</Label>
                        <Input
                            name="title_en"
                            defaultValue={event?.title_en ?? ''}
                            required
                        />
                        <FieldError error={errors?.title_en} />
                    </Field>
                    <Field>
                        <Label>{t('admin.title_bn')}</Label>
                        <Input
                            name="title_bn"
                            defaultValue={event?.title_bn ?? ''}
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.excerpt_en')}</Label>
                        <Textarea
                            name="excerpt_en"
                            defaultValue={event?.excerpt_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.excerpt_bn')}</Label>
                        <Textarea
                            name="excerpt_bn"
                            defaultValue={event?.excerpt_bn ?? ''}
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.description_en')}</Label>
                        <Textarea
                            name="description_en"
                            rows={5}
                            defaultValue={event?.description_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.description_bn')}</Label>
                        <Textarea
                            name="description_bn"
                            rows={5}
                            defaultValue={event?.description_bn ?? ''}
                        />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.schedule')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label required>{t('admin.event_type')}</Label>
                        <FieldSelect
                            name="type"
                            options={types}
                            defaultValue={event?.type ?? 'meetup'}
                            required
                        />
                    </Field>
                    <Field>
                        <Label required>{t('admin.status')}</Label>
                        <FieldSelect
                            name="status"
                            options={statuses}
                            defaultValue={event?.status ?? 'draft'}
                            required
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label required>{t('admin.starts_at')}</Label>
                        <DateTimeField
                            name="starts_at"
                            defaultValue={event?.starts_at ?? ''}
                            required
                        />
                        <FieldError error={errors?.starts_at} />
                    </Field>
                    <Field>
                        <Label required>{t('admin.ends_at')}</Label>
                        <DateTimeField
                            name="ends_at"
                            defaultValue={event?.ends_at ?? ''}
                            required
                        />
                        <FieldError error={errors?.ends_at} />
                    </Field>
                </div>
                <Field>
                    <Label>{t('admin.capacity')}</Label>
                    <Input
                        type="number"
                        name="capacity"
                        min={1}
                        defaultValue={event?.capacity ?? ''}
                    />
                    <FieldError error={errors?.capacity} />
                </Field>
                <fieldset>
                    <legend className="sr-only">
                        {t('admin.section.registration')}
                    </legend>
                    <FieldToggle
                        name="registration_enabled"
                        label={t('admin.registration_enabled')}
                        description={t('admin.registration_help')}
                        defaultChecked={event?.registration_enabled ?? true}
                        error={errors?.registration_enabled}
                    />
                </fieldset>
            </AdminSection>

            <AdminSection title={t('admin.section.cfp')}>
                <FieldToggle
                    name="cfp_enabled"
                    label={t('admin.cfp_enabled')}
                    description={t('admin.cfp_help')}
                    defaultChecked={event?.cfp_enabled ?? false}
                    error={errors?.cfp_enabled}
                />
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.cfp_opens_at')}</Label>
                        <DateTimeField
                            name="cfp_opens_at"
                            defaultValue={event?.cfp_opens_at ?? ''}
                        />
                        <FieldError error={errors?.cfp_opens_at} />
                    </Field>
                    <Field>
                        <Label>{t('admin.cfp_closes_at')}</Label>
                        <DateTimeField
                            name="cfp_closes_at"
                            defaultValue={event?.cfp_closes_at ?? ''}
                        />
                        <FieldError error={errors?.cfp_closes_at} />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.venue')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.venue')}</Label>
                        <Input
                            name="venue_name"
                            defaultValue={event?.venue_name ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.venue_address')}</Label>
                        <Input
                            name="venue_address"
                            defaultValue={event?.venue_address ?? ''}
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.venue_map')}</Label>
                        <Input
                            name="venue_map_url"
                            type="url"
                            defaultValue={event?.venue_map_url ?? ''}
                        />
                        <FieldError error={errors?.venue_map_url} />
                    </Field>
                    <Field>
                        <Label>{t('admin.online_url')}</Label>
                        <Input
                            name="online_url"
                            type="url"
                            defaultValue={event?.online_url ?? ''}
                        />
                        <FieldError error={errors?.online_url} />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.cover')}>
                <Field>
                    <Label>{t('admin.cover')}</Label>
                    <Description>{t('admin.image.cover')}</Description>
                    <ImageUploader
                        name="cover"
                        aspect="wide"
                        previewUrl={event?.cover_url}
                    />
                </Field>
            </AdminSection>
        </>
    );
}
