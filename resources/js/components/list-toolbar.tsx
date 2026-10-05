import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Input } from '@/components/catalyst/input';
import { controlClass } from '@/components/design';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export type ToolbarFilter = {
    name: string;
    label: string;
    /** The first option should be the "any" choice, with an empty value. */
    options: FieldOption[];
};

function queryFrom(values: Record<string, string>): Record<string, string> {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== ''),
    );
}

/**
 * Search, filters and export for a paginated admin list. Every change visits
 * `path` without a page number, so the results start again from page one.
 */
export function ListToolbar({
    path,
    exportPath,
    values,
    searchLabel,
    filters,
}: {
    path: string;
    exportPath: string;
    values: Record<string, string>;
    searchLabel: string;
    filters: ToolbarFilter[];
}) {
    const t = useTrans();
    const [search, setSearch] = useState(values.q ?? '');
    const timer = useRef<ReturnType<typeof setTimeout>>(undefined);
    const query = new URLSearchParams(queryFrom(values)).toString();

    useEffect(() => () => clearTimeout(timer.current), []);

    const visit = (next: Record<string, string>) => {
        router.get(path, queryFrom({ ...values, ...next }), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <div className="mt-8 flex flex-col gap-3 lg:flex-row lg:items-center">
            <Input
                type="search"
                aria-label={searchLabel}
                placeholder={searchLabel}
                value={search}
                className="lg:max-w-sm"
                onChange={(event) => {
                    const q = event.target.value;

                    setSearch(q);
                    clearTimeout(timer.current);
                    timer.current = setTimeout(() => visit({ q }), 300);
                }}
            />
            {filters.map((filter) => (
                <div key={filter.name} aria-label={filter.label} role="group">
                    <FieldSelect
                        options={filter.options}
                        defaultValue={values[filter.name]}
                        className="lg:w-56"
                        onChange={(value) =>
                            visit({ q: search, [filter.name]: value })
                        }
                    />
                </div>
            ))}
            <a
                href={query === '' ? exportPath : `${exportPath}?${query}`}
                className={cn(
                    controlClass,
                    'border-line bg-paper justify-center border font-medium lg:ml-auto',
                )}
            >
                {t('admin.export_csv')}
            </a>
        </div>
    );
}
