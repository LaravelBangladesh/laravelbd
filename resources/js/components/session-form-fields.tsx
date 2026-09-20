import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import { DateTimeField } from '@/components/datetime-field';
import { FieldError } from '@/components/field-error';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';

export type SessionFormValues = {
    title_en?: string;
    title_bn?: string | null;
    description_en?: string | null;
    description_bn?: string | null;
    kind?: string;
    starts_at?: string | null;
    ends_at?: string | null;
    room?: string | null;
    recording_url?: string | null;
};

export function SessionFormFields({
    session,
    sessionKinds,
    errors,
}: {
    session?: SessionFormValues;
    sessionKinds: FieldOption[];
    errors?: Record<string, string>;
}) {
    const t = useTrans();

    return (
        <div className="grid grid-cols-1 gap-4">
            <Field>
                <Label required>{t('admin.title_en')}</Label>
                <Input
                    name="title_en"
                    defaultValue={session?.title_en ?? ''}
                    required
                />
                <FieldError error={errors?.title_en} />
            </Field>
            <Field>
                <Label>{t('admin.title_bn')}</Label>
                <Input name="title_bn" defaultValue={session?.title_bn ?? ''} />
            </Field>
            <Field>
                <Label>{t('admin.description_en')}</Label>
                <Textarea
                    name="description_en"
                    rows={3}
                    defaultValue={session?.description_en ?? ''}
                />
            </Field>
            <Field>
                <Label>{t('admin.description_bn')}</Label>
                <Textarea
                    name="description_bn"
                    rows={3}
                    defaultValue={session?.description_bn ?? ''}
                />
            </Field>
            <Field>
                <Label required>{t('admin.session_kind')}</Label>
                <FieldSelect
                    name="kind"
                    options={sessionKinds}
                    defaultValue={session?.kind ?? 'talk'}
                    required
                />
            </Field>
            <div className="grid gap-4 sm:grid-cols-2">
                <Field>
                    <Label required>{t('admin.starts_at')}</Label>
                    <DateTimeField
                        name="starts_at"
                        defaultValue={session?.starts_at ?? ''}
                        required
                    />
                    <FieldError error={errors?.starts_at} />
                </Field>
                <Field>
                    <Label required>{t('admin.ends_at')}</Label>
                    <DateTimeField
                        name="ends_at"
                        defaultValue={session?.ends_at ?? ''}
                        required
                    />
                    <FieldError error={errors?.ends_at} />
                </Field>
            </div>
            <Field>
                <Label>{t('admin.room')}</Label>
                <Input name="room" defaultValue={session?.room ?? ''} />
            </Field>
            <Field>
                <Label>{t('admin.youtube_url')}</Label>
                <Input
                    name="recording_url"
                    type="url"
                    defaultValue={session?.recording_url ?? ''}
                />
                <FieldError error={errors?.recording_url} />
            </Field>
        </div>
    );
}
