import { AdminSection } from '@/components/admin-page-header';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { FieldError } from '@/components/field-error';
import { ImageUploader } from '@/components/image-uploader';
import { useTrans } from '@/lib/i18n';

export type SpeakerFormValues = {
    name?: string;
    title?: string | null;
    company?: string | null;
    bio_en?: string | null;
    bio_bn?: string | null;
    website?: string | null;
    github?: string | null;
    linkedin?: string | null;
    x?: string | null;
    photo_url?: string | null;
};

export function SpeakerFormFields({
    speaker,
    errors,
}: {
    speaker?: SpeakerFormValues;
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
                        defaultValue={speaker?.name ?? ''}
                        required
                    />
                    <FieldError error={errors?.name} />
                </Field>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.speaker_title')}</Label>
                        <Input
                            name="title"
                            defaultValue={speaker?.title ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.company')}</Label>
                        <Input
                            name="company"
                            defaultValue={speaker?.company ?? ''}
                        />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.bio_en')}</Label>
                        <Textarea
                            name="bio_en"
                            rows={4}
                            defaultValue={speaker?.bio_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.bio_bn')}</Label>
                        <Textarea
                            name="bio_bn"
                            rows={4}
                            defaultValue={speaker?.bio_bn ?? ''}
                        />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.links')}>
                <Field>
                    <Label>{t('admin.website')}</Label>
                    <Input
                        name="website"
                        type="url"
                        defaultValue={speaker?.website ?? ''}
                    />
                    <FieldError error={errors?.website} />
                </Field>
                <div className="grid gap-4 md:grid-cols-3">
                    <Field>
                        <Label>{t('admin.github')}</Label>
                        <Input
                            name="github"
                            defaultValue={speaker?.github ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.linkedin')}</Label>
                        <Input
                            name="linkedin"
                            type="url"
                            defaultValue={speaker?.linkedin ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.x')}</Label>
                        <Input name="x" defaultValue={speaker?.x ?? ''} />
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
                        previewUrl={speaker?.photo_url}
                    />
                </Field>
            </AdminSection>
        </>
    );
}
