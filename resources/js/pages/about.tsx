import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { usePage } from '@inertiajs/react';
import { MessagesSquare, Mic, Users } from 'lucide-react';
import {
    actionRowClass,
    Button,
    Container,
    Display,
    Eyebrow,
    Lead,
    Mesh,
    Section,
    Surface,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

type Props = {
    json_ld: JsonLd[];
    stats: {
        events: number;
        speakers: number;
    };
};

export default function About({ stats, json_ld }: Props) {
    const t = useTrans();
    const { auth } = usePage().props;

    const manifesto = [
        {
            title: t('about.manifesto.1.title'),
            body: t('about.manifesto.1.body'),
        },
        {
            title: t('about.manifesto.2.title'),
            body: t('about.manifesto.2.body'),
        },
        {
            title: t('about.manifesto.3.title'),
            body: t('about.manifesto.3.body'),
        },
    ];

    const night = [
        {
            icon: Users,
            title: t('about.night.1.title'),
            body: t('about.night.1.body'),
        },
        {
            icon: Mic,
            title: t('about.night.2.title'),
            body: t('about.night.2.body'),
        },
        {
            icon: MessagesSquare,
            title: t('about.night.3.title'),
            body: t('about.night.3.body'),
        },
    ];

    const join = [
        { title: t('about.join.1.title'), body: t('about.join.1.body') },
        { title: t('about.join.2.title'), body: t('about.join.2.body') },
        { title: t('about.join.3.title'), body: t('about.join.3.body') },
    ];

    return (
        <>
            <Seo
                title={t('about.title')}
                description={t('meta.about')}
                jsonLd={json_ld}
            />

            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative py-20 sm:py-28">
                    <Eyebrow>{t('about.hero.eyebrow')}</Eyebrow>
                    <Display className="mt-5 max-w-3xl">
                        {t('about.hero.title')}
                    </Display>
                    <Lead className="mt-6 max-w-2xl">
                        {t('about.hero.lead')}
                    </Lead>
                    <dl className="mt-14 grid max-w-3xl grid-cols-2 gap-8 sm:grid-cols-4">
                        {[
                            {
                                label: t('about.stats.members'),
                                value: t('about.stats.members_value'),
                            },
                            {
                                label: t('about.stats.founded'),
                                value: t('about.stats.founded_value'),
                            },
                            {
                                label: t('about.stats.events'),
                                value: stats.events,
                            },
                            {
                                label: t('about.stats.speakers'),
                                value: stats.speakers,
                            },
                        ].map((stat) => (
                            <div key={stat.label}>
                                <dt className="text-ink-muted text-sm">
                                    {stat.label}
                                </dt>
                                <dd className="text-ink mt-2 text-2xl font-semibold tracking-tight tabular-nums sm:text-4xl">
                                    {stat.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Container>
            </Section>

            <Section>
                <Container className="py-20 sm:py-24">
                    <Display as="h2">{t('about.manifesto.title')}</Display>
                    <ul className="mt-10 grid gap-4 lg:grid-cols-3">
                        {manifesto.map((item, index) => (
                            <li key={item.title}>
                                <Surface className="h-full p-6">
                                    <span className="text-brand-red text-sm font-medium">
                                        0{index + 1}
                                    </span>
                                    <h3 className="text-ink mt-4 text-lg font-semibold tracking-tight">
                                        {item.title}
                                    </h3>
                                    <p className="text-ink-muted mt-3 text-[15px] leading-6">
                                        {item.body}
                                    </p>
                                </Surface>
                            </li>
                        ))}
                    </ul>
                </Container>
            </Section>

            <Section tone="canvas">
                <Container className="py-20 sm:py-24">
                    <Display as="h2">{t('about.night.title')}</Display>
                    <ol className="divide-line mt-10 divide-y">
                        {night.map((item) => (
                            <li
                                key={item.title}
                                className="grid gap-4 py-8 first:pt-0 last:pb-0 sm:grid-cols-[3.25rem_1fr] sm:gap-8"
                            >
                                <span className="border-brand-green/20 text-brand-green flex size-12 items-center justify-center border">
                                    <item.icon
                                        aria-hidden
                                        className="size-5 stroke-[1.75]"
                                    />
                                </span>
                                <div>
                                    <h3 className="text-ink text-lg font-semibold tracking-tight">
                                        {item.title}
                                    </h3>
                                    <p className="text-ink-muted mt-2 text-[15px] leading-6">
                                        {item.body}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ol>
                </Container>
            </Section>

            <Section>
                <Container className="grid gap-16 py-20 sm:py-24 lg:grid-cols-2">
                    <div>
                        <Display as="h2">{t('about.who.title')}</Display>
                        <ul className="mt-8 space-y-5">
                            {[
                                t('about.who.1'),
                                t('about.who.2'),
                                t('about.who.3'),
                            ].map((item) => (
                                <li key={item} className="flex gap-3">
                                    <span className="bg-brand-green mt-2 size-1.5 shrink-0" />
                                    <p className="text-ink-muted text-[15px] leading-6">
                                        {item}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div>
                        <Display as="h2">{t('about.join.title')}</Display>
                        <ol className="mt-8 space-y-7">
                            {join.map((item, index) => (
                                <li key={item.title}>
                                    <p className="text-brand-green text-sm font-medium">
                                        {String(index + 1).padStart(2, '0')}
                                    </p>
                                    <h3 className="text-ink mt-1 text-lg font-semibold tracking-tight">
                                        {item.title}
                                    </h3>
                                    <p className="text-ink-muted mt-1 text-[15px] leading-6">
                                        {item.body}
                                    </p>
                                </li>
                            ))}
                        </ol>
                    </div>
                </Container>
            </Section>

            <Section tone="canvas">
                <Container className="py-20 sm:py-24">
                    <Display as="h2" className="max-w-xl">
                        {t('about.cities.title')}
                    </Display>
                    <Lead className="mt-5 max-w-2xl">
                        {t('about.cities.body')}
                    </Lead>
                    <p className="text-ink mt-8 text-2xl font-semibold tracking-tight sm:text-3xl">
                        {t('about.cities.list')}
                    </p>
                </Container>
            </Section>

            <Section tone="ink">
                <Container className="py-20 sm:py-24">
                    <h2 className="max-w-2xl text-3xl font-semibold tracking-[-0.04em] text-white sm:text-5xl">
                        {t('about.cta.title')}
                    </h2>
                    <p className="mt-5 max-w-xl text-lg leading-relaxed text-white/65">
                        {t('about.cta.lead')}
                    </p>
                    <div className={`${actionRowClass} mt-8`}>
                        <Button href="/events">{t('about.cta.events')}</Button>
                        {!auth.user && (
                            <Button variant="inverse" href="/login">
                                {t('about.cta.register')}
                            </Button>
                        )}
                    </div>
                    <p className="mt-12 max-w-xl text-sm text-white/45">
                        {t('about.directory_note')}
                    </p>
                </Container>
            </Section>
        </>
    );
}
