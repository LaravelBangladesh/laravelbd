import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export function LaravelMark({ className }: { className?: string }) {
    const t = useTrans();

    return (
        <div
            className={cn(
                'relative mx-auto aspect-[5/4] w-full max-w-lg',
                className,
            )}
        >
            <span className="sr-only">{t('home.hero.mark')}</span>
            <img
                src="/images/smritisoudha.svg"
                alt=""
                className="pointer-events-none absolute inset-0 size-full object-contain"
            />
            <div className="animate-hero-float absolute top-[28%] left-1/2 z-10 w-[50%] -translate-x-1/2 motion-reduce:animate-none">
                <img
                    src="/images/laravel-logo.svg"
                    alt=""
                    className="w-full drop-shadow-[22px_32px_26px_rgb(27_27_24_/_0.36)]"
                />
            </div>
        </div>
    );
}
