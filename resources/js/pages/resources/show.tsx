import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { Link } from '@inertiajs/react';
import {
    Button,
    Chip,
    Container,
    Display,
    Lead,
    Section,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

type ResourceDetail = {
    slug: string;
    title: string;
    excerpt: string;
    description: string;
    meta_description: string;
    json_ld: JsonLd[];
    kind_label: string;
    url: string | null;
    embed: string | null;
    event: { slug: string; title: string } | null;
    speaker: { name: string } | null;
};

export default function ResourceShow({
    resource,
}: {
    resource: ResourceDetail;
}) {
    const t = useTrans();

    return (
        <Section>
            <Container className="max-w-4xl py-12 sm:py-16">
                <Seo
                    title={resource.title}
                    description={resource.meta_description}
                    type="article"
                    jsonLd={resource.json_ld}
                />
                <Chip>{resource.kind_label}</Chip>
                <Display className="mt-4">{resource.title}</Display>
                {resource.excerpt && (
                    <Lead className="mt-4">{resource.excerpt}</Lead>
                )}
                {resource.speaker && (
                    <p className="text-ink-muted mt-3">
                        {resource.speaker.name}
                    </p>
                )}
                {resource.event && (
                    <p className="text-ink-muted mt-2">
                        <Link
                            href={`/events/${resource.event.slug}`}
                            className="text-brand-red underline"
                        >
                            {resource.event.title}
                        </Link>
                    </p>
                )}
                {resource.embed && (
                    <div className="border-line mt-10 aspect-video w-full overflow-hidden border bg-black">
                        <iframe
                            src={resource.embed}
                            title={resource.title}
                            className="size-full"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        />
                    </div>
                )}
                {resource.description && (
                    <p className="text-ink mt-8 text-[17px] leading-7 whitespace-pre-line">
                        {resource.description}
                    </p>
                )}
                {resource.url && (
                    <div className="mt-8 w-full sm:w-auto">
                        <Button
                            href={resource.url}
                            className="w-full sm:w-auto"
                        >
                            {t('resources.open')}
                        </Button>
                    </div>
                )}
            </Container>
        </Section>
    );
}
