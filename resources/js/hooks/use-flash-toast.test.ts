import { renderHook } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock } from '@/test/inertia';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);
vi.mock('sonner', () => ({
    toast: {
        success: vi.fn(),
        info: vi.fn(),
        warning: vi.fn(),
        error: vi.fn(),
    },
}));

const { useFlashToast } = await import('@/hooks/use-flash-toast');
const { toast } = await import('sonner');

function emit(detail: unknown) {
    const calls = routerMock.on.mock.calls as unknown as [
        string,
        (event: unknown) => void,
    ][];
    const listener = calls[calls.length - 1][1];

    listener({ detail });
}

describe('useFlashToast', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        routerMock.on.mockImplementation(() => () => {});
    });

    it('subscribes to the flash event', () => {
        renderHook(() => useFlashToast());

        expect(routerMock.on).toHaveBeenCalledWith(
            'flash',
            expect.any(Function),
        );
    });

    it.each(['success', 'info', 'warning', 'error'] as const)(
        'shows a %s toast',
        (type) => {
            renderHook(() => useFlashToast());

            emit({ flash: { toast: { type, message: `a ${type}` } } });

            expect(toast[type]).toHaveBeenCalledWith(`a ${type}`);
        },
    );

    it('ignores a flash without a toast', () => {
        renderHook(() => useFlashToast());

        emit({ flash: {} });

        expect(toast.success).not.toHaveBeenCalled();
    });

    it('ignores an event without a flash payload', () => {
        renderHook(() => useFlashToast());

        emit(undefined);

        expect(toast.success).not.toHaveBeenCalled();
    });

    it('unsubscribes on unmount', () => {
        const off = vi.fn();
        routerMock.on.mockReturnValue(off);

        const { unmount } = renderHook(() => useFlashToast());
        unmount();

        expect(off).toHaveBeenCalled();
    });
});
