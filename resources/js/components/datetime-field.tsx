import { useEffect, useRef, useState } from 'react';
import { controlClusterClass, fieldInputClass } from '@/components/design';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

function splitDateTime(value?: string): { date: string; time: string } {
    if (!value) {
        return { date: '', time: '' };
    }

    const [date = '', time = ''] = value.split('T');

    return { date, time: time.slice(0, 5) };
}

function combineDateTime(date: string, time: string): string {
    if (!date || !time) {
        return '';
    }

    return `${date}T${time}`;
}

export function DateTimeField({
    name,
    defaultValue = '',
    required = false,
    className = '',
}: {
    name: string;
    defaultValue?: string;
    required?: boolean;
    className?: string;
}) {
    const t = useTrans();
    const hiddenRef = useRef<HTMLInputElement>(null);
    const initial = splitDateTime(defaultValue);
    const [date, setDate] = useState(initial.date);
    const [time, setTime] = useState(initial.time);

    useEffect(() => {
        hiddenRef.current?.dispatchEvent(new Event('input', { bubbles: true }));
    }, [date, time]);

    return (
        <div className={cn(controlClusterClass, 'w-full', className)}>
            <input
                ref={hiddenRef}
                type="hidden"
                name={name}
                value={combineDateTime(date, time)}
                required={required}
            />
            <label className="min-w-0 flex-1">
                <span className="sr-only">{t('admin.date')}</span>
                <input
                    type="date"
                    value={date}
                    required={required}
                    onChange={(event) => setDate(event.target.value)}
                    className={fieldInputClass}
                />
            </label>
            <label className="w-32 shrink-0">
                <span className="sr-only">{t('admin.time')}</span>
                <input
                    type="time"
                    value={time}
                    required={required}
                    onChange={(event) => setTime(event.target.value)}
                    className={fieldInputClass}
                />
            </label>
        </div>
    );
}
