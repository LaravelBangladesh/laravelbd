import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { AccountMenu } from '@/components/account-menu';
import { BrandLogo } from '@/components/brand-logo';
import { Link } from '@/components/catalyst/link';
import { BrandBar, Button, Container } from '@/components/design';
import { LanguageSwitcher } from '@/components/language-switcher';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

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

export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth, version } = usePage().props;
    const url = usePage().url;
    const t = useTrans();
    const user = auth.user;
    const [menuOpen, setMenuOpen] = useState(false);

    useEffect(() => {
        setMenuOpen(false);
    }, [url]);

    return (
        <div className="bg-paper text-ink min-h-dvh overflow-x-hidden">
            <header className="border-line/70 bg-paper/90 sticky top-0 z-40 border-b backdrop-blur-xl">
                <Container className="flex h-16 min-w-0 items-center gap-2 sm:h-[4.25rem] sm:gap-6">
                    <BrandLogo />
                    <nav className="hidden items-center gap-6 xl:flex">
                        {mainNav.map((item) => {
                            const current = item.match(url);

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={cn(
                                        'text-base font-semibold transition-colors',
                                        current
                                            ? 'text-brand-red'
                                            : 'text-ink hover:text-brand-red',
                                    )}
                                >
                                    {t(item.key)}
                                </Link>
                            );
                        })}
                    </nav>
                    <div className="ml-auto flex min-w-0 items-center gap-2 sm:gap-3">
                        <div className="hidden sm:block">
                            <LanguageSwitcher />
                        </div>
                        {user ? (
                            <AccountMenu />
                        ) : (
                            <Button
                                href="/login"
                                className="hidden sm:inline-flex"
                            >
                                {t('nav.login')}
                            </Button>
                        )}
                        <button
                            type="button"
                            className="border-line text-ink inline-flex size-10 items-center justify-center rounded-none border xl:hidden"
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
                </Container>
                {menuOpen && (
                    <Container className="border-line grid gap-1 border-t py-3 xl:hidden">
                        {mainNav.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={cn(
                                    'rounded-none px-3 py-2 text-base font-semibold',
                                    item.match(url)
                                        ? 'text-brand-red'
                                        : 'text-ink hover:text-brand-red',
                                )}
                            >
                                {t(item.key)}
                            </Link>
                        ))}
                        {!user && (
                            <Link
                                href="/login"
                                className="text-brand-red rounded-none px-3 py-2 text-base font-semibold sm:hidden"
                            >
                                {t('nav.login')}
                            </Link>
                        )}
                        <div className="px-3 py-2 sm:hidden">
                            <LanguageSwitcher />
                        </div>
                    </Container>
                )}
            </header>
            <main>{children}</main>
            <footer className="bg-ink text-white">
                <BrandBar />
                <Container className="grid gap-12 py-14 sm:grid-cols-2 lg:grid-cols-[1.5fr_repeat(3,1fr)] lg:py-16">
                    <div className="max-w-sm">
                        <BrandLogo onDark />
                        <p className="mt-4 text-sm leading-6 text-white/65">
                            {t('footer.tagline')}
                        </p>
                        <a
                            href="https://www.facebook.com/groups/laravelbangladesh"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-4 inline-block text-sm text-white/65 transition-colors hover:text-white"
                        >
                            {t('footer.facebook')}
                        </a>
                    </div>
                    <FooterColumn
                        title={t('footer.community')}
                        links={mainNav.slice(0, 2)}
                        t={t}
                    />
                    <FooterColumn
                        title={t('footer.attend')}
                        links={mainNav.slice(2, 3)}
                        t={t}
                    />
                    <FooterColumn
                        title={t('footer.resources')}
                        links={mainNav.slice(3)}
                        t={t}
                    />
                </Container>
                <div className="border-t border-white/10">
                    <Container className="flex flex-col gap-2 py-5 text-sm text-white/45 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <p>{t('footer.copyright')}</p>
                            <p className="text-xs">{t('footer.credit')}</p>
                        </div>
                        <p className="flex items-center gap-3">
                            <span>{t('footer.since')}</span>
                            <span className="font-mono text-xs text-white/35">
                                v{version}
                            </span>
                        </p>
                    </Container>
                    <Container className="pb-6">
                        <p className="max-w-3xl text-xs text-white/35">
                            {t('footer.trademark')}
                        </p>
                    </Container>
                </div>
            </footer>
        </div>
    );
}

function FooterColumn({
    title,
    links,
    t,
}: {
    title: string;
    links: readonly { href: string; key: string }[];
    t: (key: string) => string;
}) {
    return (
        <div>
            <p className="text-sm font-medium text-white">{title}</p>
            <ul className="mt-4 space-y-2">
                {links.map((item) => (
                    <li key={item.href}>
                        <Link
                            href={item.href}
                            className="text-sm text-white/65 transition-colors hover:text-white"
                        >
                            {t(item.key)}
                        </Link>
                    </li>
                ))}
            </ul>
        </div>
    );
}
