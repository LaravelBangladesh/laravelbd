import { useState } from 'react';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { FieldError } from '@/components/field-error';
import { Input } from '@/components/catalyst/input';
import { Select } from '@/components/catalyst/select';
import { type FieldOption } from '@/components/field-select';
import { ImageUploader } from '@/components/image-uploader';
import { useTrans } from '@/lib/i18n';

type SpeakerOption = { id: string; name: string };

export function SessionSpeakerFields({
    speakers,
    roles,
    allowNone = false,
    errors,
}: {
    speakers: SpeakerOption[];
    roles: FieldOption[];
    allowNone?: boolean;
    errors?: Record<string, string>;
}) {
    const t = useTrans();
    const canUseExisting = speakers.length > 0;
    const [source, setSource] = useState(() => {
        if (allowNone) {
            return 'none';
        }

        return canUseExisting ? 'existing' : 'new';
    });

    return (
        <div className="grid grid-cols-1 gap-4 border-t border-zinc-950/5 pt-4">
            <Field>
                <Label required>{t('admin.speaker_source')}</Label>
                <Select
                    name="speaker_source"
                    value={source}
                    required
                    onChange={(event) => setSource(event.target.value)}
                >
                    {allowNone && (
                        <option value="none">{t('admin.speaker_none')}</option>
                    )}
                    {canUseExisting && (
                        <option value="existing">
                            {t('admin.speaker_existing')}
                        </option>
                    )}
                    <option value="new">{t('admin.speaker_new')}</option>
                </Select>
            </Field>
            {source !== 'none' && (
                <Field>
                    <Label required>{t('admin.role')}</Label>
                    <Select name="speaker_role" defaultValue="speaker" required>
                        {roles.map((role) => (
                            <option key={role.value} value={role.value}>
                                {role.label}
                            </option>
                        ))}
                    </Select>
                </Field>
            )}
            {source === 'existing' && canUseExisting && (
                <Field>
                    <Label required>{t('admin.speakers')}</Label>
                    <Select name="speaker_id" required>
                        {speakers.map((speaker) => (
                            <option key={speaker.id} value={speaker.id}>
                                {speaker.name}
                            </option>
                        ))}
                    </Select>
                    <FieldError error={errors?.speaker_id} />
                </Field>
            )}
            {source === 'new' && (
                <>
                    <Field>
                        <Label required>{t('admin.speaker_name')}</Label>
                        <Input name="speaker_name" required />
                        <FieldError error={errors?.speaker_name} />
                    </Field>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field>
                            <Label>{t('admin.speaker_title')}</Label>
                            <Input name="speaker_title" />
                        </Field>
                        <Field>
                            <Label>{t('admin.company')}</Label>
                            <Input name="speaker_company" />
                        </Field>
                    </div>
                    <Field>
                        <Label>{t('admin.photo')}</Label>
                        <Description>{t('admin.image.speaker')}</Description>
                        <ImageUploader name="speaker_photo" aspect="square" />
                    </Field>
                </>
            )}
        </div>
    );
}
