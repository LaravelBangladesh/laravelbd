import { AdminSection } from '@/components/admin-page-header';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { FieldError } from '@/components/field-error';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { ImageUploader } from '@/components/image-uploader';
import { PhoneInput } from '@/components/phone-input';
import { useTrans } from '@/lib/i18n';

export type ProfileFormValues = {
    id?: string;
    slug?: string | null;
    name?: string;
    title?: string | null;
    company?: string | null;
    city?: string | null;
    bio_en?: string | null;
    bio_bn?: string | null;
    website?: string | null;
    github?: string | null;
    linkedin?: string | null;
    x?: string | null;
    photo_url?: string | null;
    mobile_number?: string | null;
    directory_status?: string;
    directory_status_label?: string;
    is_listed?: boolean;
};

/**
 * The one profile a person keeps: edited by its owner on the account page
 * and by staff, who also see the directory status, in the admin.
 */
export function ProfileFormFields({
    profile,
    visibilities,
    errors,
}: {
    profile?: ProfileFormValues;
    visibilities?: FieldOption[];
    errors?: Record<string, string>;
}) {
    const t = useTrans();

    // The account page reuses these fields without the admin Surface framing.
    const Group = visibilities
        ? AdminSection
        : ({ children }: { title: string; children: React.ReactNode }) => (
              <div className="grid grid-cols-1 gap-6">{children}</div>
          );

    return (
        <>
            <Group title={t('admin.section.photo')}>
                <Field>
                    <Label>{t('admin.photo')}</Label>
                    <Description>{t('admin.image.speaker')}</Description>
                    <ImageUploader
                        name="photo"
                        aspect="square"
                        previewUrl={profile?.photo_url}
                    />
                </Field>
            </Group>

            <Group title={t('admin.section.profile')}>
                <Field>
                    <Label required>{t('auth.name')}</Label>
                    <Input
                        name="name"
                        defaultValue={profile?.name ?? ''}
                        required
                    />
                    <FieldError error={errors?.name} />
                </Field>
                <PhoneInput
                    defaultValue={profile?.mobile_number}
                    error={errors?.mobile_number}
                />
                {visibilities ? (
                    <Field>
                        <Label required>{t('admin.directory_status')}</Label>
                        <FieldSelect
                            name="directory_status"
                            options={visibilities}
                            defaultValue={profile?.directory_status ?? 'hidden'}
                            required
                        />
                    </Field>
                ) : null}
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('directory.designation')}</Label>
                        <Input
                            name="title"
                            defaultValue={profile?.title ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('directory.company')}</Label>
                        <Input
                            name="company"
                            defaultValue={profile?.company ?? ''}
                        />
                    </Field>
                </div>
                <Field>
                    <Label>{t('directory.city')}</Label>
                    <Input name="city" defaultValue={profile?.city ?? ''} />
                </Field>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.bio_en')}</Label>
                        <Textarea
                            name="bio_en"
                            rows={4}
                            defaultValue={profile?.bio_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.bio_bn')}</Label>
                        <Textarea
                            name="bio_bn"
                            rows={4}
                            defaultValue={profile?.bio_bn ?? ''}
                        />
                    </Field>
                </div>
            </Group>

            <Group title={t('admin.section.links')}>
                <Field>
                    <Label>{t('directory.website')}</Label>
                    <Input
                        name="website"
                        type="url"
                        defaultValue={profile?.website ?? ''}
                    />
                    <FieldError error={errors?.website} />
                </Field>
                <div className="grid gap-4 md:grid-cols-3">
                    <Field>
                        <Label>{t('directory.github')}</Label>
                        <Input
                            name="github"
                            defaultValue={profile?.github ?? ''}
                        />
                        <FieldError error={errors?.github} />
                    </Field>
                    <Field>
                        <Label>{t('directory.linkedin')}</Label>
                        <Input
                            name="linkedin"
                            type="url"
                            defaultValue={profile?.linkedin ?? ''}
                        />
                        <FieldError error={errors?.linkedin} />
                    </Field>
                    <Field>
                        <Label>{t('directory.x')}</Label>
                        <Input name="x" defaultValue={profile?.x ?? ''} />
                        <FieldError error={errors?.x} />
                    </Field>
                </div>
            </Group>
        </>
    );
}
