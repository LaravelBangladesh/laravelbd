import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { testUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/directory/index')).default;
type DirectoryCardData = import('@/pages/directory/index').DirectoryCardData;

const translations = {
    'directory.title': 'Directory',
    'directory.hero': 'People and companies',
    'directory.lead': 'Find members near you.',
    'directory.invite': 'Not listed yet?',
    'directory.cta.button': 'Add your listing',
    'directory.cta.title': 'Get listed',
    'directory.cta.lead': 'Publish a profile in minutes.',
    'directory.count': ':count listed',
    'directory.search': 'Search the directory',
    'directory.filters': 'Filters',
    'directory.empty': 'No listings yet.',
    'directory.empty_search': 'No listings match your search.',
    'resources.all': 'All',
};

const kinds = [
    { value: 'person', label: 'People' },
    { value: 'company', label: 'Companies' },
];

const ada: DirectoryCardData = {
    id: 'listing-1',
    slug: 'ada-lovelace',
    name: 'Ada Lovelace',
    title: 'Engineer',
    city: 'Dhaka',
    company: 'Analytical Co',
    kind: 'person',
    kind_label: 'Person',
    photo_url: '/images/ada.jpg',
};

const acme: DirectoryCardData = {
    id: 'listing-2',
    slug: 'acme',
    name: 'Acme Ltd',
    title: null,
    city: null,
    company: null,
    kind: 'company',
    kind_label: 'Company',
    photo_url: null,
};

describe('DirectoryIndex', () => {
    it('shows the empty state when nothing is listed', () => {
        renderPage(
            <Page json_ld={[]} listings={[]} kind={null} kinds={kinds} />,
            {
                translations,
            },
        );

        expect(screen.getByText('No listings yet.')).toBeInTheDocument();
        expect(screen.getAllByText('0 listed')).toHaveLength(2);
    });

    it('points guests at the login page to get listed', () => {
        renderPage(
            <Page json_ld={[]} listings={[]} kind={null} kinds={kinds} />,
            {
                translations,
            },
        );

        screen
            .getAllByRole('link', { name: 'Add your listing' })
            .forEach((link) => {
                expect(link).toHaveAttribute('href', '/login');
            });
    });

    it('points members at their own listing page', () => {
        renderPage(
            <Page json_ld={[]} listings={[]} kind={null} kinds={kinds} />,
            {
                translations,
                auth: { user: testUser },
            },
        );

        screen
            .getAllByRole('link', { name: 'Add your listing' })
            .forEach((link) => {
                expect(link).toHaveAttribute('href', '/account/directory');
            });
    });

    it('renders a photo for listings that have one and initials otherwise', () => {
        const { container } = renderPage(
            <Page
                json_ld={[]}
                listings={[ada, acme]}
                kind={null}
                kinds={kinds}
            />,
            { translations },
        );

        expect(
            screen.getByRole('link', { name: /Ada Lovelace/ }),
        ).toHaveAttribute('href', '/directory/ada-lovelace');
        expect(
            screen.getByText('Engineer · Analytical Co · Dhaka'),
        ).toBeInTheDocument();
        expect(screen.getByText('AL')).toBeInTheDocument();
        expect(container.querySelectorAll('img')).toHaveLength(1);
    });

    it('sizes a company logo differently from a member portrait', () => {
        const { container } = renderPage(
            <Page
                json_ld={[]}
                listings={[ada, { ...acme, photo_url: '/images/acme.png' }]}
                kind={null}
                kinds={kinds}
            />,
            { translations },
        );

        const images = container.querySelectorAll('img');
        expect(images[0]).toHaveClass('size-16');
        expect(images[1]).toHaveClass('object-contain');
    });

    it('copes with a name that starts with whitespace', () => {
        renderPage(
            <Page
                json_ld={[]}
                listings={[{ ...acme, name: ' Acme' }]}
                kind={null}
                kinds={kinds}
            />,
            { translations },
        );

        expect(screen.getByText('A')).toBeInTheDocument();
    });

    it('filters the listings by the search query', async () => {
        const user = userEvent.setup();

        renderPage(
            <Page
                json_ld={[]}
                listings={[ada, acme]}
                kind={null}
                kinds={kinds}
            />,
            {
                translations,
            },
        );

        await user.type(
            screen.getByPlaceholderText('Search the directory'),
            'acme',
        );

        expect(screen.getByText('Acme Ltd')).toBeInTheDocument();
        expect(screen.queryByText('Ada Lovelace')).not.toBeInTheDocument();
    });

    it('explains when a search matches nothing', async () => {
        const user = userEvent.setup();

        renderPage(
            <Page json_ld={[]} listings={[ada]} kind={null} kinds={kinds} />,
            {
                translations,
            },
        );

        await user.type(
            screen.getByPlaceholderText('Search the directory'),
            'nobody',
        );

        expect(
            screen.getByText('No listings match your search.'),
        ).toBeInTheDocument();
    });

    it('toggles the filter pills open and closed', async () => {
        const user = userEvent.setup();

        renderPage(
            <Page json_ld={[]} listings={[ada]} kind={null} kinds={kinds} />,
            {
                translations,
            },
        );

        const toggle = screen.getByRole('button', { name: /Filters/ });
        expect(toggle).toHaveAttribute('aria-expanded', 'false');
        expect(
            screen.queryByRole('link', { name: 'All' }),
        ).not.toBeInTheDocument();

        await user.click(toggle);

        expect(toggle).toHaveAttribute('aria-expanded', 'true');
        expect(screen.getByRole('link', { name: 'People' })).toHaveAttribute(
            'href',
            '/directory?kind=person',
        );
    });

    it('opens the filters and counts the active one when a kind is selected', () => {
        renderPage(
            <Page
                json_ld={[]}
                listings={[acme]}
                kind="company"
                kinds={kinds}
            />,
            {
                translations,
            },
        );

        expect(screen.getByRole('button', { name: /Filters/ })).toHaveAttribute(
            'aria-expanded',
            'true',
        );
        expect(screen.getByRole('link', { name: 'Companies' })).toHaveClass(
            'bg-brand-green',
        );
        expect(screen.getByText('1')).toBeInTheDocument();
    });
});
