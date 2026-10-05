import { router, usePage } from '@inertiajs/react';
import { Seo } from '@/components/seo';
import { AdminPageHeader, AdminSection } from '@/components/admin-page-header';
import { ValidatedForm } from '@/components/validated-form';
import { actionRowClass, Button } from '@/components/design';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import {
    ProfileFormFields,
    type ProfileFormValues,
} from '@/components/profile-form-fields';
import { useTrans } from '@/lib/i18n';

export default function AdminUserEdit({
    profile,
    visibilities,
    role,
    roles,
}: {
    profile: ProfileFormValues & { id: string; name: string };
    visibilities: FieldOption[];
    role: string;
    roles: FieldOption[];
}) {
    const t = useTrans();
    const { auth } = usePage().props;

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
            <AdminSection title={t('admin.role')} className="mt-8 max-w-3xl">
                {auth.user?.is_admin ? (
                    <FieldSelect
                        defaultValue={role}
                        options={roles}
                        className="sm:w-64"
                        onChange={(next) => {
                            router.patch(
                                `/admin/users/${profile.id}/role`,
                                { role: next },
                                { preserveScroll: true },
                            );
                        }}
                    />
                ) : (
                    <p className="text-ink text-sm">{t(`roles.${role}`)}</p>
                )}
            </AdminSection>
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
