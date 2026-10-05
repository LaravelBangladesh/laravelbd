import { AdminSection } from '@/components/admin-page-header';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { FieldError } from '@/components/field-error';
import { ImageUploader } from '@/components/image-uploader';
import { useTrans } from '@/lib/i18n';

/**
 * A guest speaker becomes a user, so the email is required: it is how they
 * sign in later and take over their profile.
 */
export function SpeakerFormFields({
    errors,
}: {
    errors?: Record<string, string>;
}) {
    const t = useTrans();

    return (
        <>
            <AdminSection title={t('admin.section.profile')}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label required>{t('auth.name')}</Label>
                        <Input name="name" required />
                        <FieldError error={errors?.name} />
                    </Field>
                    <Field>
                        <Label required>{t('auth.email')}</Label>
                        <Description>
                            {t('admin.speaker_email_help')}
                        </Description>
                        <Input name="email" type="email" required />
                        <FieldError error={errors?.email} />
                    </Field>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.speaker_title')}</Label>
                        <Input name="title" />
                    </Field>
                    <Field>
                        <Label>{t('admin.company')}</Label>
                        <Input name="company" />
                    </Field>
                </div>
            </AdminSection>

            <AdminSection title={t('admin.section.photo')}>
                <Field>
                    <Label>{t('admin.photo')}</Label>
                    <Description>{t('admin.image.speaker')}</Description>
                    <ImageUploader name="photo" aspect="square" />
                </Field>
            </AdminSection>
        </>
    );
}
