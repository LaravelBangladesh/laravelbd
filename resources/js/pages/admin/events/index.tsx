import { Seo } from '@/components/seo';
import {
    AdminEmptyState,
    AdminPageHeader,
} from '@/components/admin-page-header';
import { Button } from '@/components/design';
import { StatusChip } from '@/components/status-chip';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/catalyst/table';
import { useTrans } from '@/lib/i18n';

type Row = {
    id: string;
    slug: string;
    title: string;
    type_label: string;
    status: string;
    starts_at: string | null;
};

export default function AdminEventsIndex({ events }: { events: Row[] }) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.events_title')}
                description={t('admin.events_title')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.events_title')}
                description={t('admin.events_lead')}
                actions={
                    <Button
                        href="/admin/events/create"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.events_create')}
                    </Button>
                }
            />
            {events.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.events_title')}
                        description={t('admin.no_events')}
                        actionHref="/admin/events/create"
                        actionLabel={t('admin.events_create')}
                    />
                </div>
            ) : (
                <div className="mt-8 overflow-x-auto">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>{t('admin.title_en')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('admin.event_type')}
                                </TableHeader>
                                <TableHeader>{t('admin.status')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('admin.starts_at')}
                                </TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {events.map((event) => (
                                <TableRow
                                    key={event.id}
                                    href={`/admin/events/${event.id}`}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        {event.title}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {event.type_label}
                                    </TableCell>
                                    <TableCell>
                                        <StatusChip
                                            status={event.status}
                                            label={t(
                                                `events.status.${event.status}`,
                                            )}
                                        />
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {event.starts_at}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            )}
        </>
    );
}
