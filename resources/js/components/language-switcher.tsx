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
    segmented = false,
}: {
    className?: string;
    bare?: boolean;
    segmented?: boolean;
}) {
    const { locale, locales } = usePage().props;
    const t = useTrans();

    if (segmented) {
        return (
            <div
                className={cn(
                    'font-jetbrains flex items-center rounded-[0.25rem] bg-[#eeeeed] p-0.5 text-[13px]',
                    className,
                )}
            >
                {Object.entries(locales).map(([value, label]) => {
                    const selected = value === locale;

                    return (
                        <button
                            key={value}
                            type="button"
                            aria-pressed={selected}
                            aria-label={`${t('nav.language')}: ${label}`}
                            className={cn(
                                'rounded-[0.125rem] px-2 py-1 tracking-wide uppercase transition-colors',
                                selected
                                    ? 'bg-white font-bold text-[#1a1c1c] shadow-sm'
                                    : 'text-[#5e3f3a] hover:text-[#1a1c1c]',
                            )}
                            onClick={() => {
                                if (selected) {
                                    return;
                                }

                                router.post(
                                    '/locale',
                                    { locale: value },
                                    { preserveScroll: true },
                                );
                            }}
                        >
                            {value}
                        </button>
                    );
                })}
            </div>
        );
    }

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
