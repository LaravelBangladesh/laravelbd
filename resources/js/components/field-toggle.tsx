import { Switch } from '@headlessui/react';
import { useEffect, useRef, useState } from 'react';
import { FieldError } from '@/components/field-error';
import { cn } from '@/lib/utils';

export function FieldToggle({
    name,
    label,
    description,
    defaultChecked = false,
    onChange,
    error,
    className = '',
}: {
    name?: string;
    label: string;
    description?: string;
    defaultChecked?: boolean;
    onChange?: (checked: boolean) => void;
    error?: string;
    className?: string;
}) {
    const [checked, setChecked] = useState(defaultChecked);
    const hiddenRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        hiddenRef.current?.dispatchEvent(new Event('input', { bubbles: true }));
    }, [checked]);

    return (
        <div className={cn('w-full', className)}>
            {name && (
                <input
                    ref={hiddenRef}
                    type="hidden"
                    name={name}
                    value={checked ? '1' : '0'}
                />
            )}
            <div className="flex items-start gap-3">
                <Switch
                    checked={checked}
                    onChange={(next) => {
                        setChecked(next);
                        onChange?.(next);
                    }}
                    className={cn(
                        'focus-visible:outline-brand-red relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-none border transition-colors focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2',
                        checked
                            ? 'border-brand-red bg-brand-red'
                            : 'border-line bg-paper',
                    )}
                >
                    <span className="sr-only">{label}</span>
                    <span
                        aria-hidden
                        className={cn(
                            'bg-paper border-line pointer-events-none inline-block size-4 rounded-none border transition-transform',
                            checked ? 'translate-x-6' : 'translate-x-1',
                        )}
                    />
                </Switch>
                <span className="min-w-0">
                    <span className="text-ink block text-sm font-medium">
                        {label}
                    </span>
                    {description && (
                        <span className="text-ink-muted block text-sm">
                            {description}
                        </span>
                    )}
                </span>
            </div>
            <FieldError error={error} />
        </div>
    );
}
