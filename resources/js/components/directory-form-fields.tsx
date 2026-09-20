import { AdminSection } from '@/components/admin-page-header';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { FieldError } from '@/components/field-error';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { ImageUploader } from '@/components/image-uploader';
import { useTrans } from '@/lib/i18n';

export type DirectoryFormValues = {
    id?: string;
    slug?: string;
    name?: string;
    title?: string | null;
    company?: string | null;
    city?: string | null;
    kind?: string;
    status?: string;
    status_label?: string;
    is_published?: boolean;
    bio_en?: string | null;
    bio_bn?: string | null;
    website?: string | null;
    github?: string | null;
    linkedin?: string | null;
    x?: string | null;
    photo_url?: string | null;
};

export function DirectoryFormFields({
    listing,
    kinds,
    statuses,
    errors,
}: {
    listing?: DirectoryFormValues;
    kinds?: FieldOption[];
    statuses?: FieldOption[];
    errors?: Record<string, string>;
}) {
    const t = useTrans();
    const isAdmin = Boolean(kinds && statuses);

    // The account page reuses these fields without the admin Surface framing.
    const Group = isAdmin
        ? AdminSection
        : ({ children }: { title: string; children: React.ReactNode }) => (
              <div className="grid grid-cols-1 gap-6">{children}</div>
          );

    return (
        <>
            <Group title={t('admin.section.profile')}>
                <Field>
                    <Label required>{t('auth.name')}</Label>
                    <Input
                        name="name"
                        defaultValue={listing?.name ?? ''}
                        required
                    />
                    <FieldError error={errors?.name} />
                </Field>
                {kinds && statuses ? (
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field>
                            <Label required>{t('directory.kind')}</Label>
                            <FieldSelect
                                name="kind"
                                options={kinds}
                                defaultValue={listing?.kind ?? 'person'}
                                required
                            />
                        </Field>
                        <Field>
                            <Label required>{t('admin.status')}</Label>
                            <FieldSelect
                                name="status"
                                options={statuses}
                                defaultValue={listing?.status ?? 'draft'}
                                required
                            />
                        </Field>
                    </div>
                ) : null}
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>
                            {isAdmin
                                ? t('admin.speaker_title')
                                : t('directory.designation')}
                        </Label>
                        <Input
                            name="title"
                            defaultValue={listing?.title ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('directory.company')}</Label>
                        <Input
                            name="company"
                            defaultValue={listing?.company ?? ''}
                        />
                    </Field>
                </div>
                <Field>
                    <Label>{t('directory.city')}</Label>
                    <Input name="city" defaultValue={listing?.city ?? ''} />
                </Field>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field>
                        <Label>{t('admin.bio_en')}</Label>
                        <Textarea
                            name="bio_en"
                            rows={4}
                            defaultValue={listing?.bio_en ?? ''}
                        />
                    </Field>
                    <Field>
                        <Label>{t('admin.bio_bn')}</Label>
                        <Textarea
                            name="bio_bn"
                            rows={4}
                            defaultValue={listing?.bio_bn ?? ''}
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
                        defaultValue={listing?.website ?? ''}
                    />
                    <FieldError error={errors?.website} />
                </Field>
                <div className="grid gap-4 md:grid-cols-3">
                    <Field>
                        <Label>{t('directory.github')}</Label>
                        <Input
                            name="github"
                            defaultValue={listing?.github ?? ''}
                        />
                        <FieldError error={errors?.github} />
                    </Field>
                    <Field>
                        <Label>{t('directory.linkedin')}</Label>
                        <Input
                            name="linkedin"
                            type="url"
                            defaultValue={listing?.linkedin ?? ''}
                        />
                        <FieldError error={errors?.linkedin} />
                    </Field>
                    <Field>
                        <Label>{t('directory.x')}</Label>
                        <Input name="x" defaultValue={listing?.x ?? ''} />
                        <FieldError error={errors?.x} />
                    </Field>
                </div>
            </Group>

            <Group title={t('admin.section.photo')}>
                <Field>
                    <Label>{t('admin.photo')}</Label>
                    <Description>{t('admin.image.speaker')}</Description>
                    <ImageUploader
                        name="photo"
                        aspect="square"
                        previewUrl={listing?.photo_url}
                    />
                </Field>
            </Group>
        </>
    );
}
