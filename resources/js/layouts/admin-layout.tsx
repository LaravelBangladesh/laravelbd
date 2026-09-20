import { router, usePage } from '@inertiajs/react';
import { BrandLogo } from '@/components/brand-logo';
import { BrandBar, Button } from '@/components/design';
import { LanguageSwitcher } from '@/components/language-switcher';
import { ProfileAvatar } from '@/components/profile-avatar';
import {
    Sidebar,
    SidebarBody,
    SidebarFooter,
    SidebarHeader,
    SidebarItem,
    SidebarLabel,
    SidebarSection,
} from '@/components/catalyst/sidebar';
import { SidebarLayout } from '@/components/catalyst/sidebar-layout';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const navItems: { href: string; key: string; exact?: boolean }[] = [
    { href: '/admin', key: 'admin.dashboard', exact: true },
    { href: '/admin/events', key: 'admin.events' },
    { href: '/admin/speakers', key: 'admin.speakers' },
    { href: '/admin/resources', key: 'admin.resources' },
    { href: '/admin/directory', key: 'admin.directory' },
    { href: '/admin/proposals', key: 'admin.proposals' },
    { href: '/admin/users', key: 'admin.users' },
    { href: '/account', key: 'nav.account' },
];

export default function AdminLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const t = useTrans();
    const page = usePage();
    const { auth, version } = page.props;
    const path = page.url.split('?')[0];

    return (
        <SidebarLayout
            navbar={
                <div className="flex items-center px-2 py-3 lg:hidden">
                    <BrandLogo />
                </div>
            }
            sidebar={
                <Sidebar className="bg-canvas">
                    <BrandBar />
                    <SidebarHeader>
                        <BrandLogo />
                    </SidebarHeader>
                    <SidebarBody>
                        <SidebarSection>
                            {navItems.map((item) => {
                                const current = item.exact
                                    ? path === item.href
                                    : path.startsWith(item.href);

                                return (
                                    <SidebarItem
                                        key={item.href}
                                        href={item.href}
                                        current={current}
                                        className={cn(
                                            current &&
                                                'border-brand-red border-l-2',
                                        )}
                                    >
                                        <SidebarLabel
                                            className={cn(
                                                current &&
                                                    'text-brand-red font-semibold',
                                            )}
                                        >
                                            {t(item.key)}
                                        </SidebarLabel>
                                    </SidebarItem>
                                );
                            })}
                        </SidebarSection>
                    </SidebarBody>
                    <SidebarFooter>
                        <LanguageSwitcher />
                        <div className="mt-3 flex min-w-0 items-center gap-2 px-2">
                            <ProfileAvatar
                                src={auth.user?.photo_url}
                                alt=""
                                className="size-8"
                            />
                            <p className="text-ink-muted truncate text-sm">
                                {auth.user?.name}
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            className="h-auto justify-start px-2"
                            onClick={() => router.post('/logout')}
                        >
                            {t('nav.logout')}
                        </Button>
                        <p className="text-ink-muted/70 px-2 font-mono text-xs">
                            v{version}
                        </p>
                    </SidebarFooter>
                </Sidebar>
            }
        >
            {children}
        </SidebarLayout>
    );
}
