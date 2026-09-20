import { Seo } from '@/components/seo';
import { Form } from '@inertiajs/react';
import { AdminPageHeader } from '@/components/admin-page-header';
import { ValidatedForm } from '@/components/validated-form';
import { actionRowClass, Button } from '@/components/design';
import {
    DirectoryFormFields,
    type DirectoryFormValues,
} from '@/components/directory-form-fields';
import { type FieldOption } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';

export default function AdminDirectoryEdit({
    listing,
    kinds,
    statuses,
}: {
    listing: DirectoryFormValues & { id: string; slug: string };
    kinds: FieldOption[];
    statuses: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.directory_edit')}
                description={t('admin.directory_edit')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.directory_title')}
                title={t('admin.directory_edit')}
                description={listing.name}
                actions={
                    <Button
                        href={`/directory/${listing.slug}`}
                        variant="outline"
                        className="w-full sm:w-auto"
                    >
                        {t('admin.view_public')}
                    </Button>
                }
            />
            <ValidatedForm
                action={`/admin/directory/${listing.id}`}
                method="patch"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <DirectoryFormFields
                            listing={listing}
                            kinds={kinds}
                            statuses={statuses}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.save')}
                            </Button>
                            <Button href="/admin/directory" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
            <Form
                action={`/admin/directory/${listing.id}`}
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
