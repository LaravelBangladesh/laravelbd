import { Seo } from '@/components/seo';
import { AdminPageHeader } from '@/components/admin-page-header';
import { actionRowClass, Button } from '@/components/design';
import { DirectoryFormFields } from '@/components/directory-form-fields';
import { type FieldOption } from '@/components/field-select';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

export default function AdminDirectoryCreate({
    kinds,
    statuses,
}: {
    kinds: FieldOption[];
    statuses: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.directory_create')}
                description={t('admin.directory_create')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.directory_title')}
                title={t('admin.directory_create')}
            />
            <ValidatedForm
                action="/admin/directory"
                method="post"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <DirectoryFormFields
                            kinds={kinds}
                            statuses={statuses}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.create')}
                            </Button>
                            <Button href="/admin/directory" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
