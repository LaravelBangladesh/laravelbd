import { Link } from '@/components/catalyst/link';
import { cn } from '@/lib/utils';

export const controlClass =
    'inline-flex h-10 items-center gap-2 rounded-none bg-transparent px-3 text-sm text-ink transition-colors hover:bg-canvas focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-red data-open:bg-canvas';

export const controlClusterClass =
    'inline-flex items-stretch divide-x divide-line overflow-hidden rounded-none border border-line bg-paper';

export const menuPanelClass =
    'z-50 origin-top-right rounded-none border border-line bg-paper py-1 shadow-menu outline-none [--anchor-gap:--spacing(1)] [--anchor-padding:--spacing(2)]';

export const menuItemClass =
    'flex w-full items-center gap-3 px-3 py-2 text-left text-sm text-ink data-focus:bg-canvas';

export const fieldInputClass =
    'h-10 w-full rounded-none bg-transparent px-3 text-sm text-ink focus:outline-none focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brand-red [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-50';

export const actionRowClass =
    'flex flex-col gap-3 sm:flex-row sm:flex-wrap [&>.btn-offset]:w-full sm:[&>.btn-offset]:w-auto';

export const pageHeaderClass =
    'flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between';

export function Container({
    className,
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <div className={cn('mx-auto w-full max-w-6xl px-4 sm:px-6', className)}>
            {children}
        </div>
    );
}

export function Eyebrow({
    className,
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <p
            className={cn(
                'text-brand-green text-xs font-bold tracking-[0.16em] uppercase',
                className,
            )}
        >
            {children}
        </p>
    );
}

export function Display({
    as: Tag = 'h1',
    className,
    children,
}: {
    as?: 'h1' | 'h2' | 'h3';
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <Tag
            className={cn(
                'text-ink font-medium tracking-tight text-balance',
                Tag === 'h1' &&
                    'text-[2.15rem] leading-[1.08] sm:text-5xl md:text-6xl lg:text-7xl',
                Tag === 'h2' &&
                    'text-[1.75rem] leading-[1.12] sm:text-4xl md:text-5xl',
                Tag === 'h3' && 'text-xl leading-snug sm:text-2xl',
                className,
            )}
        >
            {children}
        </Tag>
    );
}

export function Lead({
    className,
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <p
            className={cn(
                'text-ink-muted text-base leading-relaxed sm:text-lg md:text-xl',
                className,
            )}
        >
            {children}
        </p>
    );
}

export function Surface({
    className,
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <div
            className={cn(
                'border-line bg-paper rounded-none border',
                className,
            )}
        >
            {children}
        </div>
    );
}

export function Section({
    tone = 'plain',
    className,
    children,
}: {
    tone?: 'plain' | 'canvas' | 'ink';
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <section
            className={cn(
                tone === 'plain' && 'bg-paper',
                tone === 'canvas' && 'bg-canvas',
                tone === 'ink' && 'bg-ink text-white',
                className,
            )}
        >
            {children}
        </section>
    );
}

export function Mesh({ className }: { className?: string }) {
    return (
        <div
            aria-hidden
            className={cn(
                'pointer-events-none absolute inset-0 overflow-hidden',
                className,
            )}
        >
            <div className="bg-artisan-grid absolute inset-0" />
            <div className="bg-brand-red/8 absolute -top-32 left-1/4 size-[min(28rem,80vw)] rounded-none blur-3xl" />
            <div className="bg-brand-green/10 absolute top-24 -right-20 size-[min(22rem,70vw)] rounded-none blur-3xl" />
        </div>
    );
}

export { LaravelMark } from '@/components/hero-mark';

export function BrandBar({ className }: { className?: string }) {
    return (
        <div className={cn('flex h-1.5', className)} aria-hidden>
            <span className="bg-brand-green flex-1" />
            <span className="bg-brand-red w-20" />
        </div>
    );
}

const chipTones = {
    green: 'bg-brand-green/8 text-brand-green',
    neutral: 'bg-canvas text-ink-muted',
    red: 'bg-brand-red/8 text-brand-red',
};

export function Chip({
    tone = 'green',
    className,
    children,
}: {
    tone?: keyof typeof chipTones;
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-none px-2 py-0.5 text-[11px] font-bold tracking-[0.12em] uppercase',
                chipTones[tone],
                className,
            )}
        >
            {children}
        </span>
    );
}

