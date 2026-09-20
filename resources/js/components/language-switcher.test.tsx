import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { LanguageSwitcher } = await import('@/components/language-switcher');

const translations = { 'nav.language': 'Language' };

beforeEach(() => {
    vi.clearAllMocks();
});

describe('LanguageSwitcher', () => {
    it('shows the current locale', () => {
        renderPage(<LanguageSwitcher />, { translations });

        expect(
            screen.getByRole('button', { name: 'Language' }),
        ).toHaveTextContent('en');
    });

    it('lists every available locale', async () => {
        const user = userEvent.setup();
        renderPage(<LanguageSwitcher />, { translations });

        await user.click(screen.getByRole('button', { name: 'Language' }));

        expect(
            screen.getByRole('option', { name: /English/ }),
        ).toBeInTheDocument();
        expect(screen.getByRole('option', { name: /বাংলা/ })).toBeInTheDocument();
    });

    it('switches to another locale', async () => {
        const user = userEvent.setup();
        renderPage(<LanguageSwitcher />, { translations });

        await user.click(screen.getByRole('button', { name: 'Language' }));
        await user.click(screen.getByRole('option', { name: /বাংলা/ }));

        expect(routerMock.post).toHaveBeenCalledWith(
            '/locale',
            { locale: 'bn' },
            { preserveScroll: true },
        );
    });

    it('ignores selecting the current locale', async () => {
        const user = userEvent.setup();
        renderPage(<LanguageSwitcher />, { translations });

        await user.click(screen.getByRole('button', { name: 'Language' }));
        await user.click(screen.getByRole('option', { name: /English/ }));

        expect(routerMock.post).not.toHaveBeenCalled();
    });

    it('renders bare without the cluster wrapper', () => {
        const { container } = renderPage(<LanguageSwitcher bare />, {
            translations,
        });

        expect(container.firstElementChild).not.toHaveClass('divide-x');
    });

    it('merges a custom class name onto the button', () => {
        renderPage(<LanguageSwitcher className="custom" />, { translations });

        expect(screen.getByRole('button', { name: 'Language' })).toHaveClass(
            'custom',
        );
    });
});
