import { Seo } from '@/components/seo';
import { AdminPageHeader } from '@/components/admin-page-header';
import { actionRowClass, Button } from '@/components/design';
import { type FieldOption } from '@/components/field-select';
import { ResourceFormFields } from '@/components/resource-form-fields';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

export default function AdminResourcesCreate({
    kinds,
    statuses,
    events,
    speakers,
}: {
    kinds: FieldOption[];
    statuses: FieldOption[];
    events: FieldOption[];
    speakers: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.resources_create')}
                description={t('admin.resources_create')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.resources_title')}
                title={t('admin.resources_create')}
            />
            <ValidatedForm
                action="/admin/resources"
                method="post"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <ResourceFormFields
                            kinds={kinds}
                            statuses={statuses}
                            events={events}
                            speakers={speakers}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.create')}
                            </Button>
                            <Button href="/admin/resources" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
