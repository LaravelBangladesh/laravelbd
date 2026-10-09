import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { EventCard, type EventCardData } from '@/components/event-card';
import { HomeHero, type FeaturedEvent } from '@/components/home-hero';
import {
    actionRowClass,
    Button,
    Container,
    Display,
    Eyebrow,
    Lead,
    Section,
    Surface,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

type Props = {
    upcomingEvents: EventCardData[];
    featuredEvent: FeaturedEvent | null;
    json_ld: JsonLd[];
    stats: {
        events: number;
        meetups: number;
        cities: number;
    };
};

export default function Welcome({
    upcomingEvents,
    featuredEvent,
    json_ld,
    stats,
}: Props) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={`${t('app.name')} — ${t('app.tagline')}`}
                description={t('meta.home')}
                jsonLd={json_ld}
            />
            <HomeHero featuredEvent={featuredEvent} counts={stats} />

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
