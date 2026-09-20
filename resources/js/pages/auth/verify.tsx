import { Seo } from '@/components/seo';
import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Text } from '@/components/catalyst/text';
import { Button } from '@/components/design';
import { FieldError } from '@/components/field-error';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

type Props = {
    email: string;
    status?: string;
};

export default function Verify({ email, status }: Props) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('auth.verify.title')}
                description={t('auth.verify.title')}
                noindex
            />
            <div className="space-y-2">
                <h1 className="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                    {t('auth.verify.title')}
                </h1>
                <p className="text-base/6 text-zinc-500 sm:text-sm/6">
                    {t('auth.verify.description')}
                </p>
            </div>
            {status && <Text className="text-brand-green">{status}</Text>}
            <ValidatedForm
                action="/login/code"
                method="post"
                className="grid grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <input type="hidden" name="email" value={email} />
                        <Field>
                            <Label required>{t('auth.code')}</Label>
                            <Input
                                type="text"
                                name="code"
                                required
                                autoFocus
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
                            className="w-full"
                            disabled={processing}
                        >
                            {t('auth.verify_code')}
                        </Button>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
