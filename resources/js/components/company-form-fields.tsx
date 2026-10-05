import { AdminSection } from '@/components/admin-page-header';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { FieldError } from '@/components/field-error';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { ImageUploader } from '@/components/image-uploader';
import { useTrans } from '@/lib/i18n';

export type CompanyFormValues = {
    id?: string;
    slug?: string;
    name?: string;
    title?: string | null;
    city?: string | null;
    status?: string;
    bio_en?: string | null;
    bio_bn?: string | null;
    website?: string | null;
    github?: string | null;
    linkedin?: string | null;
    x?: string | null;
    photo_url?: string | null;
};

export function CompanyFormFields({
    company,
    statuses,
    errors,
}: {
    company?: CompanyFormValues;
    statuses: FieldOption[];
    errors?: Record<string, string>;
}) {
    const t = useTrans();

    return (
        <>
            <AdminSection title={t('admin.section.profile')}>
                <Field>
                    <Label required>{t('auth.name')}</Label>
                    <Input
                        name="name"
                        defaultValue={company?.name ?? ''}
                        required
                    />
                    <FieldError error={errors?.name} />
                </Field>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.company_tagline')}</Label>
                        <Input
                            name="title"
                            defaultValue={company?.title ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label required>{t('admin.status')}</Label>
                        <FieldSelect
                            name="status"
                            options={statuses}
                            defaultValue={company?.status ?? 'draft'}
                            required
                        />
                    </Field>
                </div>
                <Field>
                    <Label>{t('directory.city')}</Label>
                    <Input name="city" defaultValue={company?.city ?? ''} />
                </Field>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.bio_en')}</Label>
                        <Textarea
                            name="bio_en"
                            rows={4}
                            defaultValue={company?.bio_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.bio_bn')}</Label>
                        <Textarea
                            name="bio_bn"
                            rows={4}
                            defaultValue={company?.bio_bn ?? ''}
                        />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.links')}>
                <Field>
                    <Label>{t('directory.website')}</Label>
                    <Input
                        name="website"
                        type="url"
                        defaultValue={company?.website ?? ''}
                    />
                    <FieldError error={errors?.website} />
                </Field>
                <div className="grid gap-4 md:grid-cols-3">
                    <Field>
                        <Label>{t('directory.github')}</Label>
                        <Input
                            name="github"
                            defaultValue={company?.github ?? ''}
                        />
                        <FieldError error={errors?.github} />
                    </Field>
                    <Field>
                        <Label>{t('directory.linkedin')}</Label>
                        <Input
                            name="linkedin"
                            type="url"
                            defaultValue={company?.linkedin ?? ''}
                        />
                        <FieldError error={errors?.linkedin} />
                    </Field>
                    <Field>
                        <Label>{t('directory.x')}</Label>
                        <Input name="x" defaultValue={company?.x ?? ''} />
                        <FieldError error={errors?.x} />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.photo')}>
                <Field>
                    <Label>{t('admin.photo')}</Label>
                    <Description>{t('admin.image.speaker')}</Description>
                    <ImageUploader
                        name="photo"
                        aspect="square"
                        previewUrl={company?.photo_url}
                    />
                </Field>
            </AdminSection>
        </>
    );
}
