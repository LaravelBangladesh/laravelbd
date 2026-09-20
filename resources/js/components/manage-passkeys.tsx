import { router } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
import { Subheading } from '@/components/catalyst/heading';
import { Text } from '@/components/catalyst/text';
import PasskeyItem from '@/components/passkey-item';
import PasskeyRegistration from '@/components/passkey-register';
import { useTrans } from '@/lib/i18n';
import type { Passkey } from '@/types/auth';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

const EmptyState = () => {
    const t = useTrans();

    return (
        <div className="p-8 text-center">
            <div className="border-line text-ink mx-auto mb-4 flex size-14 items-center justify-center border">
                <KeyRound className="size-7" />
            </div>
            <p className="font-medium">{t('account.passkeys_empty')}</p>
            <p className="text-ink-muted mt-1 text-sm">
                {t('account.passkeys_empty_help')}
            </p>
        </div>
    );
};

export default function ManagePasskeys(props: Props) {
    const t = useTrans();
    const passkeys = props.passkeys ?? [];

    const handleDelete = (id: number, onError: () => void) => {
        router.delete(destroy.url(id), {
            preserveScroll: true,
            onError,
        });
    };

    const handleRegisterSuccess = () => {
        router.reload();
    };

    if (!(props.canManagePasskeys ?? false)) {
        return null;
    }

    return (
        <div className="space-y-6">
            <div>
                <Subheading>{t('account.passkeys')}</Subheading>
                <Text className="mt-2">{t('account.passkeys_help')}</Text>
            </div>

            <div className="border-line overflow-hidden border">
                {passkeys.length > 0 ? (
                    passkeys.map((passkey) => (
                        <PasskeyItem
                            key={passkey.id}
                            passkey={passkey}
                            onDelete={handleDelete}
                        />
                    ))
                ) : (
                    <EmptyState />
                )}
            </div>

            <PasskeyRegistration onSuccess={handleRegisterSuccess} />
        </div>
    );
}
