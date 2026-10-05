import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Pagination } = await import('@/components/pagination');

const translations = {
    'admin.pagination': 'Pages',
    'admin.pagination_summary': ':from to :to of :total',
    'admin.previous': 'Previous',
    'admin.next': 'Next',
};

const url = (page: number) => `https://laravelbd.test/admin/users?page=${page}`;

const middlePage = {
    current_page: 2,
    last_page: 9,
    from: 26,
    to: 50,
    total: 210,
    prev_page_url: url(1),
    next_page_url: url(3),
    links: [
        { url: url(1), label: '&laquo; Previous', active: false },
        { url: url(1), label: '1', active: false },
        { url: url(2), label: '2', active: true },
        { url: null, label: '...', active: false },
        { url: url(9), label: '9', active: false },
        { url: url(3), label: 'Next &raquo;', active: false },
    ],
};

describe('Pagination', () => {
    it('links to the previous, next and numbered pages', () => {
        renderPage(<Pagination paginator={middlePage} />, { translations });

        expect(screen.getByText('26 to 50 of 210')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Previous' })).toHaveAttribute(
            'href',
            url(1),
        );
        expect(screen.getByRole('link', { name: 'Next' })).toHaveAttribute(
            'href',
            url(3),
        );
        expect(screen.getByRole('link', { name: '2' })).toHaveAttribute(
            'aria-current',
            'page',
        );
        expect(screen.getByRole('link', { name: '9' })).not.toHaveAttribute(
            'aria-current',
        );
        expect(screen.getByText('...').tagName).toBe('SPAN');
        expect(screen.queryByText('&laquo; Previous')).not.toBeInTheDocument();
    });

    it('shows the edges as plain text on the first page', () => {
        renderPage(
            <Pagination
                paginator={{
                    ...middlePage,
                    prev_page_url: null,
                    next_page_url: null,
                }}
            />,
            { translations },
        );

        expect(screen.getByText('Previous').tagName).toBe('SPAN');
        expect(screen.getByText('Next').tagName).toBe('SPAN');
    });

    it('shows only the summary for a single page', () => {
        renderPage(
            <Pagination
                paginator={{ ...middlePage, last_page: 1, from: 1, to: 3 }}
            />,
            { translations },
        );

        expect(screen.getByText('1 to 3 of 210')).toBeInTheDocument();
        expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
    });

    it('renders nothing for an empty list', () => {
        const { container } = renderPage(
            <Pagination paginator={{ ...middlePage, from: null, to: null }} />,
            { translations },
        );

        expect(container).toBeEmptyDOMElement();
    });
});
