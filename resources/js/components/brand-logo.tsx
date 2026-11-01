import { Link } from '@/components/catalyst/link';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export function BrandLogo({
    className = '',
    onDark = false,
    compact = false,
}: {
    className?: string;
    onDark?: boolean;
    compact?: boolean;
}) {
    const t = useTrans();

    return (
        <Link
            href="/"
            aria-label={t('app.name')}
            className={cn(
                'min-w-0 text-[1.05rem] leading-none font-semibold tracking-tight sm:text-[1.35rem]',
                className,
            )}
        >
            <span className="text-brand-red">Laravel</span>
            <span
                className={cn(
                    !compact && 'max-[22.5rem]:hidden',
                    onDark ? 'text-white' : 'text-brand-green',
                )}
            >
                {compact ? ' BD' : ' Bangladesh'}
            </span>
        </Link>
    );
}
