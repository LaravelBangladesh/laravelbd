import { Seo } from '@/components/seo';
import {
    AdminEmptyState,
    AdminPageHeader,
} from '@/components/admin-page-header';
import { FilterPills } from '@/components/design';
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

type ProposalRow = {
    id: string;
    title: string;
    kind_label: string;
    status: string;
    status_label: string;
    submitter: string | null;
    event: { slug: string; title: string } | null;
};

export default function AdminProposalsIndex({
    proposals,
    event,
    events,
}: {
    proposals: ProposalRow[];
    event: string | null;
    events: { value: string; label: string }[];
}) {
    const t = useTrans();

    const pills = [
        {
            href: '/admin/proposals',
            label: t('admin.all_events'),
            current: !event,
        },
        ...events.map((option) => ({
            href: `/admin/proposals?event=${option.value}`,
            label: option.label,
            current: event === option.value,
        })),
    ];

    return (
        <>
            <Seo
                title={t('admin.proposals_title')}
                description={t('admin.proposals_title')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.proposals_title')}
                description={t('admin.proposals_lead')}
            />
            {events.length > 0 && (
                <FilterPills items={pills} className="mt-6" />
            )}
            {proposals.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.proposals_title')}
                        description={t('admin.no_proposals')}
                        actionHref="/events"
                        actionLabel={t('nav.events')}
                    />
                </div>
            ) : (
                <div className="mt-8 overflow-x-auto">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>{t('admin.title_en')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('cfp.kind')}
                                </TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('cfp.event')}
                                </TableHeader>
                                <TableHeader>{t('cfp.status')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('auth.name')}
                                </TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {proposals.map((proposal) => (
                                <TableRow
                                    key={proposal.id}
                                    href={`/admin/proposals/${proposal.id}`}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        {proposal.title}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {proposal.kind_label}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {proposal.event?.title}
                                    </TableCell>
                                    <TableCell>
                                        <StatusChip
                                            status={proposal.status}
                                            label={proposal.status_label}
                                        />
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {proposal.submitter}
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
