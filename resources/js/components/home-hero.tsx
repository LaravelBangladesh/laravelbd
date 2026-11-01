import { usePage } from '@inertiajs/react';
import { useSyncExternalStore } from 'react';
import { Link } from '@/components/catalyst/link';
import { Button } from '@/components/design';
import { HeroMonument } from '@/components/hero-monument';
import { useTrans } from '@/lib/i18n';

export type FeaturedEvent = {
    slug: string;
    title: string;
    starts_at: string | null;
    starts_at_iso: string | null;
    venue_name: string | null;
    is_upcoming: boolean;
};

const founded = 2012;

export function HomeHero({
    featuredEvent,
    counts,
}: {
    featuredEvent: FeaturedEvent | null;
    counts: {
        events: number;
        meetups: number;
        cities: number;
    };
}) {
    const t = useTrans();
    const { locale } = usePage().props;
    const currentYear = new Date().getFullYear();
    const tiles = [
        {
            value: t('home.hero.stat_artisans_value'),
            label: t('home.hero.stat_artisans'),
            tone: 'text-brand-red',
        },
        {
            value: t('home.hero.stat_heritage_value', {
                years: localizeDigits(currentYear - founded, locale),
            }),
            label: t('home.hero.stat_heritage', {
                year: localizeDigits(String(currentYear).slice(-2), locale),
            }),
            tone: 'text-[#046c50]',
        },
        {
            value: localizeDigits(counts.events, locale),
            label: t('home.hero.stat_meetups'),
            tone: 'text-[#1a1c1c]',
        },
        {
            value: localizeDigits(counts.cities, locale),
            label: t('home.hero.stat_cities'),
            tone: 'text-[#046c50]',
        },
    ];

    return (
        <div className="font-jakarta relative flex w-full flex-col overflow-hidden bg-[#f9f9f8] text-[#1a1c1c]">
            <div className="pointer-events-none absolute inset-0 opacity-40 select-none">
                <svg
                    className="h-full w-full"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <pattern
                            id="arch-grid"
                            width="48"
                            height="48"
                            patternUnits="userSpaceOnUse"
                        >
                            <path
                                d="M 48 0 L 0 0 0 48"
                                fill="none"
                                stroke="#046c50"
                                strokeOpacity="0.07"
                                strokeWidth="0.75"
                            />
                            <circle
                                cx="24"
                                cy="24"
                                r="0.75"
                                fill="#bc0003"
                                fillOpacity="0.15"
                            />
                            <path
                                d="M 22 24 L 26 24 M 24 22 L 24 26"
                                stroke="#046c50"
                                strokeOpacity="0.15"
                                strokeWidth="0.5"
                            />
                        </pattern>
                        <radialGradient
                            id="hero-glow"
                            cx="65%"
                            cy="40%"
                            r="50%"
                        >
                            <stop
                                offset="0%"
                                stopColor="#9ef4d0"
                                stopOpacity="0.25"
                            />
                            <stop
                                offset="50%"
                                stopColor="#ffdad5"
                                stopOpacity="0.12"
                            />
                            <stop
                                offset="100%"
                                stopColor="#f9f9f8"
                                stopOpacity="0"
                            />
                        </radialGradient>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#arch-grid)" />
                    <rect width="100%" height="100%" fill="url(#hero-glow)" />
                </svg>
            </div>

            <section className="relative z-10 mx-auto w-full max-w-7xl px-6 pt-10 pb-20 lg:px-12">
                <div className="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">
                    <div className="flex flex-col items-start gap-6 lg:col-span-7">
                        <div className="inline-flex max-w-full flex-wrap items-center gap-2.5 rounded-[999px] bg-[#f3f4f3] px-3 py-1.5 shadow-sm">
                            <span className="relative flex size-2.5 shrink-0">
                                <span className="absolute inline-flex size-full animate-ping rounded-full bg-[#046c50] opacity-75" />
                                <span className="relative inline-flex size-2.5 rounded-full bg-[#046c50]" />
                            </span>
                            {featuredEvent?.is_upcoming ? (
                                <EventCountdown event={featuredEvent} />
                            ) : (
                                <>
                                    <span className="font-jetbrains text-[12px] font-bold tracking-wider text-[#046c50] uppercase">
                                        {t('home.hero.eyebrow_mark')}
                                    </span>
                                    <span className="size-1 shrink-0 rounded-full bg-[#e8bcb6]" />
                                    <span className="font-jetbrains text-[12px] font-medium tracking-widest text-[#5e3f3a] uppercase">
                                        {t('home.hero.eyebrow_meta')}
                                    </span>
                                </>
                            )}
                        </div>

                        <h1 className="max-w-2xl text-[36px] leading-[44px] font-extrabold tracking-[-0.02em] text-[#1a1c1c] lg:text-[56px] lg:leading-[64px] lg:tracking-[-0.03em]">
                            {t('home.hero.title_before')}
                            <span className="relative inline-block font-extrabold text-[#046c50]">
                                {t('home.hero.title_highlight')}
                                <svg
                                    viewBox="0 0 100 8"
                                    preserveAspectRatio="none"
                                    aria-hidden
                                    className="absolute -bottom-1.5 left-0 h-2 w-full text-[#046c50]/30"
                                >
                                    <path
                                        d="M0,5 Q50,0 100,5"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2.5"
                                    />
                                </svg>
                            </span>
                            {t('home.hero.title_after')}
                        </h1>

                        <p className="max-w-xl text-[18px] leading-[28px] font-normal text-[#5e3f3a]">
                            {t('home.hero.lead_before')}
                            <strong className="font-semibold text-[#1a1c1c]">
                                {t('home.hero.lead_strong')}
                            </strong>
                            {t('home.hero.lead_after')}
                        </p>

                        <div className="mt-1 w-full max-w-xl overflow-hidden rounded-[0.25rem] bg-[#2f3130] shadow-xl">
                            <div className="flex items-center justify-between bg-[#1a1c1c]/90 px-4 py-2">
                                <div className="flex min-w-0 items-center gap-1.5">
                                    <span className="size-2.5 rounded-full bg-[#ff5f56]" />
                                    <span className="size-2.5 rounded-full bg-[#ffbd2e]" />
                                    <span className="size-2.5 rounded-full bg-[#27c93f]" />
                                    <span className="font-jetbrains ml-2 truncate text-[11px] text-[#dadad9]">
                                        {t('home.hero.terminal_prompt')}
                                    </span>
                                </div>
                                <span className="font-jetbrains flex items-center gap-1 text-[11px] tracking-widest text-[#9ef4d0] uppercase">
                                    <span className="size-1.5 rounded-full bg-[#9ef4d0]" />
                                    {t('home.hero.terminal_live')}
                                </span>
                            </div>
                            <div className="font-jetbrains space-y-1.5 bg-[#0F172A] p-4 text-[13px] leading-[18px] text-[#fefcff]">
                                <div className="flex items-center gap-2 text-[#9ef4d0]">
                                    <span className="font-bold text-[#bc0003]">
                                        $
                                    </span>
                                    <span className="text-white">
                                        {t('home.hero.terminal_command')}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 pl-3 text-[13px] text-[#94A3B8]">
                                    <span className="text-[#046c50]">→</span>
                                    <span>
                                        {t('home.hero.terminal_connected', {
                                            meetups: String(counts.meetups),
                                            cities: String(counts.cities),
                                        })}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 pl-3 text-[12px] text-[#64748B]">
                                    <span className="inline-block h-3.5 w-2 animate-pulse bg-[#bc0003]" />
                                    <span className="italic">
                                        {t('home.hero.terminal_ready')}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-4 pt-2">
                            <Button href="/login">{t('home.hero.join')}</Button>
                            <Button href="/events" variant="outline">
                                {t('home.hero.explore')}
                            </Button>
                        </div>

                        <div className="mt-2 grid w-full grid-cols-2 gap-4 pt-6 sm:grid-cols-4">
                            {tiles.map((tile) => (
                                <div
                                    key={tile.label}
                                    className="rounded-[0.125rem] bg-[#f3f4f3] p-3"
                                >
                                    <div
                                        className={`text-[24px] leading-[32px] font-bold tracking-[-0.015em] ${tile.tone}`}
                                    >
                                        {tile.value}
                                    </div>
                                    <div className="font-jetbrains mt-0.5 text-[11px] font-medium tracking-wide text-[#5e3f3a] uppercase">
                                        {tile.label}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="relative flex min-h-[500px] items-center justify-center p-4 lg:col-span-5 lg:min-h-[580px]">
                        <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                            <div className="size-[360px] rounded-full bg-[#9ef4d0]/30 blur-3xl" />
                            <div className="-mt-16 ml-12 size-[280px] rounded-full bg-[#ffdad5]/30 blur-2xl" />
                        </div>
                        <div className="relative w-full max-w-[460px]">
                            <HeroMonument />
                            {featuredEvent && (
                                <Link
                                    href={`/events/${featuredEvent.slug}`}
                                    className="absolute -top-3 -right-2 flex items-center gap-2.5 rounded-[0.25rem] bg-white/95 px-3.5 py-2 shadow-lg backdrop-blur-md transition-transform hover:-translate-y-0.5 sm:right-0"
                                >
                                    <span className="relative flex size-2 shrink-0">
                                        <span className="absolute inline-flex size-full animate-ping rounded-full bg-[#bc0003] opacity-75" />
                                        <span className="relative inline-flex size-2 rounded-full bg-[#bc0003]" />
                                    </span>
                                    <span className="flex max-w-[16rem] flex-col">
                                        <span className="font-jetbrains text-[10px] font-bold tracking-wider text-[#5e3f3a] uppercase">
                                            {featuredEvent.is_upcoming
                                                ? t('home.hero.next')
                                                : t('home.hero.last')}
                                        </span>
                                        <span className="text-[13px] leading-tight font-bold text-[#1a1c1c]">
                                            {featuredEvent.title}
                                        </span>
                                        {(featuredEvent.starts_at ||
                                            featuredEvent.venue_name) && (
                                            <span className="text-[11px] leading-tight font-medium text-[#5e3f3a]">
                                                {[
                                                    featuredEvent.starts_at,
                                                    featuredEvent.venue_name,
                                                ]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </span>
                                        )}
                                    </span>
                                </Link>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            <div className="flex h-[3px]" aria-hidden>
                <span className="w-[82%] bg-[#046c50]" />
                <span className="w-[18%] bg-[#bc0003]" />
            </div>
        </div>
    );
}

function localizeDigits(value: number | string, locale: string) {
    const text = String(value);

    if (locale !== 'bn') {
        return text;
    }

    return text.replace(/\d/g, (digit) => '০১২৩৪৫৬৭৮৯'[Number(digit)]);
}

let countdownNow = Date.now();

function subscribeToClock(onStoreChange: () => void) {
    countdownNow = Date.now();
    const id = window.setInterval(() => {
        countdownNow = Date.now();
        onStoreChange();
    }, 1000);

    return () => window.clearInterval(id);
}

function EventCountdown({ event }: { event: FeaturedEvent }) {
    const t = useTrans();
    const now = useSyncExternalStore(
        subscribeToClock,
        () => countdownNow,
        () => null,
    );
    const target = event.starts_at_iso
        ? new Date(event.starts_at_iso).getTime()
        : Number.NaN;
    const remaining =
        now === null || Number.isNaN(target) ? null : target - now;

    return (
        <>
            <span className="font-jetbrains text-[12px] font-bold tracking-wider text-[#046c50] uppercase">
                {event.title}
            </span>
            <span className="size-1 shrink-0 rounded-full bg-[#e8bcb6]" />
            <span className="font-jetbrains text-[12px] font-medium tracking-widest text-[#5e3f3a] uppercase">
                {remaining === null
                    ? event.starts_at
                    : remaining <= 0
                      ? t('home.hero.countdown_now')
                      : formatRemaining(remaining)}
            </span>
        </>
    );
}

function formatRemaining(ms: number) {
    const total = Math.floor(ms / 1000);
    const days = Math.floor(total / 86400);
    const hours = Math.floor((total % 86400) / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = total % 60;
    const pad = (value: number) => String(value).padStart(2, '0');
    const parts = days > 0 ? [`${days}d`] : [];

    parts.push(`${pad(hours)}h`, `${pad(minutes)}m`, `${pad(seconds)}s`);

    return parts.join(' ');
}
