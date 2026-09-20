import { Seo } from '@/components/seo';
import { Form } from '@inertiajs/react';
import { AdminPageHeader } from '@/components/admin-page-header';
import { ValidatedForm } from '@/components/validated-form';
import { actionRowClass, Button } from '@/components/design';
import { type FieldOption } from '@/components/field-select';
import {
    ResourceFormFields,
    type ResourceFormValues,
} from '@/components/resource-form-fields';
import { useTrans } from '@/lib/i18n';

export default function AdminResourcesEdit({
    resource,
    kinds,
    statuses,
    events,
    speakers,
}: {
    resource: ResourceFormValues & { id: string; slug: string };
    kinds: FieldOption[];
    statuses: FieldOption[];
    events: FieldOption[];
    speakers: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.resources_edit')}
                description={t('admin.resources_edit')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.resources_title')}
                title={t('admin.resources_edit')}
                description={resource.title_en}
                actions={
                    <Button
                        href={`/resources/${resource.slug}`}
                        variant="outline"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.view_public')}
                    </Button>
                }
            />
            <ValidatedForm
                action={`/admin/resources/${resource.id}`}
                method="patch"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <ResourceFormFields
                            resource={resource}
                            kinds={kinds}
                            statuses={statuses}
                            events={events}
                            speakers={speakers}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.save')}
                            </Button>
                            <Button href="/admin/resources" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
            <Form
                action={`/admin/resources/${resource.id}`}
                method="delete"
                className="mt-6 max-w-3xl"
            >
                <Button
                    type="submit"
                    variant="outline"
                    className="w-full sm:w-auto"
                >
                    {t('admin.delete')}
                </Button>
            </Form>
        </>
    );
}
