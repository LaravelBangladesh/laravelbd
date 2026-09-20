import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

function tooltip(props: { sideOffset?: number } = {}) {
    return (
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger>Help</TooltipTrigger>
                <TooltipContent className="custom" {...props}>
                    Extra detail
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    );
}

describe('Tooltip', () => {
    it('renders the trigger and hides the content until hovered', () => {
        render(tooltip());

        expect(screen.getByRole('button', { name: 'Help' })).toHaveAttribute(
            'data-slot',
            'tooltip-trigger',
        );
        expect(screen.queryByText('Extra detail')).not.toBeInTheDocument();
    });

    it('shows the content on hover', async () => {
        const user = userEvent.setup();
        render(tooltip());

        await user.hover(screen.getByRole('button', { name: 'Help' }));

        const content = await screen.findByRole('tooltip');

        expect(content).toHaveTextContent('Extra detail');
        expect(content).toHaveClass('custom');
    });

    it('accepts a custom side offset', async () => {
        const user = userEvent.setup();
        render(tooltip({ sideOffset: 12 }));

        await user.hover(screen.getByRole('button', { name: 'Help' }));

        expect(await screen.findByRole('tooltip')).toHaveTextContent(
            'Extra detail',
        );
    });
});
