import { useEffect, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { AccountMenu } from '@/components/account-menu';
import { Link } from '@/components/catalyst/link';
import { LanguageSwitcher } from '@/components/language-switcher';
import { SiteFooter } from '@/components/site-footer';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const facebookGroup = 'https://www.facebook.com/groups/laravelbangladesh';

const mainNav = [
    { href: '/', key: 'nav.home', match: (url: string) => url === '/' },
    {
        href: '/about',
        key: 'nav.about',
        match: (url: string) => url.startsWith('/about'),
    },
    {
        href: '/events',
        key: 'nav.events',
        match: (url: string) => url.startsWith('/events'),
    },
    {
        href: '/resources',
        key: 'nav.resources',
        match: (url: string) => url.startsWith('/resources'),
    },
    {
        href: '/directory',
        key: 'nav.directory',
        match: (url: string) => url.startsWith('/directory'),
    },
] as const;

const HEADER_SCROLL_RANGE = 120;

function smoothstep(value: number): number {
    const t = Math.min(1, Math.max(0, value));

    return t * t * (3 - 2 * t);
}

function headerProgress(scrollY: number, reduceMotion: boolean): number {
    if (reduceMotion) {
        return scrollY > 8 ? 1 : 0;
    }

    return smoothstep(scrollY / HEADER_SCROLL_RANGE);
}

export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth } = usePage().props;
    const url = usePage().url;
    const t = useTrans();
    const user = auth.user;
    const [menuOpen, setMenuOpen] = useState(false);
    const headerRef = useRef<HTMLElement>(null);
    const menuOpenRef = useRef(false);
    const progressRef = useRef(0);

    useEffect(() => {
        setMenuOpen(false);
    }, [url]);

    useEffect(() => {
        menuOpenRef.current = menuOpen;

        const next = menuOpen
            ? 0
            : headerProgress(
                  window.scrollY,
                  typeof window.matchMedia === 'function'
                      ? window.matchMedia('(prefers-reduced-motion: reduce)')
                            .matches
                      : false,
              );

        progressRef.current = next;
        headerRef.current?.style.setProperty('--header-p', String(next));
        headerRef.current?.toggleAttribute('data-scrolled', next > 0.5);
    }, [menuOpen]);

    useEffect(() => {
        const media =
            typeof window.matchMedia === 'function'
                ? window.matchMedia('(prefers-reduced-motion: reduce)')
                : null;

        const apply = (next: number) => {
            if (Math.abs(progressRef.current - next) < 0.001) {
                return;
            }

            progressRef.current = next;
            headerRef.current?.style.setProperty('--header-p', String(next));
            headerRef.current?.toggleAttribute('data-scrolled', next > 0.5);
        };

        const update = () => {
            apply(
                menuOpenRef.current
                    ? 0
                    : headerProgress(window.scrollY, media?.matches ?? false),
            );
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        media?.addEventListener('change', update);

        return () => {
            window.removeEventListener('scroll', update);
            media?.removeEventListener('change', update);
        };
    }, []);

    return (
        <div className="font-jakarta min-h-dvh overflow-x-hidden bg-[#f9f9f8] text-[#1a1c1c]">
            <header ref={headerRef} className="site-header">
                <div className="site-header__shell">
                    <div className="site-header__inner">
                        <div className="flex shrink-0 items-center gap-3">
                            <Link
                                href="/"
                                aria-label={t('app.name')}
                                className="flex items-baseline text-[20px] leading-[28px] font-semibold tracking-tight"
                            >
                                <span className="text-[#bc0003]">Laravel</span>
                                <span className="ml-1 text-[#046c50]">
                                    Bangladesh
                                </span>
                            </Link>
                        </div>
                        <nav className="hidden items-center gap-6 xl:flex">
                            {mainNav.map((item) => {
                                const current = item.match(url);

                                return (
                                    <Link
                                        key={item.key}
                                        href={item.href}
                                        aria-current={
                                            current ? 'page' : undefined
                                        }
                                        className={cn(
                                            'text-sm transition-colors',
                                            current
                                                ? 'font-semibold text-[#bc0003]'
                                                : 'font-medium text-[#5e3f3a] hover:text-[#1a1c1c]',
                                        )}
                                    >
                                        {t(item.key)}
                                    </Link>
                                );
                            })}
                        </nav>
                        <div className="flex shrink-0 items-center gap-3">
                            <LanguageSwitcher
                                segmented
                                className="hidden sm:flex"
                            />
                            {user ? (
                                <AccountMenu />
                            ) : (
                                <Link
                                    href="/login"
                                    className="hidden rounded-[0.125rem] px-3 py-2 text-sm text-[#5e3f3a] hover:bg-[#eeeeed] hover:text-[#1a1c1c] sm:inline-flex"
                                >
                                    {t('nav.login')}
                                </Link>
                            )}
                            <FacebookGroupButton label={t('nav.facebook')} />
                            <button
                                type="button"
                                className="inline-flex size-9 items-center justify-center rounded-[0.125rem] border border-[#e8bcb6]/40 text-[#1a1c1c] xl:hidden"
                                aria-expanded={menuOpen}
                                aria-label={t('nav.menu')}
                                onClick={() => setMenuOpen((open) => !open)}
                            >
                                <span className="sr-only">{t('nav.menu')}</span>
                                <svg
                                    viewBox="0 0 16 16"
                                    aria-hidden
                                    className="size-4 fill-current"
                                >
                                    {menuOpen ? (
                                        <path d="M3.2 3.2 8 8l4.8-4.8.8.8L8.8 8.8l4.8 4.8-.8.8L8 9.6l-4.8 4.8-.8-.8 4.8-4.8L2.4 4z" />
                                    ) : (
                                        <path d="M2 4h12v1.2H2zm0 3.4h12v1.2H2zm0 3.4h12V12H2z" />
                                    )}
                                </svg>
                            </button>
                        </div>
                    </div>
                    {menuOpen && (
                        <div className="grid gap-1 border-t border-[#e8bcb6]/40 px-3 py-3 sm:px-4 xl:hidden">
                            {mainNav.map((item) => (
                                <Link
                                    key={item.key}
                                    href={item.href}
                                    className={cn(
                                        'rounded-[0.125rem] px-3 py-2 text-base font-semibold',
                                        item.match(url)
                                            ? 'text-[#bc0003]'
                                            : 'text-[#1a1c1c] hover:text-[#bc0003]',
                                    )}
                                >
                                    {t(item.key)}
                                </Link>
                            ))}
                            {!user && (
                                <Link
                                    href="/login"
                                    className="rounded-[0.125rem] px-3 py-2 text-base font-semibold text-[#bc0003] sm:hidden"
                                >
                                    {t('nav.login')}
                                </Link>
                            )}
                            <a
                                href={facebookGroup}
                                target="_blank"
                                rel="noreferrer"
                                className="rounded-[0.125rem] px-3 py-2 text-base font-semibold text-[#1877f2]"
                            >
                                {t('nav.facebook')}
                            </a>
                            <div className="px-3 py-2 sm:hidden">
                                <LanguageSwitcher />
                            </div>
                        </div>
                    )}
                </div>
            </header>
            <main className="pt-[5.5rem]">{children}</main>
            <SiteFooter />
        </div>
    );
}

