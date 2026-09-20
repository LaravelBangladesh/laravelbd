import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { Form, Link, usePage } from '@inertiajs/react';
import { ProfileAvatar } from '@/components/profile-avatar';
import {
    actionRowClass,
    Button,
    Chip,
    Container,
    Display,
    Eyebrow,
    Mesh,
    Section,
    Surface,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

type Speaker = {
    id: string;
    name: string;
    title: string | null;
    company: string | null;
    bio: string;
    photo_url: string | null;
    role?: string | null;
    role_label?: string | null;
};

type Question = {
    id: string;
    kind: string;
    label: string;
    help: string;
    options: string[];
    required: boolean;
};

type EventDetail = {
    slug: string;
    title: string;
    description: string;
    meta_description: string;
    json_ld: JsonLd[];
    type_label: string;
    starts_at: string | null;
    ends_at: string | null;
    starts_at_iso: string | null;
    date: string | null;
    time_range: string;
    venue_name: string | null;
    venue_address: string | null;
    venue_map_url: string | null;
    online_url: string | null;
    cover_url: string | null;
    capacity: number | null;
    registered_count: number;
    is_full: boolean;
    can_rsvp: boolean;
    registration_enabled: boolean;
    questions: Question[];
    registration: { status: string; status_label: string } | null;
    viewer: { profile_complete: boolean } | null;
    cfp: {
        enabled: boolean;
        accepting: boolean;
        pending: boolean;
        opens_at: string | null;
        closes_at: string | null;
    };
    speakers: Speaker[];
    sessions: {
        id: string;
        title: string;
        description: string;
        kind_label: string;
        starts_at: string | null;
        ends_at: string | null;
        room: string | null;
        recording_embed: string | null;
        speakers: Speaker[];
    }[];
    photos: { id: string; url: string | null; caption: string }[];
    videos: { id: string; embed: string | null; caption: string }[];
};

function MetaRow({
    label,
    children,
}: {
    label: string;
    children: React.ReactNode;
}) {
    return (
        <div className="border-line border-t py-3 sm:grid sm:grid-cols-[8rem_1fr] sm:gap-4">
            <dt className="text-ink-muted text-xs font-bold tracking-[0.12em] uppercase">
                {label}
            </dt>
            <dd className="text-ink mt-1 min-w-0 text-sm sm:mt-0">
                {children}
            </dd>
        </div>
    );
}

function SectionHeading({ label }: { label: string }) {
    return (
        <>
            <Eyebrow>{label}</Eyebrow>
            <h2 className="text-ink mt-3 text-2xl font-medium tracking-tight">
                {label}
            </h2>
        </>
    );
}

function QuestionField({
    question,
    error,
}: {
    question: Question;
    error?: string;
}) {
    const name = `answers[${question.id}]`;
    const labelId = `question-${question.id}`;

    const control = (() => {
        if (question.kind === 'long_text') {
            return (
                <textarea
                    id={labelId}
                    name={name}
                    rows={4}
                    aria-describedby={
                        question.help ? `${labelId}-help` : undefined
                    }
                    className="border-line bg-paper text-ink focus-visible:outline-brand-red mt-2 w-full rounded-none border p-3 text-sm focus:outline-none focus-visible:outline-2 focus-visible:-outline-offset-2"
                />
            );
        }

        if (question.kind === 'single_choice') {
            return (
                <div className="mt-2 space-y-2">
                    {question.options.map((option) => (
                        <label
                            key={option}
                            className="text-ink flex items-center gap-2 text-sm"
                        >
                            <input
                                type="radio"
                                name={name}
                                value={option}
                                className="accent-brand-red size-4"
                            />
                            {option}
                        </label>
                    ))}
                </div>
            );
        }

        if (question.kind === 'multiple_choice') {
            return (
                <div className="mt-2 space-y-2">
                    {question.options.map((option) => (
                        <label
                            key={option}
                            className="text-ink flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                name={`answers[${question.id}][]`}
                                value={option}
                                className="accent-brand-red size-4"
                            />
                            {option}
                        </label>
                    ))}
                </div>
            );
        }

        return (
            <input
                id={labelId}
                type="text"
                name={name}
                aria-describedby={question.help ? `${labelId}-help` : undefined}
                className="border-line bg-paper text-ink focus-visible:outline-brand-red mt-2 h-10 w-full rounded-none border px-3 text-sm focus:outline-none focus-visible:outline-2 focus-visible:-outline-offset-2"
            />
        );
    })();

    const isGroup =
        question.kind === 'single_choice' ||
        question.kind === 'multiple_choice';

    const heading = (
        <>
            {question.label}
            {question.required && (
                <span
                    className="text-brand-red ms-0.5 font-semibold"
                    aria-hidden
                >
                    *
                </span>
            )}
        </>
    );

    return (
        <div
            role={isGroup ? 'group' : undefined}
            aria-labelledby={isGroup ? labelId : undefined}
        >
            {isGroup ? (
                <p id={labelId} className="text-ink text-sm font-medium">
                    {heading}
                </p>
            ) : (
                <label
                    htmlFor={labelId}
                    className="text-ink text-sm font-medium"
                >
                    {heading}
                </label>
            )}
            {question.help && (
                <p id={`${labelId}-help`} className="text-ink-muted text-sm">
                    {question.help}
                </p>
            )}
            {control}
            {error && (
                <p role="alert" className="mt-2 text-sm text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}

function ProfileHint() {
    const t = useTrans();

    return (
        <p className="text-ink-muted mt-3 text-sm">
            {t('events.rsvp.profile_hint')}{' '}
            <Link
                href="/account/directory"
                className="text-brand-red underline"
            >
                {t('events.rsvp.profile_link')}
            </Link>
        </p>
    );
}

export default function EventShow({ event }: { event: EventDetail }) {
    const t = useTrans();
    const { auth } = usePage().props;
    const profileIncomplete =
        auth.user !== null && event.viewer?.profile_complete === false;

    return (
        <Section className="relative overflow-hidden">
            {!event.cover_url && <Mesh />}
            <Container className="relative max-w-4xl py-12 sm:py-16">
                <Seo
                    title={event.title}
                    description={event.meta_description}
                    image={event.cover_url}
                    type="event"
                    jsonLd={event.json_ld}
                />
                {event.cover_url && (
                    <img
                        src={event.cover_url}
                        alt=""
                        className="mb-8 aspect-video w-full object-cover"
                    />
                )}
                <Chip>{event.type_label}</Chip>
                <Display className="mt-4">{event.title}</Display>

                <dl className="mt-8">
                    <MetaRow label={t('events.date')}>
                        <time dateTime={event.starts_at_iso ?? undefined}>
                            {event.date}
                        </time>
                    </MetaRow>
                    <MetaRow label={t('events.time')}>
                        <time dateTime={event.starts_at_iso ?? undefined}>
                            {event.time_range}
                        </time>
                    </MetaRow>
                    <MetaRow label={t('events.venue')}>
                        <address className="not-italic">
                            {event.venue_name ?? t('events.online')}
                            {event.venue_address && (
                                <span className="text-ink-muted block">
                                    {event.venue_address}
                                </span>
                            )}
                        </address>
                        {event.venue_map_url && (
                            <a
                                href={event.venue_map_url}
                                className="text-brand-red mt-1 inline-block underline"
                            >
                                {t('events.map')}
                            </a>
                        )}
                    </MetaRow>
                    {event.online_url && (
                        <MetaRow label={t('events.online')}>
                            <a
                                href={event.online_url}
                                className="text-brand-red break-all underline"
                            >
                                {t('events.join_online')}
                            </a>
                        </MetaRow>
                    )}
                    <MetaRow label={t('events.capacity')}>
                        <span className="tabular-nums">
                            {t('events.registered_count', {
                                count: String(event.registered_count),
                            })}
                            {event.capacity
                                ? ` ${t('events.capacity_of', {
                                      capacity: String(event.capacity),
                                  })}`
                                : ''}
                        </span>
                    </MetaRow>
                </dl>

                <div className="mt-8">
                    {!auth.user ? (
                        <div className={actionRowClass}>
                            <Button href="/login">
                                {t('events.rsvp.login')}
                            </Button>
                        </div>
                    ) : event.registration ? (
                        <Form
                            action={`/events/${event.slug}/rsvp`}
                            method="delete"
                        >
                            {({ processing }) => (
                                <div
                                    className={`${actionRowClass} items-start`}
                                >
                                    <Chip>
                                        {event.registration?.status_label}
                                    </Chip>
                                    {event.can_rsvp && (
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            disabled={processing}
                                        >
                                            {t('events.rsvp.cancel')}
                                        </Button>
                                    )}
                                </div>
                            )}
                        </Form>
                    ) : event.can_rsvp ? (
                        <Form
                            action={`/events/${event.slug}/rsvp`}
                            method="post"
                        >
                            {({ processing, errors }) => (
                                <div>
                                    {event.is_full && (
                                        <p className="text-ink-muted mb-3">
                                            {t('events.rsvp.full')}
                                        </p>
                                    )}
                                    {event.questions.length > 0 && (
                                        <div className="mb-6 grid max-w-xl gap-5">
                                            {event.questions.map((question) => (
                                                <QuestionField
                                                    key={question.id}
                                                    question={question}
                                                    error={
                                                        (
                                                            errors as Record<
                                                                string,
                                                                string
                                                            >
                                                        )[
                                                            `answers.${question.id}`
                                                        ]
                                                    }
                                                />
                                            ))}
                                        </div>
                                    )}
                                    <div className={actionRowClass}>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {t('events.rsvp.register')}
                                        </Button>
                                    </div>
                                    {profileIncomplete && <ProfileHint />}
                                </div>
                            )}
                        </Form>
                    ) : !event.registration_enabled ? (
                        <p className="text-ink-muted">
                            {t('events.rsvp.closed_note')}
                        </p>
                    ) : null}
                </div>

                {event.cfp.enabled && (
                    <Surface className="mt-8 p-5 sm:p-6">
                        <Eyebrow>{t('cfp.title')}</Eyebrow>
                        {event.cfp.accepting ? (
                            <>
                                <p className="text-ink-muted mt-3 text-[15px] leading-7">
                                    {event.cfp.closes_at
                                        ? t('events.cfp.open_until', {
                                              date: event.cfp.closes_at,
                                          })
                                        : t('events.cfp.open')}
                                </p>
                                <div className={`${actionRowClass} mt-4`}>
                                    <Button href={`/events/${event.slug}/cfp`}>
                                        {t('cfp.submit')}
                                    </Button>
                                </div>
                                {profileIncomplete && <ProfileHint />}
                            </>
                        ) : (
                            <p className="text-ink-muted mt-3 text-[15px] leading-7">
                                {event.cfp.pending && event.cfp.opens_at
                                    ? t('events.cfp.opens_on', {
                                          date: event.cfp.opens_at,
                                      })
                                    : t('events.cfp.closed')}
                            </p>
                        )}
                    </Surface>
                )}

                {event.description && (
                    <p className="text-ink-muted mt-8 text-[15px] leading-7 whitespace-pre-wrap">
                        {event.description}
                    </p>
                )}

                {event.speakers.length > 0 && (
                    <section className="mt-14">
                        <SectionHeading label={t('events.speakers')} />
                        <ul className="mt-6 grid gap-4 sm:grid-cols-2">
                            {event.speakers.map((speaker) => (
                                <li key={speaker.id}>
                                    <Surface className="flex h-full gap-4 p-5">
                                        <ProfileAvatar
                                            src={speaker.photo_url}
                                            alt=""
                                            className="size-16 shrink-0 rounded-full"
                                        />
                                        <div className="min-w-0">
                                            <p className="text-ink font-medium">
                                                {speaker.name}
                                            </p>
                                            <p className="text-ink-muted text-sm">
                                                {[
                                                    speaker.title,
                                                    speaker.company,
                                                ]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </p>
                                            {speaker.role_label && (
                                                <Chip className="mt-2">
                                                    {speaker.role_label}
                                                </Chip>
                                            )}
                                        </div>
                                    </Surface>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {event.sessions.length > 0 && (
                    <section className="mt-14">
                        <SectionHeading label={t('events.schedule')} />
                        <ol className="mt-6 space-y-4">
                            {event.sessions.map((session) => (
                                <li key={session.id}>
                                    <Surface className="p-5 sm:grid sm:grid-cols-[7rem_1fr] sm:gap-5">
                                        <p className="text-ink-muted text-sm tabular-nums">
                                            {session.starts_at}
                                            <span className="hidden sm:inline">
                                                <br />
                                            </span>
                                            <span className="sm:hidden">–</span>
                                            {session.ends_at}
                                        </p>
                                        <div className="mt-3 min-w-0 sm:mt-0">
                                            <Chip>{session.kind_label}</Chip>
                                            <p className="text-ink mt-2 font-medium">
                                                {session.title}
                                            </p>
                                            {session.room && (
                                                <p className="text-ink-muted text-sm">
                                                    {session.room}
                                                </p>
                                            )}
                                            {session.description && (
                                                <p className="text-ink-muted mt-2 text-[15px]">
                                                    {session.description}
                                                </p>
                                            )}
                                            {session.speakers.length > 0 && (
                                                <p className="text-ink-muted mt-2 text-sm">
                                                    {session.speakers
                                                        .map(
                                                            (speaker) =>
                                                                speaker.name,
                                                        )
                                                        .join(', ')}
                                                </p>
                                            )}
                                            {session.recording_embed && (
                                                <iframe
                                                    title={session.title}
                                                    src={
                                                        session.recording_embed
                                                    }
                                                    className="mt-4 aspect-video w-full max-w-2xl"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowFullScreen
                                                />
                                            )}
                                        </div>
                                    </Surface>
                                </li>
                            ))}
                        </ol>
                    </section>
                )}

                {event.photos.length > 0 && (
                    <section className="mt-14">
                        <SectionHeading label={t('events.photos')} />
                        <div className="mt-6 grid gap-4 sm:grid-cols-2">
                            {event.photos.map((photo) =>
                                photo.url ? (
                                    <figure key={photo.id}>
                                        <img
                                            src={photo.url}
                                            alt={photo.caption}
                                            className="w-full object-cover"
                                        />
                                        {photo.caption && (
                                            <figcaption className="text-ink-muted mt-2 text-sm">
                                                {photo.caption}
                                            </figcaption>
                                        )}
                                    </figure>
                                ) : null,
                            )}
                        </div>
                    </section>
                )}

                {event.videos.length > 0 && (
                    <section className="mt-14">
                        <SectionHeading label={t('events.videos')} />
                        <div className="mt-6 space-y-6">
                            {event.videos.map((video) =>
                                video.embed ? (
                                    <figure key={video.id}>
                                        <iframe
                                            title={video.caption || event.title}
                                            src={video.embed}
                                            className="aspect-video w-full"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowFullScreen
                                        />
                                        {video.caption && (
                                            <figcaption className="text-ink-muted mt-2 text-sm">
                                                {video.caption}
                                            </figcaption>
                                        )}
                                    </figure>
                                ) : null,
                            )}
                        </div>
                    </section>
                )}
            </Container>
        </Section>
    );
}
