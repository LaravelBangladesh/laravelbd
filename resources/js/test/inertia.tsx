import type { ReactNode } from 'react';
import { vi } from 'vitest';
import type { Auth, User } from '@/types/auth';
import type { SharedSeo } from '@/types/seo';

export type SharedProps = {
    name: string;
    version: string;
    auth: Auth;
    locale: string;
    locales: Record<string, string>;
    translations: Record<string, string>;
    seo: SharedSeo;
    [key: string]: unknown;
};

export const testUser: User = {
    id: 'user-1',
    name: 'Ada Lovelace',
    email: 'ada@example.test',
    pending_email: null,
    role: 'member',
    locale: 'en',
    is_staff: false,
    is_admin: false,
    photo_url: '/images/ada.jpg',
};

export const staffUser: User = {
    ...testUser,
    id: 'user-2',
    name: 'Grace Hopper',
    email: 'grace@example.test',
    role: 'admin',
    is_staff: true,
    is_admin: true,
};

export function sharedProps(overrides: Record<string, unknown> = {}) {
    const { auth, ...rest } = overrides as {
        auth?: Partial<Auth>;
    } & Record<string, unknown>;

    return {
        name: 'Laravel Bangladesh',
        version: '1.2.3',
        auth: { user: null, ...auth },
        locale: 'en',
        locales: { en: 'English', bn: 'বাংলা' },
        translations: {},
        seo: {
            url: 'https://laravelbd.test/',
            default_image: 'https://laravelbd.test/images/og-default.webp',
            site_name: 'Laravel Bangladesh',
        },
        ...rest,
    } as SharedProps;
}

/**
 * Mutable page state backing the `@inertiajs/react` mock. Tests set this
 * through `setPage` before rendering.
 */
export const pageState: { props: SharedProps; url: string } = {
    props: sharedProps(),
    url: '/',
};

export function setPage(props: Record<string, unknown> = {}, url = '/') {
    pageState.props = sharedProps(props);
    pageState.url = url;
}

export const routerMock = {
    post: vi.fn(),
    get: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
    visit: vi.fn(),
    reload: vi.fn(),
    on: vi.fn(() => () => {}),
};

export const formSubmitSpy = vi.fn();

/**
 * Server-side validation errors handed to the mocked `Form` render bag, so a
 * test can assert the inline error a real failed submit would produce.
 */
export const formErrors: { current: Record<string, string> } = { current: {} };

export function setFormErrors(errors: Record<string, string> = {}) {
    formErrors.current = errors;
}

export function resetInertiaMocks() {
    setPage();
    Object.values(routerMock).forEach((fn) => {
        fn.mockClear();
    });
    routerMock.on.mockImplementation(() => () => {});
    formSubmitSpy.mockClear();
    setFormErrors();
}

type LinkProps = {
    href?: unknown;
    children?: ReactNode;
    as?: string;
    method?: string;
    [key: string]: unknown;
};

/**
 * The module factory passed to `vi.mock('@inertiajs/react', ...)`. Kept as a
 * factory so each test file gets its own module registry entry while sharing
 * this implementation.
 */
export function inertiaMock() {
    return {
        usePage: () => pageState,
        router: routerMock,
        Head: ({
            title,
            children,
        }: {
            title?: string;
            children?: ReactNode;
        }) => (
            <>
                {title === undefined ? null : <title>{title}</title>}
                {children}
            </>
        ),
        Link: ({
            href,
            children,
            as: _as,
            method: _method,
            ...rest
        }: LinkProps) => (
            <a
                href={
                    typeof href === 'string'
                        ? href
                        : ((href as { url?: string } | undefined)?.url ?? '#')
                }
                {...rest}
            >
                {children}
            </a>
        ),
        Form: ({
            children,
            className,
            noValidate,
            ...rest
        }: {
            children?: ReactNode | ((bag: unknown) => ReactNode);
            className?: string;
            noValidate?: boolean;
            [key: string]: unknown;
        }) => {
            const bag = {
                errors: formErrors.current,
                processing: false,
                hasErrors: false,
                progress: null,
                wasSuccessful: false,
                recentlySuccessful: false,
                reset: vi.fn(),
                clearErrors: vi.fn(),
                setError: vi.fn(),
                submit: formSubmitSpy,
            };

            return (
                <form
                    className={className}
                    noValidate={noValidate}
                    onSubmit={(event) => {
                        event.preventDefault();
                        formSubmitSpy(rest);
                    }}
                >
                    {typeof children === 'function' ? children(bag) : children}
                </form>
            );
        },
        useForm: () => ({
            data: {},
            setData: vi.fn(),
            post: vi.fn(),
            put: vi.fn(),
            patch: vi.fn(),
            delete: vi.fn(),
            processing: false,
            errors: {},
            reset: vi.fn(),
            clearErrors: vi.fn(),
        }),
        WhenVisible: ({ children }: { children?: ReactNode }) => (
            <>{children}</>
        ),
        Deferred: ({ children }: { children?: ReactNode }) => <>{children}</>,
    };
}
