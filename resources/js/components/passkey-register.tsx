import { usePasskeyRegister } from '@laravel/passkeys/react';
import { useState } from 'react';
import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Button } from '@/components/design';
import { FieldError } from '@/components/field-error';
import InputError from '@/components/input-error';
import { useTrans } from '@/lib/i18n';

type Props = {
    onSuccess: () => void;
};

export default function PasskeyRegistration({ onSuccess }: Props) {
    const t = useTrans();
    const [name, setName] = useState(() => {
        const ua = navigator.userAgent;

        const browser = [
            { pattern: /Edg|Edge/, name: 'Edge' },
            { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
            { pattern: /Firefox|FxiOS/, name: 'Firefox' },
            { pattern: /Chrome|CriOS/, name: 'Chrome' },
            { pattern: /Safari/, name: 'Safari' },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        const os = [
            { pattern: /iPhone/, name: 'iPhone' },
            { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
            { pattern: /Android/, name: 'Android' },
            { pattern: /Mac/, name: 'Mac' },
            { pattern: /Windows/, name: 'Windows' },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        return [browser, os].filter(Boolean).join(' on ') || '';
    });

    const [showForm, setShowForm] = useState(false);
    const { register, isLoading, error, isSupported } = usePasskeyRegister({
        onSuccess: () => {
            setName('');
            setShowForm(false);
            onSuccess();
        },
    });

    const [nameError, setNameError] = useState<string | null>(null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!name.trim()) {
            setNameError(t('validation.required'));

            return;
        }

        setNameError(null);
        await register(name);
    };

    const handleCancel = () => {
        setShowForm(false);
        setName('');
    };

    if (!isSupported) {
        return (
            <p className="text-ink-muted text-sm">
                {t('account.passkeys_unsupported')}
            </p>
        );
    }

    if (!showForm) {
        return (
            <Button
                variant="outline"
                className="w-full sm:w-auto"
                onClick={() => setShowForm(true)}
            >
                {t('account.passkeys_add')}
            </Button>
        );
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="border-line grid gap-6 border p-5"
        >
            <Field>
                <Label htmlFor="passkey-name" required>
                    {t('account.passkeys_name')}
                </Label>
                <Input
                    id="passkey-name"
                    type="text"
                    value={name}
                    required
                    onChange={(e) => {
                        setName(e.target.value);
                        if (nameError && e.target.value.trim() !== '') {
                            setNameError(null);
                        }
                    }}
                    placeholder={t('account.passkeys_name_placeholder')}
                    autoFocus
                />
                <FieldError error={nameError ?? undefined} />
                <p className="text-ink-muted mt-2 text-sm">
                    {t('account.passkeys_name_help')}
                </p>
            </Field>

            {error && <InputError message={error} />}

            <div className="flex flex-col gap-3 sm:flex-row">
                <Button
                    type="submit"
                    className="w-full sm:w-auto"
                    disabled={isLoading || !name.trim()}
                >
                    {isLoading
                        ? t('account.passkeys_registering')
                        : t('account.passkeys_register')}
                </Button>
                <Button type="button" variant="ghost" onClick={handleCancel}>
                    {t('image.cancel')}
                </Button>
            </div>
        </form>
    );
}
