import { Button, Eyebrow, Surface } from '@/components/design';
import { cn } from '@/lib/utils';

export function AdminPageHeader({
    eyebrow,
    title,
    description,
    actions,
}: {
    eyebrow: string;
    title: string;
    description?: string;
    actions?: React.ReactNode;
}) {
    return (
        <div className="border-line flex flex-col gap-4 border-b pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div className="min-w-0">
                <Eyebrow>{eyebrow}</Eyebrow>
                <h1 className="text-ink mt-2 text-2xl font-medium tracking-tight sm:text-3xl">
                    {title}
                </h1>
                {description && (
                    <p className="text-ink-muted mt-2 max-w-2xl text-sm">
                        {description}
                    </p>
                )}
            </div>
            {actions && (
                <div className="flex shrink-0 flex-col gap-3 sm:flex-row sm:items-center">
                    {actions}
                </div>
            )}
        </div>
    );
}

export function AdminSection({
    title,
    description,
    className,
    children,
}: {
    title: string;
    description?: string;
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <Surface className={cn('p-5 sm:p-6', className)}>
            <Eyebrow>{title}</Eyebrow>
            {description && (
                <p className="text-ink-muted mt-2 text-sm">{description}</p>
            )}
            <div className="mt-5 grid grid-cols-1 gap-5">{children}</div>
        </Surface>
    );
}

export function AdminEmptyState({
    label,
    description,
    actionHref,
    actionLabel,
}: {
    label: string;
    description: string;
    actionHref?: string;
    actionLabel?: string;
}) {
    return (
        <div className="border-line bg-canvas border border-dashed px-6 py-12 text-center">
            <Eyebrow>{label}</Eyebrow>
            <p className="text-ink-muted mx-auto mt-3 max-w-md text-sm">
                {description}
            </p>
            {actionHref && actionLabel && (
                <div className="mt-6 flex justify-center">
                    <Button href={actionHref}>{actionLabel}</Button>
                </div>
            )}
        </div>
    );
}

export function AdminTableWrap({ children }: { children: React.ReactNode }) {
    return <div className="mt-8 overflow-x-auto">{children}</div>;
}
