import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { FieldSelect } = await import('@/components/field-select');

const options = [
    { value: 'talk', label: 'Talk' },
    { value: 'workshop', label: 'Workshop' },
];

function hidden(): HTMLInputElement | null {
    return document.querySelector('input[type=hidden]');
}

describe('FieldSelect', () => {
    it('selects the first option by default', () => {
        renderPage(<FieldSelect options={options} />);

        expect(screen.getByRole('button')).toHaveTextContent('Talk');
    });

    it('honours a matching default value', () => {
        renderPage(<FieldSelect options={options} defaultValue="workshop" />);

        expect(screen.getByRole('button')).toHaveTextContent('Workshop');
    });

    it('ignores a default value that is not an option', () => {
        renderPage(<FieldSelect options={options} defaultValue="missing" />);

        expect(screen.getByRole('button')).toHaveTextContent('Talk');
    });

    it('shows a placeholder when there are no options', () => {
        renderPage(<FieldSelect options={[]} placeholder="Pick one" />);

        expect(screen.getByRole('button')).toHaveTextContent('Pick one');
    });

    it('renders a hidden input when named', () => {
        renderPage(<FieldSelect name="kind" options={options} required />);

        expect(hidden()).toHaveValue('talk');
        expect(hidden()).toHaveAttribute('required');
    });

    it('omits the hidden input when unnamed', () => {
        renderPage(<FieldSelect options={options} />);

        expect(hidden()).toBeNull();
    });

    it('selects another option and reports the change', async () => {
        const user = userEvent.setup();
        const onChange = vi.fn();
        renderPage(
            <FieldSelect name="kind" options={options} onChange={onChange} />,
        );

        await user.click(screen.getByRole('button'));
        await user.click(screen.getByRole('option', { name: /Workshop/ }));

        expect(onChange).toHaveBeenCalledWith('workshop');
        expect(hidden()).toHaveValue('workshop');
    });

    it('selects another option without an onChange handler', async () => {
        const user = userEvent.setup();
        renderPage(<FieldSelect name="kind" options={options} />);

        await user.click(screen.getByRole('button'));
        await user.click(screen.getByRole('option', { name: /Workshop/ }));

        expect(hidden()).toHaveValue('workshop');
    });

    it('merges a custom class name', () => {
        const { container } = renderPage(
            <FieldSelect options={options} className="custom" />,
        );

        expect(container.firstElementChild).toHaveClass('custom');
    });
});
