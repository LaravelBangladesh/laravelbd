import { render } from '@testing-library/react';
import type { ReactElement } from 'react';
import { setPage } from '@/test/inertia';

/**
 * Renders `ui` after seeding the mocked Inertia page with `props`, so a test
 * can describe the server-shared props it cares about in one place.
 */
export function renderPage(
    ui: ReactElement,
    props: Record<string, unknown> = {},
    url = '/',
) {
    setPage(props, url);

    return render(ui);
}
