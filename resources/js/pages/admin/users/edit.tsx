import { Seo } from '@/components/seo';
import { AdminPageHeader } from '@/components/admin-page-header';
import { ValidatedForm } from '@/components/validated-form';
import { actionRowClass, Button } from '@/components/design';
import { type FieldOption } from '@/components/field-select';
import {
    ProfileFormFields,
    type ProfileFormValues,
} from '@/components/profile-form-fields';
import { useTrans } from '@/lib/i18n';

export default function AdminUserEdit({
    profile,
    visibilities,
}: {
    profile: ProfileFormValues & { id: string; name: string };
    visibilities: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.user_edit')}
                description={t('admin.user_edit')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.users_title')}
                title={t('admin.user_edit')}
                description={profile.name}
                actions={
                    profile.slug ? (
                        <Button
                            href={`/directory/${profile.slug}`}
                            variant="outline"
                            className="w-full sm:w-auto"
                        >
                            {t('admin.view_public')}
                        </Button>
                    ) : undefined
                }
            />
            <ValidatedForm
                action={`/admin/users/${profile.id}`}
                method="patch"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <ProfileFormFields
                            profile={profile}
                            visibilities={visibilities}
                            errors={errors}
                        />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.save')}
                            </Button>
                            <Button href="/admin/users" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
