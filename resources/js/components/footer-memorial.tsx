import { useEffect, useRef } from 'react';
import { useTrans } from '@/lib/i18n';

export function FooterMemorial() {
    const t = useTrans();
    const svgRef = useRef<SVGSVGElement>(null);

    useEffect(() => {
        const svg = svgRef.current;

        if (!svg || typeof IntersectionObserver === 'undefined') {
            return;
        }

        const lines = [
            ...svg.querySelectorAll<SVGGeometryElement>('.anim-line'),
        ];
        const facets = svg.querySelector<SVGGElement>('.memorial-facets');
        const reduce = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (reduce) {
            return;
        }

        for (const line of lines) {
            const length = line.getTotalLength?.() ?? 400;
            line.style.strokeDasharray = `${length}`;
            line.style.strokeDashoffset = `${length}`;
            line.style.transition =
                'stroke-dashoffset 1.8s cubic-bezier(0.16, 1, 0.3, 1)';
        }

        if (facets) {
            facets.style.opacity = '0';
            facets.style.transition = 'opacity 1.4s ease-out 0.6s';
        }

        const observer = new IntersectionObserver(
            (entries) => {
                if (!entries.some((entry) => entry.isIntersecting)) {
                    return;
                }

                for (const line of lines) {
                    line.style.strokeDashoffset = '0';
                }

                if (facets) {
                    facets.style.opacity = '1';
                }

                observer.disconnect();
            },
            { threshold: 0.25 },
        );

        observer.observe(svg);

        return () => observer.disconnect();
    }, []);

    return (
        <svg
            ref={svgRef}
            viewBox="0 0 520 340"
            aria-hidden
            className="h-auto w-full max-w-[480px] drop-shadow-lg"
        >
            <defs>
                <linearGradient
                    id="footer-emerald"
                    x1="0%"
                    x2="100%"
                    y1="0%"
                    y2="100%"
                >
                    <stop offset="0%" stopColor="#006A4E" stopOpacity="0.22" />
                    <stop
                        offset="100%"
                        stopColor="#006A4E"
                        stopOpacity="0.04"
                    />
                </linearGradient>
                <linearGradient
                    id="footer-emerald-inner"
                    x1="50%"
                    x2="50%"
                    y1="0%"
                    y2="100%"
                >
                    <stop offset="0%" stopColor="#006A4E" stopOpacity="0.32" />
                    <stop
                        offset="100%"
                        stopColor="#046C50"
                        stopOpacity="0.08"
                    />
                </linearGradient>
            </defs>
            <g opacity="0.07" stroke="#ffffff" strokeWidth="0.8" fill="none">
                <circle cx="260" cy="180" r="145" strokeDasharray="3 6" />
                <circle cx="260" cy="180" r="115" />
                <circle cx="260" cy="180" r="80" strokeDasharray="2 4" />
                <circle cx="260" cy="180" r="45" />
                <path d="M 260 35 Q 275 80 260 100 Q 245 80 260 35 Z" />
                <path d="M 260 325 Q 275 280 260 260 Q 245 280 260 325 Z" />
                <path d="M 115 180 Q 160 195 180 180 Q 160 165 115 180 Z" />
                <path d="M 405 180 Q 360 195 340 180 Q 360 165 405 180 Z" />
                <line
                    x1="260"
                    x2="260"
                    y1="20"
                    y2="330"
                    strokeDasharray="1 5"
                />
                <line
                    x1="100"
                    x2="420"
                    y1="180"
                    y2="180"
                    strokeDasharray="1 5"
                />
            </g>
            <g
                fill="#64748B"
                fontFamily="var(--font-jetbrains), ui-monospace, monospace"
                fontSize="9"
                fontWeight="600"
            >
                <text textAnchor="middle" x="260" y="328">
                    {t('footer.memorial_caption')}
                </text>
                <text x="50" y="18" fill="#475569" fontSize="8.5">
                    LAT 23°49'29"N
                </text>
                <text
                    x="470"
                    y="18"
                    fill="#475569"
                    fontSize="8.5"
                    textAnchor="end"
                >
                    LON 90°15'16"E
                </text>
            </g>
            <g className="memorial-facets">
                <polygon
                    fill="url(#footer-emerald)"
                    points="60,285 260,185 95,285"
                />
                <polygon
                    fill="url(#footer-emerald)"
                    points="460,285 260,185 425,285"
                />
                <polygon
                    fill="url(#footer-emerald)"
                    points="98,285 260,158 135,285"
                />
                <polygon
                    fill="url(#footer-emerald)"
                    points="422,285 260,158 385,285"
                />
                <polygon
                    fill="url(#footer-emerald)"
                    points="138,285 260,130 172,285"
                />
                <polygon
                    fill="url(#footer-emerald)"
                    points="382,285 260,130 348,285"
                />
                <polygon
                    fill="url(#footer-emerald-inner)"
                    points="175,285 260,100 205,285"
                />
                <polygon
                    fill="url(#footer-emerald-inner)"
                    points="345,285 260,100 315,285"
                />
                <polygon
                    fill="url(#footer-emerald-inner)"
                    points="208,285 260,70 230,285"
                />
                <polygon
                    fill="url(#footer-emerald-inner)"
                    points="312,285 260,70 290,285"
                />
                <polygon
                    fill="url(#footer-emerald-inner)"
                    points="232,285 260,42 248,285"
                />
                <polygon
                    fill="url(#footer-emerald-inner)"
                    points="288,285 260,42 272,285"
                />
                <polygon
                    fill="#006A4E"
                    fillOpacity="0.38"
                    points="250,285 260,22 270,285"
                />
            </g>
            <g
                className="memorial-strokes"
                strokeLinecap="round"
                strokeLinejoin="round"
                fill="none"
            >
                <line
                    className="anim-line"
                    x1="40"
                    x2="480"
                    y1="285"
                    y2="285"
                    stroke="#334155"
                    strokeWidth="1.8"
                />
                <line
                    className="anim-line"
                    x1="70"
                    x2="450"
                    y1="291"
                    y2="291"
                    stroke="#1E293B"
                    strokeWidth="1.2"
                />
                <polyline
                    className="anim-line"
                    points="60,285 260,185 95,285"
                    stroke="#475569"
                    strokeWidth="1.1"
                />
                <polyline
                    className="anim-line"
                    points="460,285 260,185 425,285"
                    stroke="#475569"
                    strokeWidth="1.1"
                />
                <polyline
                    className="anim-line"
                    points="138,285 260,130 172,285"
                    stroke="#94A3B8"
                    strokeWidth="1.1"
                />
                <polyline
                    className="anim-line"
                    points="382,285 260,130 348,285"
                    stroke="#94A3B8"
                    strokeWidth="1.1"
                />
                <polyline
                    className="anim-line"
                    points="208,285 260,70 230,285"
                    stroke="#E2E8F0"
                    strokeWidth="1.2"
                />
                <polyline
                    className="anim-line"
                    points="312,285 260,70 290,285"
                    stroke="#E2E8F0"
                    strokeWidth="1.2"
                />
                <line
                    className="anim-line"
                    x1="260"
                    x2="260"
                    y1="22"
                    y2="285"
                    stroke="#FFFFFF"
                    strokeWidth="1.6"
                />
            </g>
            <circle
                className="footer-live-ping"
                cx="260"
                cy="22"
                r="5"
                fill="none"
                stroke="#FF2D20"
                strokeWidth="1"
            />
            <circle
                className="footer-live-core"
                cx="260"
                cy="22"
                r="3.5"
                fill="#FF2D20"
            />
        </svg>
    );
}
