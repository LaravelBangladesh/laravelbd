import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    Chevron,
    controlClass,
    menuItemClass,
    menuPanelClass,
} from '@/components/design';
import { ProfileAvatar } from '@/components/profile-avatar';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export function AccountMenu() {
    const { auth } = usePage().props;
    const t = useTrans();
    const user = auth.user;

    if (!user) {
        return null;
    }

    return (
        <Menu>
            <MenuButton
                className={cn(
                    controlClass,
                    'group sm:max-w-52',
                    'px-1.5 sm:px-3',
                )}
            >
                <ProfileAvatar
                    src={user.photo_url}
                    alt={t('nav.profile_photo')}
                    className="size-7"
                />
                <span className="hidden truncate sm:inline">{user.name}</span>
                <Chevron className="hidden sm:block" />
            </MenuButton>
            <MenuItems
                transition
                anchor="bottom end"
                className={cn(menuPanelClass, 'w-56')}
            >
                <div className="border-line flex items-center gap-3 border-b px-3 py-2.5">
                    <ProfileAvatar
                        src={user.photo_url}
                        alt=""
                        className="size-9"
                    />
                    <div className="min-w-0">
                        <p className="text-ink truncate text-sm font-medium">
                            {user.name}
                        </p>
                        <p className="text-ink-muted truncate text-xs">
                            {user.email}
                        </p>
                    </div>
                </div>
                <div className="py-1">
                    <MenuItem>
                        <Link href="/account" className={menuItemClass}>
                            {t('nav.account')}
                        </Link>
                    </MenuItem>
                    {user.is_staff && (
                        <MenuItem>
                            <Link href="/admin" className={menuItemClass}>
                                {t('nav.admin')}
                            </Link>
                        </MenuItem>
                    )}
                </div>
                <div className="border-line border-t py-1">
                    <MenuItem>
                        <button
                            type="button"
                            className={cn(menuItemClass, 'text-ink-muted')}
                            onClick={() => router.post('/logout')}
                        >
                            {t('nav.logout')}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </Menu>
    );
}
