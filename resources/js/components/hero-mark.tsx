import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

/** Running stitches just outside the stone, not a second outline. */
const SPIRE = [
    'M565.7 699.1 L613.9 188.3',
    'M553.8 698.0 L601.9 187.2',
    'M718.3 699.1 L670.1 188.3',
    'M730.2 698.0 L682.1 187.2',
];
const PLINTH = ['M201.3 761.0 L550.4 673.8', 'M1082.7 761.0 L733.6 673.8'];

const ALPANA_DEGREES = [0, 45, 90, 135, 180, 225, 270, 315] as const;

/**
 * Laravel in front of the National Martyrs' Memorial.
 * One alpana ring frames the logo. Kantha running stitches follow the
 * memorial seams and trail off the base. The ground is a warm wash, so
 * the stitches never cross the headline.
 */
export function LaravelMark({ className }: { className?: string }) {
    const t = useTrans();

    return (
        <figure
            className={cn(
                'relative mx-auto aspect-[5/4] w-full max-w-lg',
                className,
            )}
        >
            <figcaption className="sr-only">{t('home.hero.mark')}</figcaption>
            <img
                src="/images/smritisoudha.svg"
                alt=""
                className="pointer-events-none absolute inset-0 size-full object-contain"
            />
            <svg
                viewBox="0 0 1284 830"
                aria-hidden
                className="pointer-events-none absolute inset-0 size-full"
            >
                <defs>
                    <linearGradient id="kantha-out-left" x1="0" x2="1">
                        <stop offset="0%" stopColor="#1b1b18" stopOpacity="0" />
                        <stop
                            offset="100%"
                            stopColor="#1b1b18"
                            stopOpacity="0.55"
                        />
                    </linearGradient>
                    <linearGradient id="kantha-out-right" x1="0" x2="1">
                        <stop
                            offset="0%"
                            stopColor="#1b1b18"
                            stopOpacity="0.55"
                        />
                        <stop
                            offset="100%"
                            stopColor="#1b1b18"
                            stopOpacity="0"
                        />
                    </linearGradient>
                </defs>
                <path
                    d="M24 786 H188"
                    fill="none"
                    stroke="url(#kantha-out-left)"
                    strokeWidth="5"
                    strokeLinecap="round"
                    strokeDasharray="14 12"
                    className="hero-stitch"
                />
                <path
                    d="M1096 786 H1260"
                    fill="none"
                    stroke="url(#kantha-out-right)"
                    strokeWidth="5"
                    strokeLinecap="round"
                    strokeDasharray="14 12"
                    className="hero-stitch"
                />
                {PLINTH.map((d) => (
                    <path
                        key={d}
                        d={d}
                        fill="none"
                        stroke="#006a4e"
                        strokeOpacity="0.5"
                        strokeWidth="5"
                        strokeLinecap="round"
                        strokeDasharray="14 12"
                        className="hero-stitch"
                    />
                ))}
                {SPIRE.map((d) => (
                    <path
                        key={d}
                        d={d}
                        fill="none"
                        className="hero-stitch stroke-ink/75"
                        strokeWidth="5.5"
                        strokeLinecap="round"
                        strokeDasharray="16 11"
                    />
                ))}
            </svg>
            <div className="absolute top-[22%] left-1/2 z-10 w-[46%] -translate-x-1/2">
                <svg
                    viewBox="0 0 200 200"
                    aria-hidden
                    className="pointer-events-none absolute top-1/2 left-1/2 w-[156%] -translate-x-1/2 -translate-y-1/2"
                >
                    <circle
                        cx="100"
                        cy="100"
                        r="96"
                        fill="none"
                        className="stroke-ink/30"
                        strokeWidth="0.6"
                    />
                    <circle
                        cx="100"
                        cy="100"
                        r="88"
                        fill="none"
                        className="stroke-brand-green/50"
                        strokeWidth="0.45"
                    />
                    {ALPANA_DEGREES.map((degrees) => (
                        <ellipse
                            key={degrees}
                            cx="100"
                            cy="1"
                            rx="2.8"
                            ry="7"
                            className="fill-ink/55"
                            transform={`rotate(${degrees} 100 100)`}
                        />
                    ))}
                </svg>
                <div className="hero-logo-drift">
                    <img
                        src="/images/laravel-logo.svg"
                        alt=""
                        className="w-full drop-shadow-[0_14px_18px_rgb(27_27_24_/_0.16)]"
                    />
                </div>
            </div>
        </figure>
    );
}

export function HeroGround() {
    return (
        <div
            aria-hidden
            className="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div className="absolute inset-0 bg-[#f3efe6]/72" />
        </div>
    );
}
