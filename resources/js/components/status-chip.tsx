import { Chip } from '@/components/design';

const tones: Record<string, 'green' | 'neutral' | 'red'> = {
    published: 'green',
    listed: 'green',
    accepted: 'green',
    registered: 'green',
    draft: 'neutral',
    submitted: 'neutral',
    waitlisted: 'neutral',
    rejected: 'red',
    cancelled: 'red',
    sent: 'green',
    queued: 'neutral',
    not_sent: 'neutral',
    failed: 'red',
};

export function StatusChip({
    status,
    label,
}: {
    status: string;
    label: string;
}) {
    return <Chip tone={tones[status] ?? 'neutral'}>{label}</Chip>;
}
