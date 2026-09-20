import { Head, usePage } from '@inertiajs/react';
import type { JsonLd, SeoType } from '@/types/seo';

type Props = {
    title: string;
    description: string;
    image?: string | null;
    type?: SeoType;
    noindex?: boolean;
    jsonLd?: JsonLd | JsonLd[];
};

const ogTypes: Record<SeoType, string> = {
    website: 'website',
    article: 'article',
    profile: 'profile',
    // Open Graph has no `event` type, so events are published as pages and
    // describe themselves through their schema.org JSON-LD instead.
    event: 'website',
};

const ogLocales: Record<string, string> = {
    en: 'en_US',
    bn: 'bn_BD',
};

export function Seo({
    title,
    description,
    image,
    type = 'website',
    noindex = false,
    jsonLd,
}: Props) {
    const { locale, seo } = usePage().props;
    const ogLocale = ogLocales[locale] ?? ogLocales.en;
    const alternateLocale =
        ogLocale === ogLocales.en ? ogLocales.bn : ogLocales.en;
    const ogImage = image ?? seo.default_image;
    const blocks = jsonLd === undefined ? [] : [jsonLd].flat();

    return (
        <Head title={title}>
            <meta
                name="description"
                content={description}
                head-key="description"
            />
            <link rel="canonical" href={seo.url} head-key="canonical" />

            <meta
                property="og:type"
                content={ogTypes[type]}
                head-key="og:type"
            />
            <meta property="og:title" content={title} head-key="og:title" />
            <meta
                property="og:description"
                content={description}
                head-key="og:description"
            />
            <meta property="og:url" content={seo.url} head-key="og:url" />
            <meta property="og:image" content={ogImage} head-key="og:image" />
            <meta
                property="og:image:width"
                content="1200"
                head-key="og:image:width"
            />
            <meta
                property="og:image:height"
                content="630"
                head-key="og:image:height"
            />
            <meta
                property="og:site_name"
                content={seo.site_name}
                head-key="og:site_name"
            />
            <meta
                property="og:locale"
                content={ogLocale}
                head-key="og:locale"
            />
            <meta
                property="og:locale:alternate"
                content={alternateLocale}
                head-key="og:locale:alternate"
            />

            <meta
                name="twitter:card"
                content="summary_large_image"
                head-key="twitter:card"
            />
            <meta
                name="twitter:title"
                content={title}
                head-key="twitter:title"
            />
            <meta
                name="twitter:description"
                content={description}
                head-key="twitter:description"
            />
            <meta
                name="twitter:image"
                content={ogImage}
                head-key="twitter:image"
            />

            {noindex && (
                <meta
                    name="robots"
                    content="noindex, nofollow"
                    head-key="robots"
                />
            )}

            {blocks.map((block, index) => (
                <script
                    key={index}
                    type="application/ld+json"
                    head-key={`ld-${index}`}
                    dangerouslySetInnerHTML={{
                        __html: JSON.stringify(block).replace(/</g, '\\u003c'),
                    }}
                />
            ))}
        </Head>
    );
}
