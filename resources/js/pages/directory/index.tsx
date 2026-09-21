import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import { useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    actionRowClass,
    Button,
    Container,
    Display,
    Eyebrow,
    FilterPills,
    Lead,
    Mesh,
    Section,
} from '@/components/design';
import { type FieldOption } from '@/components/field-select';
import { ProfileAvatar } from '@/components/profile-avatar';
import { useTrans } from '@/lib/i18n';
import { Search } from 'lucide-react';

export type DirectoryCardData = {
    id: string;
    slug: string;
    name: string;
    title: string | null;
    city: string | null;
    company: string | null;
    kind: string;
    kind_label: string;
    photo_url: string | null;
};

function initials(name: string): string {
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0] ?? '')
        .join('')
        .toUpperCase();
}

export default function DirectoryIndex({
    json_ld,
    listings,
    kind,
    kinds,
}: {
    json_ld: JsonLd[];
    listings: DirectoryCardData[];
    kind: string | null;
    kinds: FieldOption[];
}) {
    const t = useTrans();
    const user = usePage().props.auth.user;
    const [query, setQuery] = useState('');
    const [filtersOpen, setFiltersOpen] = useState(kind !== null);
    const profileHref = user ? '/account/directory' : '/login';
    const activeFilterCount = kind === null ? 0 : 1;

    const visible = useMemo(() => {
        const needle = query.trim().toLowerCase();

        if (needle === '') {
            return listings;
        }

        return listings.filter((listing) =>
            [listing.name, listing.title, listing.company, listing.city]
                .filter(Boolean)
                .join(' ')
                .toLowerCase()
                .includes(needle),
        );
    }, [listings, query]);

    const filters = [
        {
            href: '/directory',
            label: t('resources.all'),
            current: kind === null,
        },
        ...kinds.map((option) => ({
            href: `/directory?kind=${option.value}`,
            label: option.label,
            current: kind === option.value,
        })),
    ];

    return (
        <>
            <Seo
                title={t('directory.title')}
                description={t('meta.directory')}
                jsonLd={json_ld}
            />

            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative grid gap-8 py-16 sm:py-20 lg:grid-cols-[5fr_6fr] lg:items-end lg:gap-14 lg:py-24">
                    <div>
                        <Eyebrow>{t('directory.title')}</Eyebrow>
                        <Display className="mt-4">
                            {t('directory.hero')}
                        </Display>
                    </div>
                    <div>
                        <Lead>{t('directory.lead')}</Lead>
                        <p className="text-ink-muted mt-5 text-[15px] leading-6">
                            {t('directory.invite')}{' '}
                            <Link
                                href={profileHref}
                                className="text-brand-red hover:text-brand-red-hover underline decoration-1 underline-offset-[3px]"
                            >
                                {t('directory.cta.button')}
                            </Link>
                        </p>
                    </div>
                </Container>
            </Section>

            <div className="border-line bg-paper border-y">
                <Container className="flex min-h-[64px] flex-col sm:min-h-[72px] lg:flex-row lg:items-stretch">
                    <p className="border-line text-ink-muted hidden items-center border-r pr-8 font-mono text-[12px] font-bold tracking-[0.12em] uppercase tabular-nums lg:flex">
                        {t('directory.count', {
                            count: String(listings.length),
                        })}
                    </p>
                    <label className="relative flex min-w-0 flex-1 items-center">
                        <span className="sr-only">{t('directory.search')}</span>
                        <div className="pl-4">
                            <Search className="text-ink-muted size-4" />
                        </div>
                        <input
                            type="search"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder={t('directory.search')}
                            className="text-ink placeholder:text-ink-muted min-h-[64px] w-full bg-transparent pr-4 pl-4 text-[15px] outline-none sm:min-h-[72px]"
                        />
                    </label>
                    <button
                        type="button"
                        className="border-line text-ink-muted flex min-h-12 flex-wrap items-center gap-2.5 border-t py-3 text-sm font-medium lg:min-h-[72px] lg:border-t-0 lg:border-l lg:py-0 lg:pl-8"
                        aria-expanded={filtersOpen}
                        onClick={() => setFiltersOpen((open) => !open)}
                    >
                        <svg
                            viewBox="0 0 16 16"
                            aria-hidden
                            className="size-4 fill-current"
                        >
                            <path d="M2 4.75A.75.75 0 0 1 2.75 4h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM4 8a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 4 8Zm2.25 3.25a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" />
                        </svg>
                        {t('directory.filters')}
                        {activeFilterCount > 0 && (
                            <span className="bg-brand-red inline-flex h-5 min-w-5 items-center justify-center px-1.5 font-mono text-[11px] font-bold text-white">
                                {activeFilterCount}
                            </span>
                        )}
                        <span className="font-mono text-[12px] font-bold tracking-[0.12em] uppercase tabular-nums lg:hidden">
                            {t('directory.count', {
                                count: String(listings.length),
                            })}
                        </span>
                    </button>
                </Container>
                {filtersOpen && (
                    <Container className="border-line border-t py-5">
                        <FilterPills items={filters} />
                    </Container>
                )}
            </div>

            <div className="border-line bg-member-hatch border-b">
                <Container>
                    {visible.length === 0 ? (
                        <p className="text-ink-muted py-16 text-[15px]">
                            {listings.length === 0
                                ? t('directory.empty')
                                : t('directory.empty_search')}
                        </p>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                            {visible.map((listing) => {
                                const location = [
                                    listing.title,
                                    listing.company,
                                    listing.city,
                                ]
                                    .filter(Boolean)
                                    .join(' · ');

                                return (
                                    <Link
                                        key={listing.id}
                                        href={`/directory/${listing.slug}`}
                                        className="group border-line bg-paper flex min-w-0 flex-col border-r border-b transition-colors duration-[180ms] hover:bg-[#fafafb] max-sm:border-r-0 sm:even:max-lg:border-r-0 lg:[&:nth-child(4n)]:border-r-0"
                                    >
                                        <span className="flex h-[132px] items-center justify-center px-6 pt-6 pb-1.5">
                                            {listing.photo_url ? (
                                                <ProfileAvatar
                                                    src={listing.photo_url}
                                                    alt=""
                                                    className={
                                                        listing.kind ===
                                                        'company'
                                                            ? 'h-16 w-auto max-w-[75%] object-contain'
                                                            : 'size-24'
                                                    }
                                                />
                                            ) : (
                                                <span className="text-brand-green text-2xl font-semibold tracking-tight">
                                                    {initials(listing.name)}
                                                </span>
                                            )}
                                        </span>
                                        <span className="flex min-h-[90px] min-w-0 flex-col px-6 pt-5 pb-[22px]">
                                            <span className="text-ink group-hover:text-brand-red text-base leading-[1.3] font-semibold break-words transition-colors duration-[180ms]">
                                                {listing.name}
                                            </span>
                                            {location && (
                                                <span className="text-brand-red mt-1.5 font-mono text-[11px] font-bold tracking-[0.1em] break-words uppercase">
                                                    {location}
                                                </span>
                                            )}
                                        </span>
                                    </Link>
                                );
                            })}
                        </div>
                    )}
                </Container>
            </div>

            <Section tone="canvas">
                <Container className="grid gap-8 py-16 sm:py-20 lg:grid-cols-2 lg:items-center lg:gap-16">
                    <Display as="h2">{t('directory.cta.title')}</Display>
                    <div>
                        <p className="text-ink-muted max-w-xl text-[15px] leading-[1.6]">
                            {t('directory.cta.lead')}
                        </p>
                        <div className={`${actionRowClass} mt-6`}>
                            <Button href={profileHref}>
                                {t('directory.cta.button')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </Section>
        </>
    );
}