export function FilterPills({
    items,
    className,
}: {
    items: { href: string; label: string; current: boolean }[];
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex [scrollbar-width:none] gap-2 overflow-x-auto sm:flex-wrap sm:overflow-visible [&::-webkit-scrollbar]:hidden',
                className,
            )}
        >
            {items.map((item) => (
                <Link
                    key={item.href}
                    href={item.href}
                    className={cn(
                        'inline-flex h-9 shrink-0 items-center rounded-none border px-3.5 text-sm font-medium transition-colors',
                        item.current
                            ? 'border-brand-green bg-brand-green text-white'
                            : 'border-line bg-paper text-ink-muted hover:border-line-strong hover:text-ink',
                    )}
                >
                    {item.label}
                </Link>
            ))}
        </div>
    );
}

export function Chevron({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 12 12"
            aria-hidden
            className={cn(
                'size-2.5 fill-current opacity-55 transition-transform group-data-open:rotate-180',
                className,
            )}
        >
            <path d="M2.2 4.2 6 8l3.8-3.8-.8-.8L6 6.4 3 3.4z" />
        </svg>
    );
}

export function Check({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 12 12"
            aria-hidden
            className={cn(
                'size-3 fill-none stroke-current stroke-[1.5]',
                className,
            )}
        >
            <path d="M2 6.2 4.7 9 10 3" />
        </svg>
    );
}

const faceVariants = {
    primary: 'border-brand-red bg-brand-red text-white',
    outline:
        'border-brand-red bg-paper text-brand-red lg:group-hover/btn:border-brand-red-hover lg:group-hover/btn:bg-brand-red-hover lg:group-hover/btn:text-white',
    inverse:
        'border-white bg-transparent text-white lg:group-hover/btn:bg-white lg:group-hover/btn:text-ink',
};

const shadowVariants = {
    primary: 'border-brand-red',
    outline: 'border-brand-red',
    inverse: 'border-white',
};

type OffsetVariant = keyof typeof faceVariants;

type ButtonShared = {
    variant?: OffsetVariant | 'ghost';
    className?: string;
    children: React.ReactNode;
};

type LinkButtonProps = ButtonShared & {
    href: string;
} & Omit<
        React.ComponentPropsWithoutRef<typeof Link>,
        'className' | 'href' | 'children'
    >;

type NativeButtonProps = ButtonShared & {
    href?: undefined;
} & Omit<
        React.ButtonHTMLAttributes<HTMLButtonElement>,
        'className' | 'children'
    >;

export function Button(props: LinkButtonProps): React.JSX.Element;
export function Button(props: NativeButtonProps): React.JSX.Element;
export function Button({
    variant = 'primary',
    className,
    children,
    href,
    ...props
}: LinkButtonProps | NativeButtonProps) {
    if (variant === 'ghost') {
        const classes = cn(
            'text-ink hover:text-brand-red focus-visible:outline-brand-red inline-flex h-11 items-center justify-center rounded-none px-1 text-sm font-medium transition-colors focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50',
            className,
        );

        if (typeof href === 'string') {
            const linkProps = props as Omit<
                LinkButtonProps,
                keyof ButtonShared | 'href'
            >;

            return (
                <Link href={href} className={classes} {...linkProps}>
                    {children}
                </Link>
            );
        }

        const buttonProps = props as Omit<
            NativeButtonProps,
            keyof ButtonShared
        >;

        return (
            <button type="button" className={classes} {...buttonProps}>
                {children}
            </button>
        );
    }

    const offset = variant;
    const classes = cn(
        'btn-offset group/btn focus-visible:outline-brand-red relative inline-flex h-11 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-4 disabled:pointer-events-none disabled:opacity-50',
        className,
    );
    const face = (
        <>
            <span
                className={cn(
                    'relative z-[2] flex h-full w-full items-center justify-center rounded-none border-[1.5px] px-5 text-xs font-bold tracking-[0.12em] uppercase',
                    'transition-[transform,background-color,color,border-color] duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]',
                    'motion-safe:max-lg:active:translate-y-0.5 motion-safe:lg:group-hover/btn:-translate-x-1 motion-safe:lg:group-hover/btn:-translate-y-1',
                    faceVariants[offset],
                )}
            >
                {children}
            </span>
            <span
                aria-hidden
                className={cn(
                    'absolute inset-0 rounded-none border-[1.5px]',
                    shadowVariants[offset],
                )}
            />
        </>
    );

    if (typeof href === 'string') {
        const linkProps = props as Omit<
            LinkButtonProps,
            keyof ButtonShared | 'href'
        >;

        return (
            <Link href={href} className={classes} {...linkProps}>
                {face}
            </Link>
        );
    }

    const buttonProps = props as Omit<NativeButtonProps, keyof ButtonShared>;

    return (
        <button type="button" className={classes} {...buttonProps}>
            {face}
        </button>
    );
}
