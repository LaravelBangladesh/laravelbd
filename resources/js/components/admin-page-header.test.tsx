import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { AdminEmptyState, AdminPageHeader, AdminSection, AdminTableWrap } =
    await import('@/components/admin-page-header');

describe('AdminPageHeader', () => {
    it('renders the eyebrow and title', () => {
        renderPage(<AdminPageHeader eyebrow="Admin" title="Events" />);

        expect(screen.getByText('Admin')).toBeInTheDocument();
        expect(
            screen.getByRole('heading', { name: 'Events' }),
        ).toBeInTheDocument();
    });

    it('renders an optional description and actions', () => {
        renderPage(
            <AdminPageHeader
                eyebrow="Admin"
                title="Events"
                description="Manage every event."
                actions={<button type="button">New event</button>}
            />,
        );

        expect(screen.getByText('Manage every event.')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'New event' }),
        ).toBeInTheDocument();
    });

    it('omits the description and actions when absent', () => {
        renderPage(<AdminPageHeader eyebrow="Admin" title="Events" />);

        expect(screen.queryByRole('button')).not.toBeInTheDocument();
    });
});

describe('AdminSection', () => {
    it('renders its title and children', () => {
        renderPage(
            <AdminSection title="Profile">
                <p>body</p>
            </AdminSection>,
        );

        expect(screen.getByText('Profile')).toBeInTheDocument();
        expect(screen.getByText('body')).toBeInTheDocument();
    });

    it('renders an optional description and custom class', () => {
        const { container } = renderPage(
            <AdminSection
                title="Profile"
                description="Who is speaking."
                className="custom"
            >
                <p>body</p>
            </AdminSection>,
        );

        expect(screen.getByText('Who is speaking.')).toBeInTheDocument();
        expect(container.firstElementChild).toHaveClass('custom');
    });

    it('omits the description when absent', () => {
        renderPage(
            <AdminSection title="Profile">
                <p>body</p>
            </AdminSection>,
        );

        expect(screen.queryByText('Who is speaking.')).not.toBeInTheDocument();
    });
});

describe('AdminEmptyState', () => {
    it('renders the label and description', () => {
        renderPage(
            <AdminEmptyState label="No events" description="Add the first." />,
        );

        expect(screen.getByText('No events')).toBeInTheDocument();
        expect(screen.getByText('Add the first.')).toBeInTheDocument();
    });

    it('renders an action when both href and label are given', () => {
        renderPage(
            <AdminEmptyState
                label="No events"
                description="Add the first."
                actionHref="/admin/events/create"
                actionLabel="New event"
            />,
        );

        expect(screen.getByRole('link', { name: 'New event' })).toHaveAttribute(
            'href',
            '/admin/events/create',
        );
    });

    it('omits the action without a label', () => {
        renderPage(
            <AdminEmptyState
                label="No events"
                description="Add the first."
                actionHref="/admin/events/create"
            />,
        );

        expect(screen.queryByRole('link')).not.toBeInTheDocument();
    });

    it('omits the action without an href', () => {
        renderPage(
            <AdminEmptyState
                label="No events"
                description="Add the first."
                actionLabel="New event"
            />,
        );

        expect(screen.queryByRole('link')).not.toBeInTheDocument();
    });
});

describe('AdminTableWrap', () => {
    it('wraps its children', () => {
        renderPage(
            <AdminTableWrap>
                <p>table</p>
            </AdminTableWrap>,
        );

        expect(screen.getByText('table')).toBeInTheDocument();
    });
});
