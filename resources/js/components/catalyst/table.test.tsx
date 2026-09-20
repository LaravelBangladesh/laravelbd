import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } =
    await import('@/components/catalyst/table');

type Flags = {
    bleed?: boolean;
    dense?: boolean;
    grid?: boolean;
    striped?: boolean;
};

function table(flags: Flags = {}, rowProps: { href?: string } = {}) {
    return (
        <Table {...flags} className="custom">
            <TableHead className="head">
                <TableRow>
                    <TableHeader className="header">Name</TableHeader>
                    <TableHeader>Role</TableHeader>
                </TableRow>
            </TableHead>
            <TableBody>
                <TableRow {...rowProps} title="Ada" target="_blank">
                    <TableCell className="cell">Ada</TableCell>
                    <TableCell>Member</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    );
}

describe('Table', () => {
    it('renders headers and cells with the default flags', () => {
        renderPage(table());

        expect(screen.getByRole('columnheader', { name: 'Name' })).toHaveClass(
            'header',
        );
        expect(screen.getByRole('cell', { name: 'Ada' })).toHaveClass('cell');
        expect(screen.getByRole('columnheader', { name: 'Name' })).toHaveClass(
            'sm:first:pl-1',
        );
        expect(screen.getByRole('cell', { name: 'Ada' })).toHaveClass(
            'border-b',
            'py-4',
            'sm:first:pl-1',
        );
        expect(screen.getByRole('table').parentElement).toHaveClass(
            'sm:px-(--gutter)',
        );
    });

    it('drops the gutter padding when bled', () => {
        renderPage(table({ bleed: true }));

        expect(screen.getByRole('table').parentElement).not.toHaveClass(
            'sm:px-(--gutter)',
        );
        expect(
            screen.getByRole('columnheader', { name: 'Name' }),
        ).not.toHaveClass('sm:first:pl-1');
        expect(screen.getByRole('cell', { name: 'Ada' })).not.toHaveClass(
            'sm:first:pl-1',
        );
    });

    it('tightens the cell padding when dense', () => {
        renderPage(table({ dense: true }));

        expect(screen.getByRole('cell', { name: 'Ada' })).toHaveClass('py-2.5');
    });

    it('adds column borders when gridded', () => {
        renderPage(table({ grid: true }));

        expect(screen.getByRole('columnheader', { name: 'Name' })).toHaveClass(
            'border-l',
        );
        expect(screen.getByRole('cell', { name: 'Ada' })).toHaveClass(
            'border-l',
        );
    });

    it('stripes rows instead of drawing cell borders', () => {
        renderPage(table({ striped: true }));

        expect(
            screen.getByRole('cell', { name: 'Ada' }).parentElement,
        ).toHaveClass('even:bg-zinc-950/2.5');
        expect(screen.getByRole('cell', { name: 'Ada' })).not.toHaveClass(
            'border-b',
        );
    });
});

describe('TableRow', () => {
    it('links each cell of a row with an href', () => {
        renderPage(table({}, { href: '/members/ada' }));

        const links = screen.getAllByRole('link', { name: 'Ada' });

        expect(links).toHaveLength(2);
        expect(links[0]).toHaveAttribute('href', '/members/ada');
        expect(links[0]).toHaveAttribute('tabindex', '0');
        expect(links[1]).toHaveAttribute('tabindex', '-1');
        expect(links[0].closest('tr')).toHaveClass('hover:bg-zinc-950/2.5');
    });

    it('combines the link and striped hover styles', () => {
        renderPage(table({ striped: true }, { href: '/members/ada' }));

        expect(
            screen.getAllByRole('link', { name: 'Ada' })[0].closest('tr'),
        ).toHaveClass('hover:bg-zinc-950/5');
    });
});
