import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { FieldToggle } = await import('@/components/field-toggle');

function hidden(): HTMLInputElement | null {
    return document.querySelector('input[type=hidden]');
}

describe('FieldToggle', () => {
    it('posts 0 when unchecked', () => {
        renderPage(<FieldToggle name="cfp_enabled" label="Accept proposals" />);

        expect(hidden()).toHaveValue('0');
        expect(hidden()).toHaveAttribute('name', 'cfp_enabled');
        expect(screen.getByRole('switch')).not.toBeChecked();
    });

    it('honours defaultChecked and posts 1', () => {
        renderPage(
            <FieldToggle
                name="cfp_enabled"
                label="Accept proposals"
                defaultChecked
            />,
        );

        expect(hidden()).toHaveValue('1');
        expect(screen.getByRole('switch')).toBeChecked();
    });

    it('toggles the posted value and reports the change', async () => {
        const user = userEvent.setup();
        const onChange = vi.fn();
        renderPage(
            <FieldToggle
                name="cfp_enabled"
                label="Accept proposals"
                onChange={onChange}
            />,
        );

        await user.click(screen.getByRole('switch'));

        expect(onChange).toHaveBeenCalledWith(true);
        expect(hidden()).toHaveValue('1');

        await user.click(screen.getByRole('switch'));

        expect(onChange).toHaveBeenLastCalledWith(false);
        expect(hidden()).toHaveValue('0');
    });

    it('toggles without an onChange handler', async () => {
        const user = userEvent.setup();
        renderPage(<FieldToggle name="flag" label="Flag" />);

        await user.click(screen.getByRole('switch'));

        expect(hidden()).toHaveValue('1');
    });

    it('omits the hidden input when unnamed', () => {
        renderPage(<FieldToggle label="Accept proposals" />);

        expect(hidden()).toBeNull();
    });

    it('renders a description and an error', () => {
        renderPage(
            <FieldToggle
                name="flag"
                label="Flag"
                description="Turn this on."
                error="Flag is invalid"
            />,
        );

        expect(screen.getByText('Turn this on.')).toBeInTheDocument();
        expect(screen.getByRole('alert')).toHaveTextContent('Flag is invalid');
    });

    it('merges a custom class name', () => {
        const { container } = renderPage(
            <FieldToggle label="Flag" className="custom" />,
        );

        expect(container.firstElementChild).toHaveClass('custom');
    });
});
