import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const {
    BrandBar,
    Button,
    Check,
    Chevron,
    Chip,
    Container,
    Display,
    Eyebrow,
    FilterPills,
    Lead,
    Mesh,
    Section,
    Surface,
} = await import('@/components/design');

describe('Chip', () => {
    it.each([
        ['green', 'text-brand-green'],
        ['neutral', 'text-ink-muted'],
        ['red', 'text-brand-red'],
    ] as const)('renders the %s tone', (tone, toneClass) => {
        renderPage(<Chip tone={tone}>{tone} chip</Chip>);

        const chip = screen.getByText(`${tone} chip`);

        expect(chip).toBeInTheDocument();
        expect(chip).toHaveClass(toneClass);
    });

    it('defaults to the green tone', () => {
        renderPage(<Chip>default chip</Chip>);

        expect(screen.getByText('default chip')).toHaveClass(
            'text-brand-green',
        );
    });

    it('merges a custom class name', () => {
        renderPage(<Chip className="custom">chip</Chip>);

        expect(screen.getByText('chip')).toHaveClass('custom');
    });
});

describe('layout primitives', () => {
    it.each([
        ['Container', Container],
        ['Eyebrow', Eyebrow],
        ['Lead', Lead],
        ['Surface', Surface],
    ] as const)(
        '%s renders children and merges a class name',
        (_, Component) => {
            renderPage(<Component className="custom">content</Component>);

            expect(screen.getByText('content')).toBeInTheDocument();
        },
    );

    it('Section renders each tone', () => {
        const { container, unmount } = renderPage(
            <Section tone="canvas">canvas</Section>,
        );

        expect(container.querySelector('section')).toHaveClass('bg-canvas');
        unmount();

        const ink = renderPage(<Section tone="ink">ink</Section>);
        expect(ink.container.querySelector('section')).toHaveClass('bg-ink');
        ink.unmount();

        const plain = renderPage(<Section>plain</Section>);
        expect(plain.container.querySelector('section')).toHaveClass(
            'bg-paper',
        );
    });

    it('Mesh renders decorative layers', () => {
        const { container } = renderPage(<Mesh className="custom" />);

        expect(container.firstElementChild).toHaveAttribute('aria-hidden');
        expect(container.firstElementChild).toHaveClass('custom');
    });

    it('BrandBar renders the two brand stripes', () => {
        const { container } = renderPage(<BrandBar className="custom" />);

        expect(container.firstElementChild).toHaveAttribute('aria-hidden');
        expect(container.querySelectorAll('span')).toHaveLength(2);
    });
});

describe('Display', () => {
    it.each(['h1', 'h2', 'h3'] as const)('renders as %s', (tag) => {
        const { container } = renderPage(<Display as={tag}>heading</Display>);

        expect(container.querySelector(tag)).toHaveTextContent('heading');
    });

    it('defaults to an h1', () => {
        const { container } = renderPage(<Display>heading</Display>);

        expect(container.querySelector('h1')).toBeInTheDocument();
    });
});

describe('icons', () => {
    it('Chevron renders an svg', () => {
        const { container } = renderPage(<Chevron className="custom" />);

        expect(container.querySelector('svg')).toHaveClass('custom');
    });

    it('Check renders an svg', () => {
        const { container } = renderPage(<Check className="custom" />);

        expect(container.querySelector('svg')).toHaveClass('custom');
    });
});

describe('FilterPills', () => {
    const items = [
        { href: '/all', label: 'All', current: true },
        { href: '/talks', label: 'Talks', current: false },
    ];

    it('renders a link per item', () => {
        renderPage(<FilterPills items={items} className="custom" />);

        expect(screen.getByRole('link', { name: 'All' })).toHaveAttribute(
            'href',
            '/all',
        );
        expect(screen.getByRole('link', { name: 'Talks' })).toHaveAttribute(
            'href',
            '/talks',
        );
    });

    it('highlights the current item', () => {
        renderPage(<FilterPills items={items} />);

        expect(screen.getByRole('link', { name: 'All' })).toHaveClass(
            'bg-brand-green',
        );
        expect(screen.getByRole('link', { name: 'Talks' })).toHaveClass(
            'bg-paper',
        );
    });
});

describe('Button', () => {
    it.each(['primary', 'outline', 'inverse'] as const)(
        'renders the %s variant as a button',
        (variant) => {
            renderPage(<Button variant={variant}>Go</Button>);

            expect(
                screen.getByRole('button', { name: 'Go' }),
            ).toBeInTheDocument();
        },
    );

    it('renders an offset variant as a link when given an href', () => {
        renderPage(<Button href="/events">Events</Button>);

        expect(screen.getByRole('link', { name: 'Events' })).toHaveAttribute(
            'href',
            '/events',
        );
    });

    it('renders the ghost variant as a button', () => {
        renderPage(<Button variant="ghost">Ghost</Button>);

        expect(
            screen.getByRole('button', { name: 'Ghost' }),
        ).toBeInTheDocument();
    });

    it('renders the ghost variant as a link when given an href', () => {
        renderPage(
            <Button variant="ghost" href="/about">
                About
            </Button>,
        );

        expect(screen.getByRole('link', { name: 'About' })).toHaveAttribute(
            'href',
            '/about',
        );
    });

    it('forwards a click handler', async () => {
        const onClick = vi.fn();
        renderPage(<Button onClick={onClick}>Go</Button>);

        await userEvent.click(screen.getByRole('button', { name: 'Go' }));

        expect(onClick).toHaveBeenCalled();
    });

    it('forwards a click handler on the ghost variant', async () => {
        const onClick = vi.fn();
        renderPage(
            <Button variant="ghost" onClick={onClick}>
                Go
            </Button>,
        );

        await userEvent.click(screen.getByRole('button', { name: 'Go' }));

        expect(onClick).toHaveBeenCalled();
    });

    it('can be disabled', () => {
        renderPage(<Button disabled>Go</Button>);

        expect(screen.getByRole('button', { name: 'Go' })).toBeDisabled();
    });
});
