import {
    Combobox,
    ComboboxButton,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
} from '@headlessui/react';
import { useState } from 'react';
import {
    Check,
    Chevron,
    controlClass,
    controlClusterClass,
    menuItemClass,
    menuPanelClass,
} from '@/components/design';
import type { FieldOption } from '@/components/field-select';
import { cn } from '@/lib/utils';

/**
 * A FieldSelect the viewer can type into to narrow a long option list. The
 * query matches an option's label or its value.
 */
export function FieldCombobox({
    name,
    options,
    defaultValue,
    onChange,
    className = '',
}: {
    name?: string;
    options: FieldOption[];
    defaultValue?: string;
    onChange?: (value: string) => void;
    className?: string;
}) {
    const [value, setValue] = useState(
        defaultValue && options.some((option) => option.value === defaultValue)
            ? defaultValue
            : (options[0]?.value ?? ''),
    );
    const [query, setQuery] = useState('');
    const needle = query.trim().toLowerCase();
    const filtered =
        needle === ''
            ? options
            : options.filter(
                  (option) =>
                      option.label.toLowerCase().includes(needle) ||
                      option.value.toLowerCase() === needle,
              );

    return (
        <div className={cn(controlClusterClass, 'w-full min-w-40', className)}>
            <Combobox
                name={name}
                value={value}
                onChange={(next: string | null) => {
                    if (next === null) {
                        return;
                    }

                    setValue(next);
                    onChange?.(next);
                }}
                onClose={() => setQuery('')}
            >
                <div className="relative w-full">
                    <ComboboxInput
                        className={cn(controlClass, 'w-full pr-9')}
                        displayValue={(current: string) =>
                            options.find((option) => option.value === current)
                                ?.label ?? ''
                        }
                        onChange={(event) => setQuery(event.target.value)}
                        onFocus={(event) => event.target.select()}
                        autoComplete="off"
                    />
                    <ComboboxButton className="group absolute inset-y-0 right-0 flex items-center px-3">
                        <Chevron />
                    </ComboboxButton>
                </div>
                <ComboboxOptions
                    transition
                    anchor="bottom start"
                    className={cn(
                        menuPanelClass,
                        'max-h-72 w-[var(--input-width)] overflow-y-auto empty:invisible',
                    )}
                >
                    {filtered.map((option) => (
                        <ComboboxOption
                            key={option.value}
                            value={option.value}
                            className={cn(
                                menuItemClass,
                                'cursor-default justify-between',
                            )}
                        >
                            {({ selected }) => (
                                <>
                                    <span
                                        className={cn(
                                            'truncate',
                                            selected && 'font-medium',
                                        )}
                                    >
                                        {option.label}
                                    </span>
                                    <Check
                                        className={cn(
                                            'text-brand-green',
                                            selected
                                                ? 'opacity-100'
                                                : 'opacity-0',
                                        )}
                                    />
                                </>
                            )}
                        </ComboboxOption>
                    ))}
                </ComboboxOptions>
            </Combobox>
        </div>
    );
}
