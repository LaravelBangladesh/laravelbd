import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import {
    AdminEmptyState,
    AdminPageHeader,
} from '@/components/admin-page-header';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/catalyst/table';
import { ConfirmButton } from '@/components/confirm-button';
import { Button, Chip } from '@/components/design';
import { type FieldOption } from '@/components/field-select';
import { ListToolbar } from '@/components/list-toolbar';
import { Pagination, type Paginated } from '@/components/pagination';
import { Seo } from '@/components/seo';
import { StatusChip } from '@/components/status-chip';
import { useTrans } from '@/lib/i18n';

type Attendee = {
    id: string;
    name: string | null;
    email: string | null;
    mobile_number: string | null;
    user_active: boolean;
    status: string;
    status_label: string;
    registered_at: string | null;
    answers: { question_id: string; label: string; value: string }[];
};

export default function AdminEventAttendees({
    event,
    attendees,
    filters,
    statuses,
}: {
    event: { id: string; title_en: string };
    attendees: Paginated<Attendee>;
    filters: { q: string; status: string };
    statuses: FieldOption[];
}) {
    const t = useTrans();
    const path = `/admin/events/${event.id}/attendees`;
    const filtered = filters.q !== '' || filters.status !== '';
    const registrationPath = (attendee: Attendee) =>
        `/admin/events/${event.id}/registrations/${attendee.id}`;

    return (
        <>
            <Seo
                title={`${t('admin.events_attendees')} · ${event.title_en}`}
                description={t('admin.attendees_lead')}
                noindex
            />
            <AdminPageHeader
                eyebrow={event.title_en}
                title={t('admin.events_attendees')}
                description={t('admin.attendees_lead')}
                actions={
                    <Button
                        href={`/admin/events/${event.id}`}
                        variant="outline"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.events_manage')}
                    </Button>
                }
            />
            <ListToolbar
                path={path}
                exportPath={`${path}/export`}
                values={filters}
                searchLabel={t('admin.search_people')}
                filters={[
                    {
                        name: 'status',
                        label: t('admin.status'),
                        options: [
                            {
                                value: '',
                                label: t('admin.filter_all_statuses'),
                            },
                            ...statuses,
                        ],
                    },
                ]}
            />
            {attendees.data.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.events_attendees')}
                        description={t(
                            filtered
                                ? 'admin.no_matches'
                                : 'admin.no_attendees',
                        )}
                    />
                </div>
            ) : (
                <div className="mt-8 overflow-x-auto">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>{t('auth.name')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('auth.email')}
                                </TableHeader>
                                <TableHeader className="hidden lg:table-cell">
                                    {t('admin.mobile_number')}
                                </TableHeader>
                                <TableHeader className="hidden lg:table-cell">
                                    {t('admin.registered_at')}
                                </TableHeader>
                                <TableHeader />
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {attendees.data.map((attendee) => (
                                <TableRow key={attendee.id}>
                                    <TableCell className="font-medium">
                                        <span className="flex items-center gap-2">
                                            {attendee.name}
                                            <StatusChip
                                                status={attendee.status}
                                                label={attendee.status_label}
                                            />
                                            {!attendee.user_active && (
                                                <Chip tone="red">
                                                    {t('admin.inactive')}
                                                </Chip>
                                            )}
                                        </span>
                                        {attendee.answers.length > 0 && (
                                            <span className="text-ink-muted mt-1 block text-xs font-normal">
                                                {attendee.answers.map(
                                                    (answer) => (
                                                        <span
                                                            key={
                                                                answer.question_id
                                                            }
                                                            className="block"
                                                        >
                                                            {answer.label}:{' '}
                                                            {answer.value}
                                                        </span>
                                                    ),
                                                )}
                                            </span>
                                        )}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {attendee.email}
                                    </TableCell>
                                    <TableCell className="hidden lg:table-cell">
                                        {attendee.mobile_number}
                                    </TableCell>
                                    <TableCell className="hidden lg:table-cell">
                                        {attendee.registered_at}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex justify-end">
                                            {attendee.status !== 'cancelled' ? (
                                                <ConfirmButton
                                                    label={t(
                                                        'admin.unregister',
                                                    )}
                                                    title={t(
                                                        'admin.unregister_title',
                                                        {
                                                            name:
                                                                attendee.name ??
                                                                '',
                                                        },
                                                    )}
                                                    body={t(
                                                        'admin.unregister_body',
                                                    )}
                                                    onConfirm={() =>
                                                        router.delete(
                                                            registrationPath(
                                                                attendee,
                                                            ),
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                />
                                            ) : (
                                                attendee.user_active && (
                                                    <Button
                                                        variant="ghost"
                                                        type="button"
                                                        onClick={() =>
                                                            router.patch(
                                                                `${registrationPath(attendee)}/restore`,
                                                                {},
                                                                {
                                                                    preserveScroll: true,
                                                                    onError: (
                                                                        errors,
                                                                    ) => {
                                                                        toast.error(
                                                                            Object.values(
                                                                                errors,
                                                                            ).join(
                                                                                ' ',
                                                                            ),
                                                                        );
                                                                    },
                                                                },
                                                            )
                                                        }
                                                    >
                                                        {t('admin.reregister')}
                                                    </Button>
                                                )
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            )}
            <Pagination paginator={attendees} />
        </>
    );
}
