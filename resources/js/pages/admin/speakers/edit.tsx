import { Seo } from '@/components/seo';
import { Form } from '@inertiajs/react';
import { AdminPageHeader } from '@/components/admin-page-header';
import { actionRowClass, Button } from '@/components/design';
import {
    SpeakerFormFields,
    type SpeakerFormValues,
} from '@/components/speaker-form-fields';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

export default function AdminSpeakersEdit({
    speaker,
}: {
    speaker: SpeakerFormValues & { id: string };
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('admin.speakers_edit')}
                description={t('admin.speakers_edit')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.speakers_title')}
                title={t('admin.speakers_edit')}
                description={speaker.name}
            />
            <ValidatedForm
                action={`/admin/speakers/${speaker.id}`}
                method="patch"
                encType="multipart/form-data"
                className="mt-8 grid max-w-3xl grid-cols-1 gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <SpeakerFormFields speaker={speaker} errors={errors} />
                        <div className={actionRowClass}>
                            <Button type="submit" disabled={processing}>
                                {t('admin.save')}
                            </Button>
                            <Button href="/admin/speakers" variant="ghost">
                                {t('admin.cancel')}
                            </Button>
                        </div>
                    </>
                )}
            </ValidatedForm>
            <Form
                action={`/admin/speakers/${speaker.id}`}
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
