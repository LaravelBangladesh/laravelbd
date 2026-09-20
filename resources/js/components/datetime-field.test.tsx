import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { DateTimeField } = await import('@/components/datetime-field');

const translations = { 'admin.date': 'Date', 'admin.time': 'Time' };

function hidden(): HTMLInputElement {
    return document.querySelector('input[type=hidden]') as HTMLInputElement;
}

describe('DateTimeField', () => {
    it('starts empty without a default value', () => {
        renderPage(<DateTimeField name="starts_at" />, { translations });

        expect(hidden()).toHaveValue('');
    });

    it('splits a default value into date and time', () => {
        renderPage(
            <DateTimeField name="starts_at" defaultValue="2026-03-12T09:30" />,
            { translations },
        );

        expect(screen.getByLabelText('Date')).toHaveValue('2026-03-12');
        expect(screen.getByLabelText('Time')).toHaveValue('09:30');
        expect(hidden()).toHaveValue('2026-03-12T09:30');
    });

    it('trims seconds from the time part', () => {
        renderPage(
            <DateTimeField
                name="starts_at"
                defaultValue="2026-03-12T09:30:45"
            />,
            { translations },
        );

        expect(screen.getByLabelText('Time')).toHaveValue('09:30');
    });

    it('handles a date without a time part', () => {
        renderPage(
            <DateTimeField name="starts_at" defaultValue="2026-03-12" />,
            { translations },
        );

        expect(screen.getByLabelText('Date')).toHaveValue('2026-03-12');
        expect(hidden()).toHaveValue('');
    });

    it('combines the parts once both are set', async () => {
        const user = userEvent.setup();
        renderPage(<DateTimeField name="starts_at" />, { translations });

        await user.type(screen.getByLabelText('Date'), '2026-03-12');

        expect(hidden()).toHaveValue('');

        await user.type(screen.getByLabelText('Time'), '09:30');

        expect(hidden()).toHaveValue('2026-03-12T09:30');
    });

    it('can be marked required', () => {
        renderPage(<DateTimeField name="starts_at" required />, {
            translations,
        });

        expect(hidden()).toHaveAttribute('required');
        expect(screen.getByLabelText('Date')).toBeRequired();
    });

    it('merges a custom class name', () => {
        const { container } = renderPage(
            <DateTimeField name="starts_at" className="custom" />,
            { translations },
        );

        expect(container.firstElementChild).toHaveClass('custom');
    });
});
