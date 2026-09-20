import { Seo } from '@/components/seo';
import { Heading } from '@/components/catalyst/heading';
import { Text } from '@/components/catalyst/text';
import { pageHeaderClass, Button, Check, Surface } from '@/components/design';
import {
    DirectoryFormFields,
    type DirectoryFormValues,
} from '@/components/directory-form-fields';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

const PROFILE_FIELDS = ['name', 'photo', 'title', 'company'] as const;

type ProfileField = (typeof PROFILE_FIELDS)[number];

function ProfileCompleteness({
    missing,
    returnTo,
}: {
    missing: ProfileField[];
    returnTo: { label: string } | null;
}) {
    const t = useTrans();

    return (
        <Surface className="mt-8 p-5 sm:p-6">
            <h2 className="text-ink text-lg font-medium tracking-tight">
                {t('profile.completeness')}
            </h2>
            <p className="text-ink-muted mt-2 text-[15px] leading-7">
                {returnTo
                    ? t('profile.continue_to', { destination: returnTo.label })
                    : t('profile.completeness_lead')}
            </p>
            <ul className="mt-4 space-y-2">
                {PROFILE_FIELDS.map((field) => {
                    const done = !missing.includes(field);

                    return (
                        <li
                            key={field}
                            className="flex items-center gap-3 text-[15px]"
                        >
                            <span
                                className={
                                    done
                                        ? 'text-brand-green flex size-5 items-center justify-center'
                                        : 'border-line text-ink-muted flex size-5 items-center justify-center rounded-full border'
                                }
                            >
                                {done ? <Check className="size-4" /> : null}
                            </span>
                            <span className="text-ink">
                                {t(`profile.field.${field}`)}
                            </span>
                            <span className="text-ink-muted ml-auto text-xs font-bold tracking-[0.12em] uppercase">
                                {done
                                    ? t('profile.field_done')
                                    : t('profile.field_missing')}
                            </span>
                        </li>
                    );
                })}
            </ul>
        </Surface>
    );
}

export default function AccountDirectory({
    listing,
    missing = [],
    return_to: returnTo = null,
}: {
    listing: DirectoryFormValues;
    missing?: ProfileField[];
    return_to?: { label: string } | null;
}) {
    const t = useTrans();
    const exists = Boolean(listing.id);

    return (
        <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 sm:py-16">
            <Seo
                title={t('account.directory')}
                description={t('account.directory')}
                noindex
            />
            <div className={`${pageHeaderClass} sm:items-start`}>
                <div>
                    <p className="text-brand-green text-sm font-medium tracking-wide">
                        {t('nav.directory')}
                    </p>
                    <Heading>{t('account.directory')}</Heading>
                    <Text className="mt-2">{t('account.directory_lead')}</Text>
                    {exists && listing.status_label && (
                        <Text className="mt-2">
                            {listing.is_published
                                ? listing.status_label
                                : t('account.directory_pending')}
                        </Text>
                    )}
                </div>
                {listing.slug && (
                    <Button
                        href={`/directory/${listing.slug}`}
                        variant="outline"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.view_public')}
                    </Button>
                )}
            </div>

            <ProfileCompleteness missing={missing} returnTo={returnTo} />

            <ValidatedForm
                action="/account/directory"
                method={exists ? 'patch' : 'post'}
                encType="multipart/form-data"
                className="mt-8 grid grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <DirectoryFormFields
                            listing={listing}
                            errors={errors}
                        />
                        <Button
                            type="submit"
                            className="w-full sm:w-auto sm:justify-self-start"
                            disabled={processing}
                        >
                            {t('account.save')}
                        </Button>
                    </>
                )}
            </ValidatedForm>
        </div>
    );
}
