import { useEffect, useState } from 'react';
import { useTrans } from '@/lib/i18n';

export function ShortUrl({ url }: { url: string }) {
    const t = useTrans();
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        if (!copied) {
            return;
        }

        const timer = setTimeout(() => setCopied(false), 2000);

        return () => clearTimeout(timer);
    }, [copied]);

    return (
        <span className="flex min-w-0 flex-wrap items-center gap-3">
            <a
                href={url}
                className="text-brand-red font-mono break-all underline"
            >
                {url.replace(/^https?:\/\//, '')}
            </a>
            <button
                type="button"
                className="border-line text-ink hover:text-brand-red focus-visible:outline-brand-red border px-2.5 py-1 text-xs font-medium transition-colors focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2"
                onClick={() =>
                    void navigator.clipboard
                        .writeText(url)
                        .then(() => setCopied(true))
                }
            >
                <span aria-live="polite">
                    {copied ? t('events.copied') : t('events.copy')}
                </span>
            </button>
        </span>
    );
}
