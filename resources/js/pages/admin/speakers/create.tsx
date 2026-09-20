import { Seo } from '@/components/seo';
import { AdminPageHeader } from '@/components/admin-page-header';
import { actionRowClass, Button } from '@/components/design';
import { SpeakerFormFields } from '@/components/speaker-form-fields';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

export default function AdminSpeakersCreate() {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.speakers_create')}
                description={t('admin.speakers_create')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.speakers_title')}
                title={t('admin.speakers_create')}
            />
            <ValidatedForm
                action="/admin/speakers"
                method="post"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <SpeakerFormFields errors={errors} />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.create')}
                            </Button>
                            <Button href="/admin/speakers" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
        </>
    );
}
