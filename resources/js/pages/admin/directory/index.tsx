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

type ListingRow = {
    id: string;
    name: string;
    kind_label: string;
    city: string | null;
    status: string;
    status_label: string;
};

export default function AdminDirectoryIndex({
    listings,
}: {
    listings: ListingRow[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.directory_title')}
                description={t('admin.directory_title')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.directory_title')}
                description={t('admin.directory_lead')}
                actions={
                    <Button
                        href="/admin/directory/create"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.directory_create')}
                    </Button>
                }
            />
            {listings.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.directory_title')}
                        description={t('admin.no_listings')}
                        actionHref="/admin/directory/create"
                        actionLabel={t('admin.directory_create')}
                    />
                </div>
            ) : (
                <div className="mt-8 overflow-x-auto">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>{t('auth.name')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('directory.kind')}
                                </TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('directory.city')}
                                </TableHeader>
                                <TableHeader>{t('admin.status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {listings.map((listing) => (
                                <TableRow
                                    key={listing.id}
                                    href={`/admin/directory/${listing.id}/edit`}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        {listing.name}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {listing.kind_label}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {listing.city}
                                    </TableCell>
                                    <TableCell>
                                        <StatusChip
                                            status={listing.status}
                                            label={listing.status_label}
                                        />
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
