import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import InputError from '@/components/input-error';

describe('InputError', () => {
    it('renders the message', () => {
        render(<InputError message="Bad input" />);

        expect(screen.getByText('Bad input')).toBeInTheDocument();
    });

    it('merges a custom class name', () => {
        render(<InputError message="Bad input" className="text-center" />);

        expect(screen.getByText('Bad input')).toHaveClass('text-center');
    });

    it('forwards extra props', () => {
        render(<InputError message="Bad input" data-testid="error" />);

        expect(screen.getByTestId('error')).toBeInTheDocument();
    });

    it('renders nothing without a message', () => {
        const { container } = render(<InputError />);

        expect(container).toBeEmptyDOMElement();
    });
});
