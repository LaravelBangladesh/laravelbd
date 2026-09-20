import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/react';
import { router, usePage } from '@inertiajs/react';
import {
    Check,
    Chevron,
    controlClass,
    controlClusterClass,
    menuItemClass,
    menuPanelClass,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export function LanguageSwitcher({
    className = '',
    bare = false,
}: {
    className?: string;
    bare?: boolean;
}) {
    const { locale, locales } = usePage().props;
    const t = useTrans();

    const field = (
        <Listbox
            value={locale}
            onChange={(value) => {
                if (value === locale) {
                    return;
                }

                router.post(
                    '/locale',
                    { locale: value },
                    { preserveScroll: true },
                );
            }}
        >
            <ListboxButton
                aria-label={t('nav.language')}
                className={cn(
                    controlClass,
                    'group min-w-16 justify-between',
                    className,
                )}
            >
                <span className="font-medium tracking-[0.08em] uppercase">
                    {locale}
                </span>
                <Chevron />
            </ListboxButton>
            <ListboxOptions
                transition
                anchor="bottom end"
                className={cn(menuPanelClass, 'min-w-48')}
            >
                {Object.entries(locales).map(([value, label]) => (
                    <ListboxOption
                        key={value}
                        value={value}
                        className={cn(
                            menuItemClass,
                            'cursor-default justify-between',
                        )}
                    >
                        {({ selected }) => (
                            <>
                                <span className="flex min-w-0 flex-col">
                                    <span
                                        className={cn(
                                            'truncate',
                                            selected && 'font-medium',
                                        )}
                                    >
                                        {label}
                                    </span>
                                    <span className="text-ink-muted text-[11px] tracking-[0.08em] uppercase">
                                        {value}
                                    </span>
                                </span>
                                <Check
                                    className={cn(
                                        'text-brand-green',
                                        selected ? 'opacity-100' : 'opacity-0',
                                    )}
                                />
                            </>
                        )}
                    </ListboxOption>
                ))}
            </ListboxOptions>
        </Listbox>
    );

    if (bare) {
        return field;
    }

    return <div className={controlClusterClass}>{field}</div>;
}
