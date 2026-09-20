import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { StatusChip } from '@/components/status-chip';

describe('StatusChip', () => {
    it.each([
        ['published', 'text-brand-green'],
        ['accepted', 'text-brand-green'],
        ['registered', 'text-brand-green'],
        ['draft', 'text-ink-muted'],
        ['submitted', 'text-ink-muted'],
        ['waitlisted', 'text-ink-muted'],
        ['rejected', 'text-brand-red'],
        ['cancelled', 'text-brand-red'],
    ])('maps %s to its tone', (status, toneClass) => {
        render(<StatusChip status={status} label={status} />);

        expect(screen.getByText(status)).toHaveClass(toneClass);
    });

    it('falls back to the neutral tone for an unknown status', () => {
        render(<StatusChip status="mystery" label="Mystery" />);

        expect(screen.getByText('Mystery')).toHaveClass('text-ink-muted');
    });
});
