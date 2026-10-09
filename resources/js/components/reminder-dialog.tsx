import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import {
    Dialog,
    DialogActions,
    DialogBody,
    DialogDescription,
    DialogTitle,
} from '@/components/catalyst/dialog';
import { Button } from '@/components/design';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

/**
 * Shows the reminder email as attendees get it, then queues it for every
 * registered attendee who has not had it yet. The admin pages refuse to be
 * framed, so the email HTML is fetched and rendered into a sandboxed srcdoc.
 */
export function ReminderDialog({
    eventId,
    recipients,
}: {
    eventId: string;
    recipients: number;
}) {
    const t = useTrans();
    const { locale: current, locales } = usePage().props;
    const [open, setOpen] = useState(false);
    const [locale, setLocale] = useState(current);
    const [html, setHtml] = useState<string | null>(null);
    const [failed, setFailed] = useState(false);
    const path = `/admin/events/${eventId}/reminders`;

    useEffect(() => {
        if (!open) {
            return;
        }

        let active = true;
        setHtml(null);
        setFailed(false);

        fetch(`${path}/preview?locale=${locale}`, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html' },
        })
            .then((response) =>
                response.ok ? response.text() : Promise.reject(),
            )
            .then((body) => active && setHtml(body))
            .catch(() => active && setFailed(true));

        return () => {
            active = false;
        };
    }, [open, locale, path]);

    const send = () => {
        setOpen(false);
        router.post(
            path,
            {},
            {
                preserveScroll: true,
                onError: (errors) =>
                    toast.error(Object.values(errors).join(' ')),
            },
        );
    };

    return (
        <>
            <Button
                type="button"
                onClick={() => setOpen(true)}
                className="w-full sm:w-auto"
            >
                {t('admin.send_reminder')}
            </Button>
            <Dialog open={open} onClose={() => setOpen(false)} size="3xl">
                <DialogTitle>{t('admin.reminder_preview_title')}</DialogTitle>
                <DialogDescription>
                    {t('admin.reminder_preview_lead')}
                </DialogDescription>
                <DialogBody>
                    <div
                        role="group"
                        aria-label={t('admin.preview_language')}
                        className="flex gap-2"
                    >
                        {Object.entries(locales).map(([value, label]) => (
                            <button
                                key={value}
                                type="button"
                                aria-pressed={locale === value}
                                onClick={() => setLocale(value)}
                                className={cn(
                                    'inline-flex h-9 items-center rounded-none border px-3.5 text-sm font-medium transition-colors',
                                    locale === value
                                        ? 'border-brand-green bg-brand-green text-white'
                                        : 'border-line bg-paper text-ink-muted hover:border-line-strong hover:text-ink',
                                )}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                    <div className="border-line bg-canvas mt-4 h-[60vh] border">
                        {html !== null ? (
                            <iframe
                                title={t('admin.reminder_preview_title')}
                                sandbox=""
                                srcDoc={html}
                                className="size-full"
                            />
                        ) : (
                            <p className="text-ink-muted p-6 text-sm">
                                {t(
                                    failed
                                        ? 'admin.reminder_load_failed'
                                        : 'admin.reminder_loading',
                                )}
                            </p>
                        )}
                    </div>
                    <p className="text-ink mt-4 text-sm">
                        {recipients > 0
                            ? t('admin.reminder_recipients', {
                                  count: String(recipients),
                              })
                            : t('admin.reminder_none')}
                    </p>
                </DialogBody>
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
                        disabled={recipients === 0}
                        onClick={send}
                    >
                        {t('admin.reminder_send', {
                            count: String(recipients),
                        })}
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
}
