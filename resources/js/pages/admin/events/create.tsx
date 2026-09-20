import { Seo } from '@/components/seo';
import { AdminPageHeader } from '@/components/admin-page-header';
import { actionRowClass, Button } from '@/components/design';
import { EventFormFields } from '@/components/event-form-fields';
import { type FieldOption } from '@/components/field-select';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

export default function AdminEventsCreate({
    types,
    statuses,
}: {
    types: FieldOption[];
    statuses: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.events_create')}
                description={t('admin.events_create')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.events_title')}
                title={t('admin.events_create')}
            />
            <ValidatedForm
                action="/admin/events"
                method="post"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <EventFormFields
                            types={types}
                            statuses={statuses}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.create')}
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
