import {
    AdminEmptyState,
    AdminPageHeader,
    AdminSection,
} from '@/components/admin-page-header';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/catalyst/table';
import { Button } from '@/components/design';
import { ProfileAvatar } from '@/components/profile-avatar';
import { Seo } from '@/components/seo';
import { StatusChip } from '@/components/status-chip';
import { useTrans } from '@/lib/i18n';

type Profile = {
    id: string;
    name: string;
    email: string;
    mobile_number: string | null;
    photo_url: string;
    role_label: string;
    directory_status: string;
    directory_status_label: string;
    joined_at: string | null;
    is_active: boolean;
};

type Registration = {
    id: string;
    event_id: string;
    event_title: string;
    status: string;
    status_label: string;
    registered_at: string | null;
};

type Proposal = {
    id: string;
    title: string;
    status: string;
    status_label: string;
    event: { title: string } | null;
};

export default function AdminUserShow({
    user,
    registrations,
    proposals,
}: {
    user: Profile;
    registrations: Registration[];
    proposals: Proposal[];
}) {
    const t = useTrans();
    const details = [
        [t('auth.email'), user.email],
        [t('admin.mobile_number'), user.mobile_number],
        [t('admin.role'), user.role_label],
        [t('admin.joined'), user.joined_at],
    ];

    return (
        <>
            <Seo title={user.name} description={t('admin.user_view')} noindex />
            <AdminPageHeader
                eyebrow={t('admin.user_view')}
                title={user.name}
                actions={
                    user.is_active ? (
                        <Button
                            href={`/admin/users/${user.id}/edit`}
                            className="w-full sm:w-auto"
                        >
                            {t('admin.edit')}
                        </Button>
                    ) : undefined
                }
            />
            {!user.is_active && (
                <p
                    role="status"
                    className="mt-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    {t('admin.user_deactivated_notice')}
                </p>
            )}
            <div className="mt-8 grid max-w-4xl grid-cols-1 gap-6">
                <AdminSection title={t('admin.users_title')}>
                    <div className="flex flex-col gap-5 sm:flex-row">
                        <ProfileAvatar
                            src={user.photo_url}
                            alt={user.name}
                            className="size-24"
                        />
                        <dl className="grid flex-1 grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                            {details.map(([label, value]) => (
                                <div key={label}>
                                    <dt className="text-ink-muted">{label}</dt>
                                    <dd className="text-ink mt-1 break-all">
                                        {value}
                                    </dd>
                                </div>
                            ))}
                            <div>
                                <dt className="text-ink-muted">
                                    {t('admin.directory_status')}
                                </dt>
                                <dd className="mt-1">
                                    <StatusChip
                                        status={user.directory_status}
                                        label={user.directory_status_label}
                                    />
                                </dd>
                            </div>
                        </dl>
                    </div>
                </AdminSection>

                <AdminSection title={t('admin.registrations')}>
                    {registrations.length === 0 ? (
                        <AdminEmptyState
                            label={t('admin.registrations')}
                            description={t('admin.no_registrations')}
                        />
                    ) : (
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableHeader>
                                        {t('admin.events')}
                                    </TableHeader>
                                    <TableHeader>
                                        {t('admin.status')}
                                    </TableHeader>
                                    <TableHeader className="hidden md:table-cell">
                                        {t('admin.registered_at')}
                                    </TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {registrations.map((registration) => (
                                    <TableRow key={registration.id}>
                                        <TableCell className="font-medium">
                                            <a
                                                href={`/admin/events/${registration.event_id}/attendees`}
                                                className="underline-offset-4 hover:underline"
                                            >
                                                {registration.event_title}
                                            </a>
                                        </TableCell>
                                        <TableCell>
                                            <StatusChip
                                                status={registration.status}
                                                label={
                                                    registration.status_label
                                                }
                                            />
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            {registration.registered_at}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </AdminSection>

                <AdminSection title={t('admin.proposals')}>
                    {proposals.length === 0 ? (
                        <AdminEmptyState
                            label={t('admin.proposals')}
                            description={t('admin.no_user_proposals')}
                        />
                    ) : (
                        <ul className="divide-line divide-y">
                            {proposals.map((proposal) => (
                                <li
                                    key={proposal.id}
                                    className="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="min-w-0">
                                        <a
                                            href={`/admin/proposals/${proposal.id}`}
                                            className="text-ink text-sm font-medium underline-offset-4 hover:underline"
                                        >
                                            {proposal.title}
                                        </a>
                                        {proposal.event && (
                                            <p className="text-ink-muted text-xs">
                                                {proposal.event.title}
                                            </p>
                                        )}
                                    </div>
                                    <StatusChip
                                        status={proposal.status}
                                        label={proposal.status_label}
                                    />
                                </li>
                            ))}
                        </ul>
                    )}
                </AdminSection>
            </div>
        </>
    );
}
