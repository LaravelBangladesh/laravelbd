import { Seo } from '@/components/seo';
import { Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Textarea } from '@/components/catalyst/textarea';
import {
    actionRowClass,
    Button,
    Chip,
    Container,
    Display,
    Eyebrow,
    Lead,
    Mesh,
    Section,
    Surface,
} from '@/components/design';
import { FieldError } from '@/components/field-error';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

type EventSummary = {
    slug: string;
    title: string;
    type_label: string;
    starts_at: string | null;
    venue_name: string | null;
};

function Aside() {
    const t = useTrans();

    return (
        <Surface className="p-6 sm:p-8 lg:sticky lg:top-24">
            <Eyebrow>{t('cfp.what_we_look_for')}</Eyebrow>
            <ul className="text-ink-muted mt-4 space-y-3 text-sm leading-6">
                {[t('cfp.look.1'), t('cfp.look.2'), t('cfp.look.3')].map(
                    (item) => (
                        <li key={item} className="flex gap-3">
                            <span
                                aria-hidden
                                className="bg-brand-green mt-2 size-1.5 shrink-0"
                            />
                            <span>{item}</span>
                        </li>
                    ),
                )}
            </ul>

            <div className="border-line mt-8 border-t pt-6">
                <Eyebrow>{t('cfp.timeline')}</Eyebrow>
                <ol className="mt-4 space-y-4">
                    {[t('cfp.step.1'), t('cfp.step.2'), t('cfp.step.3')].map(
                        (step, index) => (
                            <li key={step} className="flex gap-3">
                                <span
                                    aria-hidden
                                    className="border-line text-ink-muted flex size-6 shrink-0 items-center justify-center border text-xs font-bold tabular-nums"
                                >
                                    {index + 1}
                                </span>
                                <span className="text-ink-muted text-sm leading-6">
                                    {step}
                                </span>
                            </li>
                        ),
                    )}
                </ol>
            </div>
        </Surface>
    );
}

export default function EventCfp({
    event,
    kinds,
}: {
    event: EventSummary;
    kinds: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo title={t('cfp.title')} description={t('cfp.title')} noindex />
            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative py-16 sm:py-24">
                    <Eyebrow>{t('cfp.title')}</Eyebrow>
                    <Display className="mt-4 max-w-4xl">{event.title}</Display>
                    <Lead className="mt-5 max-w-2xl">{t('cfp.lead')}</Lead>
                    <div className="mt-6 flex flex-wrap items-center gap-3">
                        <Chip>{event.type_label}</Chip>
                        {event.starts_at && (
                            <span className="text-ink-muted text-sm">
                                {event.starts_at}
                            </span>
                        )}
                        {event.venue_name && (
                            <span className="text-ink-muted text-sm">
                                {event.venue_name}
                            </span>
                        )}
                    </div>
                </Container>
            </Section>
            <Section tone="canvas">
                <Container className="grid gap-10 py-16 sm:py-20 lg:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)] lg:gap-14">
                    <div className="min-w-0">
                        <Surface className="p-6 sm:p-8">
                            <Eyebrow>{t('cfp.submit')}</Eyebrow>
                            <p className="text-ink-muted mt-3 text-sm">
                                {t('cfp.profile_note')}
                            </p>
                            <ValidatedForm
                                action={`/events/${event.slug}/cfp`}
                                method="post"
                                className="mt-6 grid grid-cols-1 gap-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <FieldError error={errors.event} />
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <Field>
                                                <Label required>
                                                    {t('admin.title_en')}
                                                </Label>
                                                <Input
                                                    name="title_en"
                                                    required
                                                />
                                                <FieldError
                                                    error={errors.title_en}
                                                />
                                            </Field>
                                            <Field>
                                                <Label>
                                                    {t('admin.title_bn')}
                                                </Label>
                                                <Input name="title_bn" />
                                            </Field>
                                        </div>
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <Field>
                                                <Label required>
                                                    {t('cfp.abstract_en')}
                                                </Label>
                                                <Textarea
                                                    name="abstract_en"
                                                    rows={5}
                                                    required
                                                />
                                                <FieldError
                                                    error={errors.abstract_en}
                                                />
                                            </Field>
                                            <Field>
                                                <Label>
                                                    {t('cfp.abstract_bn')}
                                                </Label>
                                                <Textarea
                                                    name="abstract_bn"
                                                    rows={5}
                                                />
                                            </Field>
                                        </div>
                                        <Field>
                                            <Label required>
                                                {t('cfp.kind')}
                                            </Label>
                                            <FieldSelect
                                                name="kind"
                                                options={kinds}
                                                defaultValue="talk"
                                                required
                                            />
                                        </Field>
                                        <div className={actionRowClass}>
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                {t('cfp.submit')}
                                            </Button>
                                            <Button
                                                href={`/events/${event.slug}`}
                                                variant="ghost"
                                            >
                                                {t('admin.cancel')}
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </ValidatedForm>
                        </Surface>
                    </div>
                    <Aside />
                </Container>
            </Section>
        </>
    );
}
