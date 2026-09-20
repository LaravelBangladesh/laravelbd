import { Seo } from '@/components/seo';
import { router, usePage } from '@inertiajs/react';
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
import { FieldSelect } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';

type UserRow = {
    id: string;
    name: string;
    email: string;
    role: string;
    locale: string;
    created_at: string | null;
};

type Props = {
    users: UserRow[];
    roles: { value: string; label: string }[];
};

export default function AdminUsers({ users, roles }: Props) {
    const t = useTrans();
    const { auth } = usePage().props;

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
            {users.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.users_title')}
                        description={t('admin.no_users')}
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
                                <TableHeader>{t('admin.role')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {users.map((user) => (
                                <TableRow
                                    key={user.id}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        {user.name}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {user.email}
                                    </TableCell>
                                    <TableCell>
                                        {auth.user?.is_admin ? (
                                            <FieldSelect
                                                defaultValue={user.role}
                                                options={roles}
                                                onChange={(role) => {
                                                    router.patch(
                                                        `/admin/users/${user.id}/role`,
                                                        { role },
                                                    );
                                                }}
                                            />
                                        ) : (
                                            t(`roles.${user.role}`)
                                        )}
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
