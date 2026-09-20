import { Chip, Surface } from '@/components/design';
import { Link } from '@/components/catalyst/link';
import { useTrans } from '@/lib/i18n';

export type EventCardData = {
    slug: string;
    title: string;
    excerpt: string | null;
    type_label: string;
    starts_at: string | null;
    venue_name: string | null;
    cover_url: string | null;
};

export function EventCard({ event }: { event: EventCardData }) {
    const t = useTrans();

    return (
        <Surface className="hover:border-brand-red/40 overflow-hidden transition-colors">
            {event.cover_url && (
                <img
                    src={event.cover_url}
                    alt=""
                    className="h-44 w-full object-cover"
                />
            )}
            <div className="p-6">
                <Chip>{event.type_label}</Chip>
                <h2 className="text-ink mt-3 text-lg font-medium tracking-tight">
                    {event.title}
                </h2>
                <p className="text-ink-muted mt-2 text-sm">{event.starts_at}</p>
                {event.venue_name && (
                    <p className="text-ink-muted text-sm">{event.venue_name}</p>
                )}
                {event.excerpt && (
                    <p className="text-ink-muted mt-3 text-[15px] leading-6">
                        {event.excerpt}
                    </p>
                )}
                <Link
                    href={`/events/${event.slug}`}
                    className="text-brand-red hover:text-brand-red-hover mt-5 inline-flex text-sm font-medium"
                >
                    {t('events.view')} →
                </Link>
            </div>
        </Surface>
    );
}
