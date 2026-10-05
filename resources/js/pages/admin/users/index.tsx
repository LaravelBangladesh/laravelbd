import { Seo } from '@/components/seo';
import { router } from '@inertiajs/react';
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
import { StatusChip } from '@/components/status-chip';
import { useTrans } from '@/lib/i18n';

type UserRow = {
    id: string;
    name: string;
    email: string;
    mobile_number: string | null;
    role: string;
    directory_status: string;
    directory_status_label: string;
    locale: string;
    created_at: string | null;
    is_active: boolean;
    can_deactivate: boolean;
    can_reactivate: boolean;
};

type Filters = {
    q: string;
    role: string;
    directory_status: string;
    speaker: string;
    status: string;
};

type Props = {
    users: Paginated<UserRow>;
    filters: Filters;
    roles: FieldOption[];
    visibilities: FieldOption[];
};

export default function AdminUsers({
    users,
    filters,
    roles,
    visibilities,
}: Props) {
    const t = useTrans();
    const filtered = Object.values(filters).some((value) => value !== '');

    return (
        <>
            <Seo
                title={t('admin.users_title')}
                description={t('admin.users_title')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.users_title')}
                description={t('admin.users_lead')}
            />
            <ListToolbar
                path="/admin/users"
                exportPath="/admin/users/export"
                values={filters}
                searchLabel={t('admin.search_people')}
                filters={[
                    {
                        name: 'role',
                        label: t('admin.role'),
                        options: [
                            { value: '', label: t('admin.filter_all_roles') },
                            ...roles,
                        ],
                    },
                    {
                        name: 'directory_status',
                        label: t('admin.directory_status'),
                        options: [
                            {
                                value: '',
                                label: t('admin.filter_all_directory'),
                            },
                            ...visibilities,
                        ],
                    },
                    {
                        name: 'status',
                        label: t('admin.status'),
                        options: [
                            {
                                value: '',
                                label: t('admin.filter_status_active'),
                            },
                            {
                                value: 'inactive',
                                label: t('admin.filter_status_inactive'),
                            },
                            {
                                value: 'all',
                                label: t('admin.filter_status_all'),
                            },
                        ],
                    },
                    {
                        name: 'speaker',
                        label: t('admin.speakers'),
                        options: [
                            { value: '', label: t('admin.filter_all_people') },
                            { value: 'yes', label: t('admin.filter_speakers') },
                            {
                                value: 'no',
                                label: t('admin.filter_non_speakers'),
                            },
                        ],
                    },
                ]}
            />
            {users.data.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.users_title')}
                        description={t(
                            filtered ? 'admin.no_matches' : 'admin.no_users',
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
                                <TableHeader className="hidden md:table-cell">
                                    {t('admin.directory_status')}
                                </TableHeader>
                                <TableHeader className="hidden sm:table-cell">
                                    {t('admin.role')}
                                </TableHeader>
                                <TableHeader />
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {users.data.map((user) => (
                                <TableRow
                                    key={user.id}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        <span className="flex items-center gap-2">
                                            {user.name}
                                            {!user.is_active && (
                                                <Chip tone="red">
                                                    {t('admin.inactive')}
                                                </Chip>
                                            )}
                                        </span>
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {user.email}
                                    </TableCell>
                                    <TableCell className="hidden lg:table-cell">
                                        {user.mobile_number}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        <StatusChip
                                            status={user.directory_status}
                                            label={user.directory_status_label}
                                        />
                                    </TableCell>
                                    <TableCell className="hidden sm:table-cell">
                                        {t(`roles.${user.role}`)}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex justify-end gap-4">
                                            <Button
                                                href={`/admin/users/${user.id}`}
                                                variant="ghost"
                                            >
                                                {t('admin.view')}
                                            </Button>
                                            {user.is_active && (
                                                <Button
                                                    href={`/admin/users/${user.id}/edit`}
                                                    variant="ghost"
                                                >
                                                    {t('admin.edit')}
                                                </Button>
                                            )}
                                            {user.can_deactivate && (
                                                <ConfirmButton
                                                    label={t(
                                                        'admin.deactivate',
                                                    )}
                                                    title={t(
                                                        'admin.deactivate_title',
                                                        { name: user.name },
                                                    )}
                                                    body={t(
                                                        'admin.deactivate_body',
                                                    )}
                                                    onConfirm={() =>
                                                        router.delete(
                                                            `/admin/users/${user.id}`,
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                />
                                            )}
                                            {user.can_reactivate && (
                                                <Button
                                                    variant="ghost"
                                                    type="button"
                                                    onClick={() =>
                                                        router.patch(
                                                            `/admin/users/${user.id}/restore`,
                                                            {},
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                >
                                                    {t('admin.reactivate')}
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            )}
            <Pagination paginator={users} />
        </>
    );
}
