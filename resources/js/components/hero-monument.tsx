import { useTrans } from '@/lib/i18n';

export function HeroMonument() {
    const t = useTrans();

    return (
        <div className="relative flex aspect-square w-full max-w-[460px] items-center justify-center">
            <p className="sr-only">{t('home.hero.mark')}</p>
            <svg
                viewBox="0 0 540 540"
                fill="none"
                aria-hidden
                className="h-full w-full drop-shadow-md select-none"
            >
                <defs>
                    <linearGradient
                        id="laravel-grad"
                        x1="0%"
                        x2="100%"
                        y1="0%"
                        y2="100%"
                    >
                        <stop offset="0%" stopColor="#FF493B" />
                        <stop offset="100%" stopColor="#BC0003" />
                    </linearGradient>
                    <linearGradient
                        id="monument-main"
                        x1="50%"
                        x2="50%"
                        y1="0%"
                        y2="100%"
                    >
                        <stop
                            offset="0%"
                            stopColor="#046C50"
                            stopOpacity="0.9"
                        />
                        <stop
                            offset="100%"
                            stopColor="#002116"
                            stopOpacity="0.95"
                        />
                    </linearGradient>
                    <linearGradient
                        id="monument-side"
                        x1="0%"
                        x2="100%"
                        y1="0%"
                        y2="100%"
                    >
                        <stop
                            offset="0%"
                            stopColor="#10B981"
                            stopOpacity="0.4"
                        />
                        <stop
                            offset="100%"
                            stopColor="#046C50"
                            stopOpacity="0.75"
                        />
                    </linearGradient>
                </defs>
                <g opacity="0.45" stroke="#046C50" strokeWidth="0.75">
                    <circle cx="270" cy="270" r="235" strokeDasharray="4 6" />
                    <circle cx="270" cy="270" r="195" strokeDasharray="2 4" />
                    <circle cx="270" cy="270" r="150" strokeOpacity="0.5" />
                    <circle
                        cx="270"
                        cy="270"
                        r="95"
                        strokeDasharray="6 6"
                        strokeOpacity="0.7"
                    />
                    <line
                        x1="35"
                        x2="505"
                        y1="270"
                        y2="270"
                        strokeDasharray="3 7"
                        strokeOpacity="0.4"
                    />
                    <line
                        x1="270"
                        x2="270"
                        y1="35"
                        y2="505"
                        strokeDasharray="3 7"
                        strokeOpacity="0.4"
                    />
                    <line
                        x1="104"
                        x2="436"
                        y1="104"
                        y2="436"
                        strokeDasharray="2 8"
                        strokeOpacity="0.3"
                    />
                    <line
                        x1="436"
                        x2="104"
                        y1="104"
                        y2="436"
                        strokeDasharray="2 8"
                        strokeOpacity="0.3"
                    />
                    <g fill="none" stroke="#BC0003" strokeWidth="1">
                        <polygon points="270,30 274,38 270,46 266,38" />
                        <polygon points="270,494 274,502 270,510 266,502" />
                        <polygon points="30,270 38,274 46,270 38,266" />
                        <polygon points="494,270 502,274 510,270 502,266" />
                    </g>
                    <g
                        fill="none"
                        opacity="0.7"
                        stroke="#046C50"
                        strokeWidth="1"
                    >
                        <circle
                            cx="270"
                            cy="270"
                            r="215"
                            strokeDasharray="1 11"
                            strokeWidth="3"
                        />
                        <path d="M 270 75 Q 295 120 270 160 Q 245 120 270 75 Z" />
                        <path d="M 270 465 Q 295 420 270 380 Q 245 420 270 465 Z" />
                        <path d="M 75 270 Q 120 295 160 270 Q 120 245 75 270 Z" />
                        <path d="M 465 270 Q 420 295 380 270 Q 420 245 465 270 Z" />
                    </g>
                </g>
                <g id="sriti-soudho" stroke="#046C50" strokeWidth="1.25">
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.35"
                        points="120,440 270,170 145,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.35"
                        points="420,440 270,170 395,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.45"
                        points="150,440 270,140 180,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.45"
                        points="390,440 270,140 360,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.6"
                        points="185,440 270,110 215,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.6"
                        points="355,440 270,110 325,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.75"
                        points="215,440 270,80 238,440"
                    />
                    <polygon
                        fill="url(#monument-side)"
                        opacity="0.75"
                        points="325,440 270,80 302,440"
                    />
                    <polygon
                        fill="url(#monument-main)"
                        opacity="0.85"
                        points="238,440 270,55 252,440"
                    />
                    <polygon
                        fill="url(#monument-main)"
                        opacity="0.85"
                        points="302,440 270,55 288,440"
                    />
                    <polygon
                        fill="url(#monument-main)"
                        opacity="0.95"
                        points="252,440 270,35 262,440"
                    />
                    <polygon
                        fill="url(#monument-main)"
                        opacity="0.95"
                        points="288,440 270,35 278,440"
                    />
                    <line
                        x1="270"
                        x2="270"
                        y1="20"
                        y2="445"
                        stroke="#BC0003"
                        strokeLinecap="round"
                        strokeWidth="2.5"
                    />
                    <line
                        x1="100"
                        x2="440"
                        y1="440"
                        y2="440"
                        stroke="#046C50"
                        strokeLinecap="round"
                        strokeWidth="3"
                    />
                    <line
                        x1="125"
                        x2="415"
                        y1="447"
                        y2="447"
                        opacity="0.7"
                        stroke="#046C50"
                        strokeLinecap="round"
                        strokeWidth="2"
                    />
                    <line
                        x1="160"
                        x2="380"
                        y1="453"
                        y2="453"
                        opacity="0.5"
                        stroke="#046C50"
                        strokeLinecap="round"
                        strokeWidth="1.5"
                    />
                </g>
                <g id="laravel-mark" transform="translate(0, 5)">
                    <ellipse
                        cx="270"
                        cy="385"
                        fill="#000000"
                        fillOpacity="0.12"
                        rx="90"
                        ry="24"
                    />
                    <g
                        stroke="#ffffff"
                        strokeLinejoin="round"
                        strokeWidth="2.5"
                    >
                        <path
                            d="M 235 210 L 235 295 L 180 260 L 180 178 Z"
                            fill="#E0261C"
                        />
                        <path
                            d="M 235 210 L 290 178 L 290 260 L 235 295 Z"
                            fill="#C41E14"
                        />
                        <path
                            d="M 235 210 L 180 178 L 235 145 L 290 178 Z"
                            fill="#FF2D20"
                        />
                        <path
                            d="M 335 235 L 335 290 L 295 265 L 295 210 Z"
                            fill="#E0261C"
                        />
                        <path
                            d="M 335 235 L 375 210 L 375 265 L 335 290 Z"
                            fill="#C41E14"
                        />
                        <path
                            d="M 335 235 L 295 210 L 335 185 L 375 210 Z"
                            fill="#FF2D20"
                        />
                        <path
                            d="M 235 295 L 235 345 L 290 380 L 335 350 L 335 290"
                            fill="none"
                            stroke="#FF2D20"
                            strokeLinecap="round"
                            strokeWidth="4.5"
                        />
                        <path
                            d="M 180 260 L 140 285 L 140 345 L 235 405 L 290 380"
                            fill="none"
                            stroke="#FF2D20"
                            strokeLinecap="round"
                            strokeWidth="4.5"
                        />
                    </g>
                    <circle
                        cx="235"
                        cy="145"
                        r="4.5"
                        fill="#FFFFFF"
                        stroke="#FF2D20"
                        strokeWidth="2"
                    />
                    <circle
                        cx="335"
                        cy="185"
                        r="3.5"
                        fill="#FFFFFF"
                        stroke="#FF2D20"
                        strokeWidth="1.5"
                    />
                    <circle
                        cx="235"
                        cy="405"
                        r="4.5"
                        fill="#FFFFFF"
                        stroke="#046C50"
                        strokeWidth="2"
                    />
                    <circle
                        cx="140"
                        cy="345"
                        r="3.5"
                        fill="#FFFFFF"
                        stroke="#046C50"
                        strokeWidth="1.5"
                    />
                    <circle
                        cx="375"
                        cy="265"
                        r="3.5"
                        fill="#FFFFFF"
                        stroke="#046C50"
                        strokeWidth="1.5"
                    />
                </g>
                <text
                    x="280"
                    y="32"
                    fill="#046C50"
                    fontFamily="JetBrains Mono, monospace"
                    fontSize="10"
                    fontWeight="700"
                    opacity="0.8"
                >
                    APEX 90°00&apos;N
                </text>
                <text
                    x="390"
                    y="435"
                    fill="#5E3F3A"
                    fontFamily="JetBrains Mono, monospace"
                    fontSize="9"
                    fontWeight="600"
                    opacity="0.85"
                >
                    GRID.SOUDHO.07
                </text>
                <text
                    x="80"
                    y="435"
                    fill="#5E3F3A"
                    fontFamily="JetBrains Mono, monospace"
                    fontSize="9"
                    fontWeight="600"
                    opacity="0.85"
                >
                    PHP::ARTISAN.V11
                </text>
            </svg>
        </div>
    );
}
