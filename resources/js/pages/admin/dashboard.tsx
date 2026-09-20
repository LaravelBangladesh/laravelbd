import { Seo } from '@/components/seo';
import {
    AdminEmptyState,
    AdminPageHeader,
} from '@/components/admin-page-header';
import { Link } from '@/components/catalyst/link';
import { Button, Eyebrow, Surface } from '@/components/design';
import { StatusChip } from '@/components/status-chip';
import { useTrans } from '@/lib/i18n';

type Props = {
    stats: {
        upcoming_events: number;
        pending_proposals: number;
        draft_listings: number;
        next_event_registrations: number;
        members: number;
        staff: number;
    };
    nextEvent: {
        id: string;
        title: string;
        starts_at: string | null;
        venue_name: string | null;
        capacity: number | null;
        registered_count: number;
    } | null;
    recentProposals: {
        id: string;
        title: string;
        status: string;
        status_label: string;
        submitter: string | null;
    }[];
};

export default function AdminDashboard({
    stats,
    nextEvent,
    recentProposals,
}: Props) {
    const t = useTrans();

    const tiles = [
        {
            label: t('admin.stat.upcoming_events'),
            value: stats.upcoming_events,
        },
        {
            label: t('admin.stat.pending_proposals'),
            value: stats.pending_proposals,
        },
        { label: t('admin.stat.draft_listings'), value: stats.draft_listings },
        {
            label: t('admin.stat.next_registrations'),
            value: stats.next_event_registrations,
        },
        { label: t('admin.stat.members'), value: stats.members },
        { label: t('admin.stat.staff'), value: stats.staff },
    ];

    return (
        <>
            <Seo
                title={t('admin.dashboard')}
                description={t('admin.dashboard')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.dashboard')}
                description={t('admin.dashboard_lead')}
            />

            <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {tiles.map((tile) => (
                    <Surface key={tile.label} className="p-5">
                        <p className="text-ink-muted text-sm">{tile.label}</p>
                        <p className="text-ink mt-2 text-3xl font-semibold tracking-tight tabular-nums">
                            {tile.value}
                        </p>
                    </Surface>
                ))}
            </div>

            <div className="mt-8 grid gap-6 lg:grid-cols-2">
                <Surface className="p-5 sm:p-6">
                    <Eyebrow>{t('admin.next_event')}</Eyebrow>
                    {nextEvent ? (
                        <div className="mt-4">
                            <p className="text-ink text-lg font-medium tracking-tight">
                                {nextEvent.title}
                            </p>
                            <p className="text-ink-muted mt-1 text-sm">
                                {nextEvent.starts_at}
                                {nextEvent.venue_name
                                    ? ` · ${nextEvent.venue_name}`
                                    : ''}
                            </p>
                            <p className="text-ink-muted mt-3 text-sm tabular-nums">
                                {t('events.registered_count', {
                                    count: String(nextEvent.registered_count),
                                })}
                                {nextEvent.capacity
                                    ? ` / ${nextEvent.capacity}`
                                    : ''}
                            </p>
                            <div className="mt-5">
                                <Button
                                    href={`/admin/events/${nextEvent.id}`}
                                    variant="outline"
                                >
                                    {t('admin.events_manage')}
                                </Button>
                            </div>
                        </div>
                    ) : (
                        <div className="mt-4">
                            <AdminEmptyState
                                label={t('admin.next_event')}
                                description={t('admin.no_upcoming_events')}
                                actionHref="/admin/events/create"
                                actionLabel={t('admin.events_create')}
                            />
                        </div>
                    )}
                </Surface>

                <Surface className="p-5 sm:p-6">
                    <Eyebrow>{t('admin.recent_proposals')}</Eyebrow>
                    {recentProposals.length > 0 ? (
                        <ul className="divide-line mt-4 divide-y">
                            {recentProposals.map((proposal) => (
                                <li key={proposal.id} className="py-3">
                                    <Link
                                        href={`/admin/proposals/${proposal.id}`}
                                        className="hover:text-brand-red flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <span className="min-w-0">
                                            <span className="text-ink block truncate text-sm font-medium">
                                                {proposal.title}
                                            </span>
                                            {proposal.submitter && (
                                                <span className="text-ink-muted block truncate text-xs">
                                                    {proposal.submitter}
                                                </span>
                                            )}
                                        </span>
                                        <StatusChip
                                            status={proposal.status}
                                            label={proposal.status_label}
                                        />
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <div className="mt-4">
                            <AdminEmptyState
                                label={t('admin.recent_proposals')}
                                description={t('admin.no_proposals')}
                            />
                        </div>
                    )}
                </Surface>
            </div>
        </>
    );
}
