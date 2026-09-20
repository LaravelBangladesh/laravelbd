import { cleanup, render } from '@testing-library/react';
import type { ReactElement } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { setPage } from '@/test/inertia';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Seo } = await import('@/components/seo');

const seo = {
    url: 'https://laravelbd.test/events/laracon',
    default_image: 'https://laravelbd.test/images/og-default.webp',
    site_name: 'Laravel Bangladesh',
};

/**
 * React hoists `title`, `meta` and `link` out of the tree into the document
 * head, so assertions read from there while the JSON-LD scripts stay inline.
 */
function renderSeo(ui: ReactElement, props: Record<string, unknown> = {}) {
    setPage({ seo, ...props });

    const { container } = render(ui);

    return {
        head: document.head,
        container,
    };
}

function meta(head: HTMLElement, selector: string): string | null {
    return head.querySelector(selector)?.getAttribute('content') ?? null;
}

describe('Seo', () => {
    afterEach(() => {
        // Unmount first so React removes its own hoisted tags, then clear
        // whatever it left behind so each test starts from an empty head.
        cleanup();
        document.head.replaceChildren();
    });

    it('renders the title, description and canonical url', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="A day of Laravel talks." />,
        );

        expect(head.querySelector('title')?.textContent).toBe('Laracon');
        expect(meta(head, 'meta[name="description"]')).toBe(
            'A day of Laravel talks.',
        );
        expect(
            head.querySelector('link[rel="canonical"]')?.getAttribute('href'),
        ).toBe(seo.url);
    });

    it('describes the page to Open Graph and Twitter', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="A day of Laravel talks." />,
        );

        expect(meta(head, 'meta[property="og:title"]')).toBe('Laracon');
        expect(meta(head, 'meta[property="og:description"]')).toBe(
            'A day of Laravel talks.',
        );
        expect(meta(head, 'meta[property="og:url"]')).toBe(seo.url);
        expect(meta(head, 'meta[property="og:site_name"]')).toBe(
            'Laravel Bangladesh',
        );
        expect(meta(head, 'meta[property="og:image:width"]')).toBe('1200');
        expect(meta(head, 'meta[property="og:image:height"]')).toBe('630');
        expect(meta(head, 'meta[name="twitter:card"]')).toBe(
            'summary_large_image',
        );
        expect(meta(head, 'meta[name="twitter:title"]')).toBe('Laracon');
        expect(meta(head, 'meta[name="twitter:description"]')).toBe(
            'A day of Laravel talks.',
        );
    });

    it('falls back to the shared default image', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
        );

        expect(meta(head, 'meta[property="og:image"]')).toBe(seo.default_image);
        expect(meta(head, 'meta[name="twitter:image"]')).toBe(
            seo.default_image,
        );
    });

    it('prefers the image it is given', () => {
        const { head } = renderSeo(
            <Seo
                title="Laracon"
                description="Talks."
                image="https://laravelbd.test/covers/laracon.png"
            />,
        );

        expect(meta(head, 'meta[property="og:image"]')).toBe(
            'https://laravelbd.test/covers/laracon.png',
        );
        expect(meta(head, 'meta[name="twitter:image"]')).toBe(
            'https://laravelbd.test/covers/laracon.png',
        );
    });

    it('treats a null image as absent', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." image={null} />,
        );

        expect(meta(head, 'meta[property="og:image"]')).toBe(seo.default_image);
    });

    it.each([
        ['website', 'website'],
        ['article', 'article'],
        ['profile', 'profile'],
        // Open Graph has no event type, so events publish as a website.
        ['event', 'website'],
    ] as const)('maps the %s type to og:type %s', (type, expected) => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." type={type} />,
        );

        expect(meta(head, 'meta[property="og:type"]')).toBe(expected);
    });

    it('defaults to the website type', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
        );

        expect(meta(head, 'meta[property="og:type"]')).toBe('website');
    });

    it('publishes the english locale and its bangla alternate', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
        );

        expect(meta(head, 'meta[property="og:locale"]')).toBe('en_US');
        expect(meta(head, 'meta[property="og:locale:alternate"]')).toBe(
            'bn_BD',
        );
    });

    it('publishes the bangla locale and its english alternate', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
            { locale: 'bn' },
        );

        expect(meta(head, 'meta[property="og:locale"]')).toBe('bn_BD');
        expect(meta(head, 'meta[property="og:locale:alternate"]')).toBe(
            'en_US',
        );
    });

    it('falls back to the english locale for an unknown one', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
            { locale: 'fr' },
        );

        expect(meta(head, 'meta[property="og:locale"]')).toBe('en_US');
        expect(meta(head, 'meta[property="og:locale:alternate"]')).toBe(
            'bn_BD',
        );
    });

    it('leaves indexable pages without a robots directive', () => {
        const { head } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
        );

        expect(head.querySelector('meta[name="robots"]')).toBeNull();
    });

    it('asks crawlers to skip a noindex page', () => {
        const { head } = renderSeo(
            <Seo title="Account" description="Your account." noindex />,
        );

        expect(meta(head, 'meta[name="robots"]')).toBe('noindex, nofollow');
    });

    it('renders no structured data when none is given', () => {
        const { container } = renderSeo(
            <Seo title="Laracon" description="Talks." />,
        );

        expect(
            container.querySelectorAll('script[type="application/ld+json"]'),
        ).toHaveLength(0);
    });

    it('renders a single structured data block', () => {
        const { container } = renderSeo(
            <Seo
                title="Laracon"
                description="Talks."
                jsonLd={{ '@type': 'Event', name: 'Laracon' }}
            />,
        );

        const scripts = container.querySelectorAll(
            'script[type="application/ld+json"]',
        );

        expect(scripts).toHaveLength(1);
        expect(JSON.parse(scripts[0].textContent ?? '')).toEqual({
            '@type': 'Event',
            name: 'Laracon',
        });
    });

    it('renders every block of an array of structured data', () => {
        const { container } = renderSeo(
            <Seo
                title="Laracon"
                description="Talks."
                jsonLd={[
                    { '@type': 'Event', name: 'Laracon' },
                    { '@type': 'BreadcrumbList' },
                ]}
            />,
        );

        const scripts = container.querySelectorAll(
            'script[type="application/ld+json"]',
        );

        expect(scripts).toHaveLength(2);
        expect(JSON.parse(scripts[1].textContent ?? '')).toEqual({
            '@type': 'BreadcrumbList',
        });
    });
});
