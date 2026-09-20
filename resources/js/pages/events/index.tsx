import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { EventCard, type EventCardData } from '@/components/event-card';
import {
    Container,
    Display,
    Eyebrow,
    FilterPills,
    Lead,
    Mesh,
    Section,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

type Props = {
    json_ld: JsonLd[];
    upcoming: EventCardData[];
    past: EventCardData[];
    type: string | null;
    types: { value: string; label: string }[];
};

export default function EventsIndex({
    json_ld,
    upcoming,
    past,
    type,
    types,
}: Props) {
    const t = useTrans();

    const pills = [
        { href: '/events', label: t('events.filter.all'), current: !type },
        ...types.map((option) => ({
            href: `/events?type=${option.value}`,
            label: option.label,
            current: type === option.value,
        })),
    ];

    return (
        <>
            <Seo
                title={t('events.title')}
                description={t('meta.events')}
                jsonLd={json_ld}
            />
            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative py-16 sm:py-24">
                    <Eyebrow>{t('events.title')}</Eyebrow>
                    <Display className="mt-4 max-w-4xl">
                        {t('events.hero')}
                    </Display>
                    <Lead className="mt-5 max-w-2xl">{t('events.lead')}</Lead>
                    <FilterPills items={pills} className="mt-8" />
                </Container>
            </Section>
            <Section tone="canvas">
                <Container className="py-16 sm:py-20">
                    <section>
                        <Eyebrow>{t('events.upcoming')}</Eyebrow>
                        <Display
                            as="h2"
                            className="mt-3 text-2xl! sm:text-3xl!"
                        >
                            {t('events.upcoming')}
                        </Display>
                        {upcoming.length === 0 ? (
                            <p className="text-ink-muted mt-6">
                                {t('events.no_upcoming')}
                            </p>
                        ) : (
                            <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {upcoming.map((event) => (
                                    <EventCard key={event.slug} event={event} />
                                ))}
                            </div>
                        )}
                    </section>

                    <section id="past" className="mt-16">
                        <Eyebrow>{t('events.past')}</Eyebrow>
                        <Display
                            as="h2"
                            className="mt-3 text-2xl! sm:text-3xl!"
                        >
                            {t('events.past')}
                        </Display>
                        {past.length === 0 ? (
                            <p className="text-ink-muted mt-6">
                                {t('events.no_past')}
                            </p>
                        ) : (
                            <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {past.map((event) => (
                                    <EventCard key={event.slug} event={event} />
                                ))}
                            </div>
                        )}
                    </section>
                </Container>
            </Section>
        </>
    );
}
