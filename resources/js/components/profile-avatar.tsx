import { cn } from '@/lib/utils';

export const PROFILE_PLACEHOLDER = '/images/profile-placeholder.svg';

export function ProfileAvatar({
    src,
    alt = '',
    className,
}: {
    src?: string | null;
    alt?: string;
    className?: string;
}) {
    return (
        <img
            src={src || PROFILE_PLACEHOLDER}
            alt={alt}
            className={cn('size-8 shrink-0 object-cover', className)}
        />
    );
}
