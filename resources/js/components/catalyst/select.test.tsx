import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { Select } from '@/components/catalyst/select';

function options() {
    return (
        <>
            <option value="talk">Talk</option>
            <option value="workshop">Workshop</option>
        </>
    );
}

describe('Select', () => {
    it('renders a single select with a chevron', () => {
        const { container } = render(
            <Select aria-label="Kind" className="custom">
                {options()}
            </Select>,
        );

        expect(screen.getByLabelText('Kind')).toBeInTheDocument();
        expect(container.querySelector('svg')).toBeInTheDocument();
        expect(container.firstElementChild).toHaveClass('custom');
    });

    it('renders a multiple select without a chevron', () => {
        const { container } = render(
            <Select aria-label="Kinds" multiple>
                {options()}
            </Select>,
        );

        expect(screen.getByLabelText('Kinds')).toHaveAttribute('multiple');
        expect(container.querySelector('svg')).toBeNull();
    });
});
