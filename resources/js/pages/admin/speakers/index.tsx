import { Seo } from '@/components/seo';
import {
    AdminEmptyState,
    AdminPageHeader,
} from '@/components/admin-page-header';
import { Button } from '@/components/design';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/catalyst/table';
import { useTrans } from '@/lib/i18n';

type SpeakerRow = {
    id: string;
    name: string;
    title: string | null;
    company: string | null;
};

export default function AdminSpeakersIndex({
    speakers,
}: {
    speakers: SpeakerRow[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.speakers_title')}
                description={t('admin.speakers_title')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('nav.admin')}
                title={t('admin.speakers_title')}
                description={t('admin.speakers_lead')}
                actions={
                    <Button
                        href="/admin/speakers/create"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.speakers_create')}
                    </Button>
                }
            />
            {speakers.length === 0 ? (
                <div className="mt-8">
                    <AdminEmptyState
                        label={t('admin.speakers_title')}
                        description={t('admin.no_speaker_records')}
                        actionHref="/admin/speakers/create"
                        actionLabel={t('admin.speakers_create')}
                    />
                </div>
            ) : (
                <div className="mt-8 overflow-x-auto">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>{t('auth.name')}</TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('admin.speaker_title')}
                                </TableHeader>
                                <TableHeader className="hidden md:table-cell">
                                    {t('admin.company')}
                                </TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {speakers.map((speaker) => (
                                <TableRow
                                    key={speaker.id}
                                    href={`/admin/speakers/${speaker.id}/edit`}
                                    className="hover:bg-canvas"
                                >
                                    <TableCell className="font-medium">
                                        {speaker.name}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {speaker.title}
                                    </TableCell>
                                    <TableCell className="hidden md:table-cell">
                                        {speaker.company}
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
