import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { Link } from '@inertiajs/react';
import {
    Chip,
    Container,
    Display,
    Eyebrow,
    FilterPills,
    Lead,
    Mesh,
    Section,
    Surface,
} from '@/components/design';
import { type FieldOption } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';

export type ResourceCardData = {
    id: string;
    slug: string;
    title: string;
    excerpt: string;
    kind: string;
    kind_label: string;
    event: { slug: string; title: string } | null;
    speaker: { name: string } | null;
};

export default function ResourcesIndex({
    json_ld,
    resources,
    kind,
    kinds,
}: {
    json_ld: JsonLd[];
    resources: ResourceCardData[];
    kind: string | null;
    kinds: FieldOption[];
}) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('resources.title')}
                description={t('meta.resources')}
                jsonLd={json_ld}
            />
            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative py-16 sm:py-24">
                    <Eyebrow>{t('resources.title')}</Eyebrow>
                    <Display className="mt-4 max-w-4xl">
                        {t('resources.hero')}
                    </Display>
                    <Lead className="mt-5 max-w-2xl">
                        {t('resources.lead')}
                    </Lead>
                </Container>
            </Section>
            <Section tone="canvas">
                <Container className="py-16 sm:py-20">
                    <FilterPills
                        items={[
                            {
                                href: '/resources',
                                label: t('resources.all'),
                                current: kind === null,
                            },
                            ...kinds.map((option) => ({
                                href: `/resources?kind=${option.value}`,
                                label: option.label,
                                current: kind === option.value,
                            })),
                        ]}
                    />

                    {resources.length === 0 ? (
                        <p className="text-ink-muted mt-10">
                            {t('resources.empty')}
                        </p>
                    ) : (
                        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {resources.map((resource) => (
                                <Surface
                                    key={resource.id}
                                    className="hover:border-brand-red/30 p-6 transition-[border-color,transform] hover:-translate-y-0.5"
                                >
                                    <Chip>{resource.kind_label}</Chip>
                                    <h2 className="text-ink mt-3 text-lg font-medium tracking-tight">
                                        {resource.title}
                                    </h2>
                                    {resource.excerpt && (
                                        <p className="text-ink-muted mt-3 text-[15px] leading-6">
                                            {resource.excerpt}
                                        </p>
                                    )}
                                    {resource.speaker && (
                                        <p className="text-ink-muted mt-3 text-sm">
                                            {resource.speaker.name}
                                        </p>
                                    )}
                                    <Link
                                        href={`/resources/${resource.slug}`}
                                        className="text-brand-red hover:text-brand-red-hover mt-5 inline-block text-sm font-medium"
                                    >
                                        {t('resources.view')} →
                                    </Link>
                                </Surface>
                            ))}
                        </div>
                    )}
                </Container>
            </Section>
        </>
    );
}
