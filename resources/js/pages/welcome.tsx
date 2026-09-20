import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { EventCard, type EventCardData } from '@/components/event-card';
import {
    actionRowClass,
    BrandBar,
    Button,
    Container,
    Display,
    Eyebrow,
    LaravelMark,
    Lead,
    Mesh,
    Section,
    Surface,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

type Props = {
    upcomingEvents: EventCardData[];
    json_ld: JsonLd[];
    stats: {
        events: number;
    };
};

export default function Welcome({ upcomingEvents, json_ld, stats }: Props) {
    const t = useTrans();

    const facts = [
        {
            label: t('home.facts.members'),
            value: t('home.facts.members_value'),
        },
        {
            label: t('home.facts.founded'),
            value: t('home.facts.founded_value'),
        },
        {
            label: t('home.facts.meetups'),
            value: String(stats.events),
        },
    ];

    return (
        <>
            <Seo
                title={`${t('app.name')} — ${t('app.tagline')}`}
                description={t('meta.home')}
                jsonLd={json_ld}
            />
            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative grid items-center gap-12 py-16 sm:py-24 lg:grid-cols-[minmax(0,1.15fr)_minmax(16rem,0.85fr)] lg:gap-16 lg:py-28">
                    <div>
                        <Eyebrow>{t('home.hero.eyebrow')}</Eyebrow>
                        <Display className="mt-5 max-w-3xl">
                            {t('home.hero.title')}
                        </Display>
                        <Lead className="mt-6 max-w-2xl">
                            {t('home.hero.lead')}
                        </Lead>
                        <div className={`${actionRowClass} mt-10`}>
                            <Button href="/about">{t('nav.about')} →</Button>
                            <Button href="/events" variant="outline">
                                {t('nav.events')}
                            </Button>
                        </div>
                    </div>
                    <LaravelMark />
                </Container>
                <BrandBar />
                <Container>
                    <dl className="grid gap-8 py-8 sm:grid-cols-3">
                        {facts.map((fact) => (
                            <div key={fact.label}>
                                <dt className="text-ink-muted text-sm">
                                    {fact.label}
                                </dt>
                                <dd className="text-ink mt-2 text-2xl font-semibold tracking-tight tabular-nums sm:text-3xl">
                                    {fact.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Container>
            </Section>

            <Section tone="canvas">
                <Container className="py-20 sm:py-24">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <Eyebrow>{t('home.upcoming')}</Eyebrow>
                            <Display as="h2" className="mt-3">
                                {t('home.upcoming')}
                            </Display>
                        </div>
                        <div className="flex flex-wrap items-center gap-4">
                            <Button href="/events" variant="ghost">
                                {t('home.view_all_events')}
                            </Button>
                            <Button href="/events#past" variant="ghost">
                                {t('home.past_events')}
                            </Button>
                        </div>
                    </div>
                    {upcomingEvents.length === 0 ? (
                        <p className="text-ink-muted mt-10">
                            {t('home.no_events')}
                        </p>
                    ) : (
                        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {upcomingEvents.map((event) => (
                                <div key={event.slug} className="max-w-md">
                                    <EventCard event={event} />
                                </div>
                            ))}
                        </div>
                    )}
                </Container>
            </Section>

            <Section>
                <Container className="grid gap-10 py-20 sm:py-24 lg:grid-cols-2 lg:items-center">
                    <div>
                        <Eyebrow>{t('home.community.cta')}</Eyebrow>
                        <Display as="h2" className="mt-3">
                            {t('home.community.title')}
                        </Display>
                        <Lead className="mt-5 max-w-xl">
                            {t('home.community.lead')}
                        </Lead>
                        <div className={`${actionRowClass} mt-8`}>
                            <Button href="/about" variant="outline">
                                {t('home.community.cta')}
                            </Button>
                        </div>
                    </div>
                    <Surface className="p-6 sm:p-8">
                        <Eyebrow>{t('nav.directory')}</Eyebrow>
                        <p className="text-ink mt-3 text-lg font-medium tracking-tight">
                            {t('directory.lead')}
                        </p>
                        <div className={`${actionRowClass} mt-6`}>
                            <Button href="/directory">
                                {t('nav.directory')} →
                            </Button>
                            <Button href="/resources" variant="outline">
                                {t('nav.resources')}
                            </Button>
                        </div>
                    </Surface>
                </Container>
            </Section>
        </>
    );
}
