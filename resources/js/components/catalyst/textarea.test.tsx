import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { Textarea } from '@/components/catalyst/textarea';

describe('Textarea', () => {
    it('is resizable by default', () => {
        const { container } = render(
            <Textarea aria-label="Bio" className="custom" />,
        );

        expect(screen.getByLabelText('Bio')).toHaveClass('resize-y');
        expect(container.firstElementChild).toHaveClass('custom');
    });

    it('can be made non-resizable', () => {
        render(<Textarea aria-label="Bio" resizable={false} />);

        expect(screen.getByLabelText('Bio')).toHaveClass('resize-none');
    });
});
