import { Seo } from '@/components/seo';
import { Form, usePage } from '@inertiajs/react';
import ManagePasskeys from '@/components/manage-passkeys';
import { Field, Label } from '@/components/catalyst/fieldset';
import { FieldError } from '@/components/field-error';
import { ValidatedForm } from '@/components/validated-form';
import { Heading } from '@/components/catalyst/heading';
import { Input } from '@/components/catalyst/input';
import { Text } from '@/components/catalyst/text';
import { actionRowClass, Button, Chip } from '@/components/design';
import { FieldSelect } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';
import type { Passkey } from '@/types/auth';

type Registration = {
    status_label: string;
    event: {
        slug: string;
        title: string;
        starts_at: string | null;
    };
};

type Proposal = {
    id: string;
    title: string;
    kind_label: string;
    status: string;
    status_label: string;
    event: { slug: string; title: string } | null;
};

type Props = {
    canManagePasskeys: boolean;
    passkeys: Passkey[];
    status?: string;
    proposals: Proposal[];
    directory: {
        slug: string;
        status: string;
        status_label: string;
        is_published: boolean;
    } | null;
    registrations: Registration[];
};

export default function AccountEdit({
    canManagePasskeys,
    passkeys,
    status,
    proposals,
    directory,
    registrations,
}: Props) {
    const t = useTrans();
    const { auth, locales } = usePage().props;
    const user = auth.user;

    if (!user) {
        return null;
    }

    return (
        <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 sm:py-16">
            <Seo
                title={t('account.title')}
                description={t('account.title')}
                noindex
            />
            <p className="text-brand-green text-sm font-medium tracking-wide">
                {t('nav.account')}
            </p>
            <Heading>{t('account.title')}</Heading>
            <Text className="mt-2">{t('account.description')}</Text>

            <ValidatedForm
                action="/account"
                method="patch"
                className="mt-8 grid grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <Field>
                            <Label required>{t('auth.name')}</Label>
                            <Input
                                name="name"
                                defaultValue={user.name}
                                required
                            />
                            <FieldError error={errors.name} />
                        </Field>
                        <Field>
                            <Label required>{t('auth.email')}</Label>
                            <Input
                                type="email"
                                name="email"
                                defaultValue={user.pending_email ?? user.email}
                                required
                                autoComplete="email"
                            />
                            <Text className="mt-2">
                                {t('account.email_help')}
                            </Text>
                            <FieldError error={errors.email} />
                        </Field>
                        <Field>
                            <Label required>{t('account.locale')}</Label>
                            <FieldSelect
                                name="locale"
                                defaultValue={user.locale}
                                options={Object.entries(locales).map(
                                    ([value, label]) => ({
                                        value,
                                        label,
                                    }),
                                )}
                                required
                            />
                            <FieldError error={errors.locale} />
                        </Field>
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

            {user.pending_email && (
                <div className="border-line mt-10 grid grid-cols-1 gap-6 border-t pt-10">
                    <div>
                        <Heading level={2}>{t('account.email_verify')}</Heading>
                        <Text className="mt-2">
                            {t('account.email_pending')}
                        </Text>
                        {status && (
                            <Text className="text-brand-green mt-2">
                                {status}
                            </Text>
                        )}
                    </div>
                    <ValidatedForm
                        action="/account/email/code"
                        method="post"
                        className="grid grid-cols-1 gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <Field>
                                    <Label required>{t('auth.code')}</Label>
                                    <Input
                                        type="text"
                                        name="code"
                                        required
                                        inputMode="numeric"
                                        autoComplete="one-time-code"
                                        minLength={6}
                                        maxLength={6}
                                        pattern="[0-9]{6}"
                                    />
                                    <FieldError error={errors.code} />
                                </Field>
                                <Button
                                    type="submit"
                                    className="w-full sm:w-auto sm:justify-self-start"
                                    disabled={processing}
                                >
                                    {t('account.email_verify')}
                                </Button>
                            </>
                        )}
                    </ValidatedForm>
                    <Form action="/account/email/cancel" method="post">
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="outline"
                                className="w-full sm:w-auto"
                                disabled={processing}
                            >
                                {t('account.email_cancel')}
                            </Button>
                        )}
                    </Form>
                </div>
            )}

            <div className="border-line mt-12 border-t pt-10">
                <Heading level={2}>{t('account.directory')}</Heading>
                <Text className="mt-3">{t('account.directory_lead')}</Text>
                {directory ? (
                    <div className={`${actionRowClass} mt-4`}>
                        <Button href="/account/directory">
                            {t('account.directory_edit')}
                        </Button>
                        <Button
                            href={`/directory/${directory.slug}`}
                            variant="outline"
                        >
                            {t('admin.view_public')}
                        </Button>
                    </div>
                ) : (
                    <div className="mt-4">
                        <Text>{t('account.directory_none')}</Text>
                        <Button
                            href="/account/directory"
                            className="mt-4 w-full sm:w-auto"
                        >
                            {t('account.directory_create')}
                        </Button>
                    </div>
                )}
                {directory && !directory.is_published && (
                    <Text className="mt-3">
                        {t('account.directory_pending')}
                    </Text>
                )}
            </div>

            <div className="border-line mt-12 border-t pt-10">
                <Heading level={2}>{t('account.talks')}</Heading>
                <Text className="mt-3">{t('account.talks_lead')}</Text>
                {proposals.length === 0 ? (
                    <Text className="mt-3">{t('account.no_talks')}</Text>
                ) : (
                    <ul className="mt-4 space-y-4">
                        {proposals.map((proposal) => (
                            <li key={proposal.id}>
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="font-medium">
                                        {proposal.title}
                                    </p>
                                    <Chip>{proposal.status_label}</Chip>
                                </div>
                                <Text>
                                    {proposal.kind_label}
                                    {proposal.event && (
                                        <>
                                            {' · '}
                                            <a
                                                href={`/events/${proposal.event.slug}`}
                                                className="font-medium underline"
                                            >
                                                {proposal.event.title}
                                            </a>
                                        </>
                                    )}
                                </Text>
                            </li>
                        ))}
                    </ul>
                )}
                <div className={`${actionRowClass} mt-4`}>
                    <Button href="/events" variant="outline">
                        {t('cfp.submit')}
                    </Button>
                </div>
            </div>

            <div className="border-line mt-12 border-t pt-10">
                <Heading level={2}>{t('account.events')}</Heading>
                {registrations.length === 0 ? (
                    <Text className="mt-3">{t('account.no_events')}</Text>
                ) : (
                    <ul className="mt-4 space-y-3">
                        {registrations.map((registration) => (
                            <li key={registration.event.slug}>
                                <a
                                    href={`/events/${registration.event.slug}`}
                                    className="font-medium underline"
                                >
                                    {registration.event.title}
                                </a>
                                <Text>
                                    {registration.event.starts_at} ·{' '}
                                    {registration.status_label}
                                </Text>
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <div className="mt-12">
                <ManagePasskeys
                    canManagePasskeys={canManagePasskeys}
                    passkeys={passkeys}
                />
            </div>
        </div>
    );
}
