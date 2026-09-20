import { Seo } from '@/components/seo';
import type { JsonLd } from '@/types/seo';
import {
    actionRowClass,
    Button,
    Chip,
    Container,
    Display,
    Lead,
    Mesh,
    Section,
} from '@/components/design';
import { ProfileAvatar } from '@/components/profile-avatar';
import { useTrans } from '@/lib/i18n';

type ListingDetail = {
    name: string;
    title: string | null;
    company: string | null;
    city: string | null;
    kind_label: string;
    bio: string;
    meta_description: string;
    json_ld: JsonLd[];
    photo_url: string | null;
    links: { key: string; url: string }[];
};

function initials(name: string): string {
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0] ?? '')
        .join('')
        .toUpperCase();
}

export default function DirectoryShow({
    listing,
    is_owner = false,
    is_published = true,
}: {
    listing: ListingDetail;
    is_owner?: boolean;
    is_published?: boolean;
}) {
    const t = useTrans();

    return (
        <Section className="relative overflow-hidden">
            <Mesh />
            <Container className="relative max-w-3xl py-12 sm:py-16">
                <Seo
                    title={listing.name}
                    description={listing.meta_description}
                    image={listing.photo_url}
                    type="profile"
                    jsonLd={listing.json_ld}
                />
                {is_owner && (
                    <div className="border-line bg-paper/80 mb-8 flex flex-col gap-3 border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-ink-muted text-sm">
                            {is_published
                                ? t('directory.owner_published')
                                : t('directory.pending')}
                        </p>
                        <Button href="/account/directory" variant="outline">
                            {t('account.directory_edit')}
                        </Button>
                    </div>
                )}
                {listing.photo_url ? (
                    <ProfileAvatar
                        src={listing.photo_url}
                        alt=""
                        className="mb-8 size-28"
                    />
                ) : (
                    <span className="bg-brand-green/10 text-brand-green mb-8 flex size-28 items-center justify-center text-2xl font-medium">
                        {initials(listing.name)}
                    </span>
                )}
                <Chip>{listing.kind_label}</Chip>
                <Display className="mt-4">{listing.name}</Display>
                {(listing.title || listing.company || listing.city) && (
                    <Lead className="mt-4">
                        {[listing.title, listing.company, listing.city]
                            .filter(Boolean)
                            .join(' · ')}
                    </Lead>
                )}
                {listing.bio && (
                    <p className="text-ink mt-8 text-[17px] leading-7 whitespace-pre-line">
                        {listing.bio}
                    </p>
                )}
                {listing.links.length > 0 && (
                    <div className={`${actionRowClass} mt-8`}>
                        {listing.links.map((link, index) => (
                            <Button
                                key={link.key}
                                href={link.url}
                                variant={index === 0 ? 'primary' : 'outline'}
                            >
                                {t(`directory.${link.key}`)}
                            </Button>
                        ))}
                    </div>
                )}
            </Container>
        </Section>
    );
}
