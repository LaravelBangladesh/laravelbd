import { Form } from '@inertiajs/react';
import { Seo } from '@/components/seo';
import { Heading } from '@/components/catalyst/heading';
import { Text } from '@/components/catalyst/text';
import { pageHeaderClass, Button, Check, Surface } from '@/components/design';
import {
    ProfileFormFields,
    type ProfileFormValues,
} from '@/components/profile-form-fields';
import { StatusChip } from '@/components/status-chip';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

const PROFILE_FIELDS = [
    'name',
    'photo',
    'title',
    'company',
    'mobile_number',
] as const;

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
            {missing.length > 0 && (
                <p className="text-ink-muted mt-2 text-[15px] leading-7">
                    {t('profile.shared_note')}
                </p>
            )}
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

/**
 * Members ask to be listed and can hide again at any time; only staff list
 * them, so there is never a button that lists directly.
 */
function DirectoryListing({ profile }: { profile: ProfileFormValues }) {
    const t = useTrans();
    const status = profile.directory_status ?? 'hidden';
    const action =
        status === 'hidden'
            ? { visibility: 'pending', label: t('account.directory_request') }
            : {
                  visibility: 'hidden',
                  label:
                      status === 'pending'
                          ? t('account.directory_withdraw')
                          : t('account.directory_hide'),
              };

    return (
        <Surface className="mt-8 p-5 sm:p-6">
            <div className="flex flex-wrap items-center gap-3">
                <h2 className="text-ink text-lg font-medium tracking-tight">
                    {t('account.directory_status')}
                </h2>
                {profile.directory_status_label && (
                    <StatusChip
                        status={status}
                        label={profile.directory_status_label}
                    />
                )}
            </div>
            <p className="text-ink-muted mt-2 text-[15px] leading-7">
                {t(`account.directory_status_${status}`)}
            </p>
            <Form
                action="/account/directory/visibility"
                method="patch"
                className="mt-4"
            >
                <input
                    type="hidden"
                    name="visibility"
                    value={action.visibility}
                />
                <Button
                    type="submit"
                    variant={status === 'hidden' ? 'primary' : 'outline'}
                    className="w-full sm:w-auto"
                >
                    {action.label}
                </Button>
            </Form>
        </Surface>
    );
}

export default function AccountDirectory({
    profile,
    missing = [],
    return_to: returnTo = null,
}: {
    profile: ProfileFormValues;
    missing?: ProfileField[];
    return_to?: { label: string } | null;
}) {
    const t = useTrans();

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
                        {t('nav.account')}
                    </p>
                    <Heading>{t('account.directory')}</Heading>
                    <Text className="mt-2">{t('account.directory_lead')}</Text>
                </div>
                {profile.slug && (
                    <Button
                        href={`/directory/${profile.slug}`}
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
                method="patch"
                encType="multipart/form-data"
                className="mt-8 grid grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <ProfileFormFields profile={profile} errors={errors} />
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

            <DirectoryListing profile={profile} />
        </div>
    );
}
