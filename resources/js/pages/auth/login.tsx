import { Seo } from '@/components/seo';
import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Text } from '@/components/catalyst/text';
import { Button, Check } from '@/components/design';
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
            <h1 className="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                {t('auth.login.title')}
            </h1>
            <ul className="border-brand-green bg-brand-green/5 space-y-2 border-l-2 p-4 text-sm/6 text-zinc-700">
                {[
                    t('auth.login.info.1'),
                    t('auth.login.info.2'),
                    t('auth.login.info.3'),
                ].map((item) => (
                    <li key={item} className="flex gap-3">
                        <Check className="text-brand-green mt-1.5 shrink-0" />
                        <span>{item}</span>
                    </li>
                ))}
            </ul>
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
