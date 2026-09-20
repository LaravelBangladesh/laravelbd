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

type ResourceRow = {
    id: string;
    slug: string;
    title: string;
    kind_label: string;
    status: string;
    status_label: string;
};

export default function AdminResourcesIndex({
    resources,
}: {
    resources: ResourceRow[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.resources_title')}
                description={t('admin.resources_title')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.resources_title')}
                description={t('admin.resources_lead')}
                actions={
                    <Button
                        href="/admin/resources/create"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.resources_create')}
                    </Button>
                }
            />
            {resources.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.resources_title')}
                        description={t('admin.no_resources')}
                        actionHref="/admin/resources/create"
                        actionLabel={t('admin.resources_create')}
                    />
                </div>
            ) : (
                <div className="mt-8 overflow-x-auto">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>{t('admin.title_en')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('resources.kind')}
                                </TableHeader>
                                <TableHeader>{t('admin.status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {resources.map((resource) => (
                                <TableRow
                                    key={resource.id}
                                    href={`/admin/resources/${resource.id}/edit`}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        {resource.title}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {resource.kind_label}
                                    </TableCell>
                                    <TableCell>
                                        <StatusChip
                                            status={resource.status}
                                            label={resource.status_label}
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
