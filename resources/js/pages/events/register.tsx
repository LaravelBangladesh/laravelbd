import { Seo } from '@/components/seo';
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
import { QuestionField, type Question } from '@/components/question-field';
import { ValidatedForm } from '@/components/validated-form';
import { useTrans } from '@/lib/i18n';

type EventSummary = {
    slug: string;
    title: string;
    type_label: string;
    starts_at: string | null;
    venue_name: string | null;
};

export default function EventRegister({
    event,
    is_full,
    questions,
}: {
    event: EventSummary;
    is_full: boolean;
    questions: Question[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('events.rsvp.register')}
                description={t('events.rsvp.register')}
                noindex
            />
            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative py-16 sm:py-24">
                    <Eyebrow>{t('events.rsvp.register')}</Eyebrow>
                    <Display className="mt-4 max-w-4xl">{event.title}</Display>
                    <Lead className="mt-5 max-w-2xl">
                        {t('events.register.lead')}
                    </Lead>
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
                <Container className="max-w-3xl py-16 sm:py-20">
                    <Surface className="p-6 sm:p-8">
                        <ValidatedForm
                            action={`/events/${event.slug}/rsvp`}
                            method="post"
                            className="grid grid-cols-1 gap-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <FieldError error={errors.event} />
                                    {is_full && (
                                        <p className="text-ink-muted text-sm">
                                            {t('events.rsvp.full')}
                                        </p>
                                    )}
                                    {questions.length > 0 && (
                                        <section className="grid gap-6">
                                            <h2 className="text-ink text-lg font-medium tracking-tight">
                                                {t('events.register.questions')}
                                            </h2>
                                            {questions.map((question) => (
                                                <QuestionField
                                                    key={question.id}
                                                    question={question}
                                                    error={
                                                        errors[
                                                            `answers.${question.id}`
                                                        ]
                                                    }
                                                />
                                            ))}
                                        </section>
                                    )}
                                    <div className={actionRowClass}>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {t('events.rsvp.register')}
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
                </Container>
            </Section>
        </>
    );
}
