import { Seo } from '@/components/seo';
import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Text } from '@/components/catalyst/text';
import { Button } from '@/components/design';
import { FieldError } from '@/components/field-error';
import { ValidatedForm } from '@/components/validated-form';
import PasskeyVerify from '@/components/passkey-verify';
import { useTrans } from '@/lib/i18n';

type Props = {
    status?: string;
};

export default function Login({ status }: Props) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('auth.login.title')}
                description={t('auth.login.title')}
                noindex
            />
            <div className="space-y-2">
                <h1 className="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                    {t('auth.login.title')}
                </h1>
                <p className="text-base/6 text-zinc-500 sm:text-sm/6">
                    {t('auth.login.description')}
                </p>
            </div>
            <PasskeyVerify
                label={t('auth.passkey')}
                loadingLabel={t('auth.passkey_loading')}
                separator={t('auth.or_email')}
            />
            <ValidatedForm
                action="/login/email"
                method="post"
                className="grid grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <Field>
                            <Label required>{t('auth.email')}</Label>
                            <Input
                                type="email"
                                name="email"
                                required
                                autoFocus
                                autoComplete="email webauthn"
                            />
                            <FieldError error={errors.email} />
                        </Field>
                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            {t('auth.send_code')}
                        </Button>
                    </>
                )}
            </ValidatedForm>
            {status && (
                <Text className="text-brand-green text-center">{status}</Text>
            )}
        </>
    );
}

Login.layout = {
    title: undefined,
    description: undefined,
};
