import { Seo } from '@/components/seo';
import { AdminPageHeader } from '@/components/admin-page-header';
import { actionRowClass, Button } from '@/components/design';
import {
    EventFormFields,
    type EventFormValues,
} from '@/components/event-form-fields';
import { type FieldOption } from '@/components/field-select';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

export default function AdminEventsEdit({
    event,
    types,
    statuses,
}: {
    event: EventFormValues & { id: string; slug: string };
    types: FieldOption[];
    statuses: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.events_edit')}
                description={t('admin.events_edit')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.events_title')}
                title={t('admin.events_edit')}
                description={event.title_en}
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
            <ValidatedForm
                action={`/admin/events/${event.id}`}
                method="patch"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <EventFormFields
                            event={event}
                            types={types}
                            statuses={statuses}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.save')}
                            </Button>
                            <Button href="/admin/events" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
