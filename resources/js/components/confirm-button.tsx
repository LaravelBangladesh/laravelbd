import { useState } from 'react';
import {
    Dialog,
    DialogActions,
    DialogDescription,
    DialogTitle,
} from '@/components/catalyst/dialog';
import { Button } from '@/components/design';
import { useTrans } from '@/lib/i18n';

/**
 * A ghost button that asks before it acts, for changes staff should not
 * make by a stray click.
 */
export function ConfirmButton({
    label,
    title,
    body,
    onConfirm,
}: {
    label: string;
    title: string;
    body: string;
    onConfirm: () => void;
}) {
    const t = useTrans();
    const [open, setOpen] = useState(false);

    return (
        <>
            <Button variant="ghost" type="button" onClick={() => setOpen(true)}>
                {label}
            </Button>
            <Dialog open={open} onClose={() => setOpen(false)} size="sm">
                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{body}</DialogDescription>
                <DialogActions>
                    <Button
                        variant="outline"
                        type="button"
                        onClick={() => setOpen(false)}
                    >
                        {t('admin.cancel')}
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            setOpen(false);
                            onConfirm();
                        }}
                    >
                        {label}
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
}
