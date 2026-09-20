import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/react';
import { useEffect, useRef, useState } from 'react';
import {
    Check,
    Chevron,
    controlClass,
    controlClusterClass,
    menuItemClass,
    menuPanelClass,
} from '@/components/design';
import { cn } from '@/lib/utils';

export type FieldOption = {
    value: string;
    label: string;
};

export function FieldSelect({
    name,
    options,
    defaultValue,
    onChange,
    required = false,
    placeholder,
    className = '',
}: {
    name?: string;
    options: FieldOption[];
    defaultValue?: string;
    onChange?: (value: string) => void;
    required?: boolean;
    placeholder?: string;
    className?: string;
}) {
    const initial =
        defaultValue && options.some((option) => option.value === defaultValue)
            ? defaultValue
            : (options[0]?.value ?? '');
    const [value, setValue] = useState(initial);
    const hiddenRef = useRef<HTMLInputElement>(null);
    const selected = options.find((option) => option.value === value);

    useEffect(() => {
        hiddenRef.current?.dispatchEvent(new Event('input', { bubbles: true }));
    }, [value]);

    return (
        <div className={cn(controlClusterClass, 'w-full min-w-40', className)}>
            {name && (
                <input
                    ref={hiddenRef}
                    type="hidden"
                    name={name}
                    value={value}
                    required={required}
                />
            )}
            <Listbox
                value={value}
                onChange={(next) => {
                    setValue(next);
                    onChange?.(next);
                }}
            >
                <ListboxButton
                    className={cn(controlClass, 'group w-full justify-between')}
                >
                    <span
                        className={cn(
                            'truncate',
                            !selected && 'text-ink-muted',
                        )}
                    >
                        {selected?.label ?? placeholder}
                    </span>
                    <Chevron />
                </ListboxButton>
                <ListboxOptions
                    transition
                    anchor="bottom start"
                    className={cn(
                        menuPanelClass,
                        'min-w-[var(--button-width)]',
                    )}
                >
                    {options.map((option) => (
                        <ListboxOption
                            key={option.value}
                            value={option.value}
                            className={cn(
                                menuItemClass,
                                'cursor-default justify-between',
                            )}
                        >
                            {({ selected: isSelected }) => (
                                <>
                                    <span
                                        className={cn(
                                            'truncate',
                                            isSelected && 'font-medium',
                                        )}
                                    >
                                        {option.label}
                                    </span>
                                    <Check
                                        className={cn(
                                            'text-brand-green',
                                            isSelected
                                                ? 'opacity-100'
                                                : 'opacity-0',
                                        )}
                                    />
                                </>
                            )}
                        </ListboxOption>
                    ))}
                </ListboxOptions>
            </Listbox>
        </div>
    );
}
