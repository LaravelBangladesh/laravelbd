import { Link } from '@/components/catalyst/link';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
};

const itemClass =
    'inline-flex h-9 min-w-9 items-center justify-center border px-3 text-sm tabular-nums';

function PageLink({
    url,
    active = false,
    children,
}: {
    url: string | null;
    active?: boolean;
    children: React.ReactNode;
}) {
    if (url === null) {
        return (
            <span className={cn(itemClass, 'border-line text-ink-muted')}>
                {children}
            </span>
        );
    }

    return (
        <Link
            href={url}
            aria-current={active ? 'page' : undefined}
            className={cn(
                itemClass,
                active
                    ? 'border-brand-green bg-brand-green text-white'
                    : 'border-line bg-paper text-ink hover:bg-canvas',
            )}
        >
            {children}
        </Link>
    );
}

/**
 * Page links for a Laravel length-aware paginator. The first and last entries
 * of `links` are Laravel's own previous and next labels, so they are swapped
 * for translated ones.
 */
export function Pagination({
    paginator,
}: {
    paginator: Omit<Paginated<unknown>, 'data'>;
}) {
    const t = useTrans();

    if (paginator.from === null || paginator.to === null) {
        return null;
    }

    return (
        <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-ink-muted text-sm">
                {t('admin.pagination_summary', {
                    from: String(paginator.from),
                    to: String(paginator.to),
                    total: String(paginator.total),
                })}
            </p>
            {paginator.last_page > 1 && (
                <nav
                    aria-label={t('admin.pagination')}
                    className="flex flex-wrap gap-1"
                >
                    <PageLink url={paginator.prev_page_url}>
                        {t('admin.previous')}
                    </PageLink>
                    {paginator.links.slice(1, -1).map((link, index) => (
                        <PageLink
                            key={`${link.label}-${index}`}
                            url={link.url}
                            active={link.active}
                        >
                            {link.label}
                        </PageLink>
                    ))}
                    <PageLink url={paginator.next_page_url}>
                        {t('admin.next')}
                    </PageLink>
                </nav>
            )}
        </div>
    );
}
