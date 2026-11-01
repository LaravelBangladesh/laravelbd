import { Link } from '@/components/catalyst/link';
import { FooterMemorial } from '@/components/footer-memorial';
import { useTrans } from '@/lib/i18n';

const facebookGroup = 'https://www.facebook.com/groups/laravelbangladesh';

const columns = [
    {
        title: 'footer.community',
        links: [
            { href: '/', key: 'nav.home' },
            { href: '/about', key: 'nav.about' },
        ],
    },
    {
        title: 'footer.attend',
        links: [{ href: '/events', key: 'nav.events' }],
    },
    {
        title: 'footer.resources',
        links: [
            { href: '/resources', key: 'nav.resources' },
            { href: '/directory', key: 'nav.directory' },
        ],
    },
] as const;

export function SiteFooter() {
    const t = useTrans();

    return (
        <footer className="relative mt-20 w-full overflow-hidden bg-[#090D11] text-slate-300">
            <div className="flex h-[2px] w-full" aria-hidden>
                <span className="w-2/3 bg-[#006A4E]" />
                <span className="w-1/3 bg-[#FF2D20]" />
            </div>
            <div className="relative z-10 mx-auto flex max-w-7xl flex-col px-6 pt-14 pb-12 lg:px-12">
                <div className="grid grid-cols-1 items-center gap-10 border-b border-slate-800/80 pb-12 lg:grid-cols-12 lg:gap-14">
                    <div className="flex flex-col items-start text-left lg:col-span-6">
                        <div className="font-jetbrains mb-4 inline-flex items-center gap-2 rounded-[0.125rem] border border-[#006A4E]/30 bg-[#006A4E]/15 px-3 py-1 text-xs text-[#9ef4d0]">
                            <span className="size-1.5 animate-pulse rounded-full bg-[#006A4E]" />
                            <span>{t('footer.eyebrow')}</span>
                        </div>
                        <h2 className="mb-4 text-3xl leading-tight font-extrabold tracking-tight text-white sm:text-4xl">
                            {t('footer.headline')}
                        </h2>
                        <p className="mb-7 max-w-xl text-base leading-relaxed font-normal text-slate-400">
                            {t('footer.lead')}
                        </p>
                        <a
                            href={facebookGroup}
                            target="_blank"
                            rel="noreferrer"
                            className="btn-offset group/btn focus-visible:outline-brand-red relative inline-flex h-11 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-4"
                        >
                            <span className="border-brand-red bg-brand-red relative z-[2] flex h-full w-full items-center justify-center gap-2 rounded-none border-[1.5px] px-5 text-xs font-bold tracking-[0.12em] text-white uppercase transition-[transform,background-color,color,border-color] duration-200 ease-[cubic-bezier(0.4,0,0.2,1)] motion-safe:max-lg:active:translate-y-0.5 motion-safe:lg:group-hover/btn:-translate-x-1 motion-safe:lg:group-hover/btn:-translate-y-1">
                                {t('footer.facebook')}
                            </span>
                            <span
                                aria-hidden
                                className="border-brand-red absolute inset-0 rounded-none border-[1.5px]"
                            />
                        </a>
                    </div>
                    <div className="flex min-h-[300px] items-center justify-center select-none sm:min-h-[340px] lg:col-span-6">
                        <FooterMemorial />
                    </div>
                </div>

                <div className="grid grid-cols-1 items-start gap-10 border-b border-slate-800/80 py-12 lg:grid-cols-12">
                    <div className="flex flex-col items-start gap-4 lg:col-span-5">
                        <div className="flex items-center gap-3">
                            <div className="flex size-10 shrink-0 items-center justify-center rounded-[0.25rem] border border-[#FF2D20]/30 bg-[#FF2D20]/10 text-[#FF2D20]">
                                <LaravelMark />
                            </div>
                            <div>
                                <div className="flex items-baseline text-xl font-bold tracking-tight">
                                    <span className="text-white">Laravel</span>
                                    <span className="ml-1 text-[#006A4E]">
                                        Bangladesh
                                    </span>
                                </div>
                                <span className="font-jetbrains text-[11px] tracking-wider text-slate-400 uppercase">
                                    {t('footer.guild')}
                                </span>
                            </div>
                        </div>
                        <p className="max-w-sm text-sm leading-relaxed text-slate-400">
                            {t('footer.hub')}
                        </p>
                    </div>
                    <div className="grid grid-cols-2 gap-8 sm:grid-cols-4 lg:col-span-7">
                        {columns.map((column) => (
                            <div
                                key={column.title}
                                className="flex flex-col gap-3"
                            >
                                <span className="font-jetbrains text-xs font-bold tracking-wider text-slate-200 uppercase">
                                    {t(column.title)}
                                </span>
                                <ul className="flex flex-col gap-2 text-sm text-slate-400">
                                    {column.links.map((item) => (
                                        <li key={item.href}>
                                            <Link
                                                href={item.href}
                                                className="transition-colors hover:text-white"
                                            >
                                                {t(item.key)}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                        <div className="flex flex-col gap-3">
                            <span className="font-jetbrains text-xs font-bold tracking-wider text-slate-200 uppercase">
                                {t('footer.connect')}
                            </span>
                            <ul className="flex flex-col gap-2 text-sm text-slate-400">
                                <li>
                                    <a
                                        href={facebookGroup}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1 transition-colors hover:text-white"
                                    >
                                        <span>
                                            {t('footer.facebook_short')}
                                        </span>
                                        <NorthEastIcon />
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div className="font-jetbrains flex flex-col items-center gap-3.5 pt-8 text-center text-xs text-slate-500 sm:items-start sm:text-left">
                    <div className="flex w-full flex-col items-center justify-between gap-3 text-slate-400 sm:flex-row">
                        <div className="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 sm:justify-start">
                            <span>
                                {t('footer.copyright')} · {t('footer.since')}
                            </span>
                            <span className="opacity-40">•</span>
                            <Link
                                href="/terms"
                                className="transition-colors hover:text-slate-300"
                            >
                                {t('nav.terms')}
                            </Link>
                            <span className="opacity-40">•</span>
                            <Link
                                href="/privacy"
                                className="transition-colors hover:text-slate-300"
                            >
                                {t('nav.privacy')}
                            </Link>
                        </div>
                        <div className="text-[11px] text-slate-500">
                            {t('footer.credit')}
                        </div>
                    </div>
                    <p className="max-w-4xl text-[11px] leading-relaxed font-normal text-slate-500">
                        {t('footer.trademark')}
                    </p>
                </div>
            </div>
        </footer>
    );
}

function NorthEastIcon() {
    return (
        <svg
            viewBox="0 0 24 24"
            aria-hidden
            className="size-3.5 fill-none stroke-current stroke-2 opacity-70"
        >
            <path
                d="M7 17 17 7M9 7h8v8"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function LaravelMark() {
    return (
        <svg viewBox="0 0 50 52" aria-hidden className="size-6 fill-current">
            <path d="M48.71 13.5l-22.5-13a2.5 2.5 0 0 0-2.5 0l-22.5 13A2.5 2.5 0 0 0 0 15.66v26a2.5 2.5 0 0 0 1.21 2.16l22.5 13a2.5 2.5 0 0 0 2.5 0l22.5-13A2.5 2.5 0 0 0 50 41.66v-26a2.5 2.5 0 0 0-1.29-2.16zM25 4.88L43.83 15.75 37 19.69l-18.83-10.87zm-2 42.24L4.17 36.25V17.75L23 28.62zm2-13.62l-18.5-10.68 6.83-3.94 18.5 10.68zm19.83 2.75L26.17 47.12V28.62L44.83 17.75z" />
        </svg>
    );
}
