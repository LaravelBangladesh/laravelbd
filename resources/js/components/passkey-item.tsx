import { KeyRound } from 'lucide-react';
import { useState } from 'react';
import {
    Dialog,
    DialogActions,
    DialogDescription,
    DialogTitle,
} from '@/components/catalyst/dialog';
import { Button } from '@/components/design';
import { useTrans } from '@/lib/i18n';
import type { Passkey } from '@/types/auth';

type Props = {
    passkey: Passkey;
    onDelete: (id: number, onError: () => void) => void;
};

export default function PasskeyItem({ passkey, onDelete }: Props) {
    const t = useTrans();
    const [open, setOpen] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        setIsDeleting(true);
        onDelete(passkey.id, () => setIsDeleting(false));
    };

    return (
        <div className="border-line flex flex-col gap-3 border-b p-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex min-w-0 items-center gap-4">
                <div className="border-line text-ink flex size-10 shrink-0 items-center justify-center border">
                    <KeyRound className="size-5" />
                </div>
                <div className="min-w-0 space-y-1">
                    <div className="flex flex-wrap items-center gap-2.5">
                        <p className="font-medium tracking-tight">
                            {passkey.name}
                        </p>
                        {passkey.authenticator && (
                            <span className="border-line text-ink-muted inline-flex items-center border px-2 py-0.5 text-[11px] font-bold tracking-[0.12em] uppercase">
                                {passkey.authenticator}
                            </span>
                        )}
                    </div>
                    <p className="text-ink-muted text-sm">
                        {t('account.passkeys_added', {
                            when: passkey.created_at_diff,
                        })}
                        {passkey.last_used_at_diff && (
                            <>
                                <span className="mx-1 opacity-50">/</span>
                                {t('account.passkeys_last_used', {
                                    when: passkey.last_used_at_diff,
                                })}
                            </>
                        )}
                    </p>
                </div>
            </div>

            <Button
                variant="ghost"
                type="button"
                className="self-start"
                onClick={() => setOpen(true)}
            >
                {t('account.passkeys_remove')}
            </Button>
            <Dialog open={open} onClose={() => setOpen(false)} size="sm">
                <DialogTitle>{t('account.passkeys_remove_title')}</DialogTitle>
                <DialogDescription>
                    {t('account.passkeys_remove_body', { name: passkey.name })}
                </DialogDescription>
                <DialogActions>
                    <Button
                        variant="outline"
                        type="button"
                        onClick={() => setOpen(false)}
                    >
                        {t('image.cancel')}
                    </Button>
                    <Button
                        type="button"
                        onClick={handleDelete}
                        disabled={isDeleting}
                    >
                        {isDeleting
                            ? t('account.passkeys_removing')
                            : t('account.passkeys_remove_title')}
                    </Button>
                </DialogActions>
            </Dialog>
        </div>
    );
}