function FacebookGroupButton({ label }: { label: string }) {
    return (
        <a
            href={facebookGroup}
            target="_blank"
            rel="noreferrer"
            aria-label={label}
            className="group/fb inline-flex h-9 max-w-9 items-center overflow-hidden rounded-[0.25rem] border border-[#dbe1ea] bg-[#1877f2] text-white transition-[max-width,box-shadow,background-color] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:max-w-[12rem] hover:bg-[#166fe5] hover:shadow-[0_8px_20px_-10px_rgba(24,119,242,0.7)] focus-visible:max-w-[12rem] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1877f2]"
        >
            <span className="flex size-9 shrink-0 items-center justify-center">
                <FacebookIcon />
            </span>
            <span className="max-w-0 overflow-hidden pr-0 text-[13px] leading-none font-semibold whitespace-nowrap opacity-0 transition-[max-width,padding,opacity] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover/fb:max-w-[9rem] group-hover/fb:pr-3.5 group-hover/fb:opacity-100 group-focus-visible/fb:max-w-[9rem] group-focus-visible/fb:pr-3.5 group-focus-visible/fb:opacity-100">
                {label}
            </span>
        </a>
    );
}

function FacebookIcon() {
    return (
        <svg
            viewBox="0 0 24 24"
            aria-hidden
            className="size-[15px] fill-current"
        >
            <path d="M14.5 8.5V6.8c0-.7.1-1.1 1.1-1.1H17V3h-2.3C11.9 3 11 4.6 11 6.6v1.9H9v2.7h2V21h3.5v-9.8h2.3l.3-2.7z" />
        </svg>
    );
}
