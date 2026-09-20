import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { cropImage, flipImage } from '@/lib/crop-image';

const crop = { x: 0, y: 0, width: 100, height: 50 };

type ImageHandlers = Record<string, () => void>;

let imageHandlers: ImageHandlers;
let imageFails: boolean;

class FakeImage {
    width = 200;
    height = 100;
    crossOrigin = '';
    private handlers: ImageHandlers = {};

    addEventListener(event: string, handler: () => void) {
        this.handlers[event] = handler;
        imageHandlers = this.handlers;
    }

    set src(_value: string) {
        queueMicrotask(() => this.handlers[imageFails ? 'error' : 'load']?.());
    }
}

function stubCanvas({
    context = {},
    blob = new Blob(['x'], { type: 'image/jpeg' }) as Blob | null,
    nullContextAfter = Number.POSITIVE_INFINITY,
}: {
    context?: Record<string, unknown>;
    blob?: Blob | null;
    nullContextAfter?: number;
} = {}) {
    let contexts = 0;

    vi.spyOn(HTMLCanvasElement.prototype, 'getContext').mockImplementation(
        () =>
            contexts++ >= nullContextAfter
                ? null
                : ({
                      translate: vi.fn(),
                      scale: vi.fn(),
                      rotate: vi.fn(),
                      drawImage: vi.fn(),
                      ...context,
                  } as unknown as CanvasRenderingContext2D),
    );

    vi.spyOn(HTMLCanvasElement.prototype, 'toBlob').mockImplementation(
        (callback) => {
            callback(blob);
        },
    );
}

beforeEach(() => {
    imageFails = false;
    imageHandlers = {};
    vi.stubGlobal('Image', FakeImage);
    vi.stubGlobal('URL', {
        ...URL,
        createObjectURL: vi.fn(() => 'blob:cropped'),
    });
});

afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});

describe('flipImage', () => {
    it('returns an object url for the flipped image', async () => {
        stubCanvas();

        await expect(flipImage('blob:source')).resolves.toBe('blob:cropped');
    });

    it('rejects when the image cannot be read', async () => {
        imageFails = true;
        stubCanvas();

        await expect(flipImage('bad')).rejects.toThrow(
            'Unable to read the image.',
        );
    });

    it('rejects when no canvas context is available', async () => {
        stubCanvas({ nullContextAfter: 0 });

        await expect(flipImage('blob:source')).rejects.toThrow(
            'Unable to flip the image.',
        );
    });

    it('rejects when the canvas produces no blob', async () => {
        stubCanvas({ blob: null });

        await expect(flipImage('blob:source')).rejects.toThrow(
            'Unable to flip the image.',
        );
    });

    it('registers a load handler', async () => {
        stubCanvas();
        await flipImage('blob:source');

        expect(imageHandlers.load).toBeTypeOf('function');
    });
});

describe('cropImage', () => {
    it('resolves to the cropped blob', async () => {
        stubCanvas();

        const result = await cropImage({ src: 'blob:source', crop });

        expect(result).toBeInstanceOf(Blob);
    });

    it('honours a rotation and a custom mime type', async () => {
        stubCanvas();

        const result = await cropImage({
            src: 'blob:source',
            crop,
            rotation: 90,
            mimeType: 'image/png',
        });

        expect(result).toBeInstanceOf(Blob);
    });

    it('scales down a crop larger than the maximum edge', async () => {
        stubCanvas();

        const result = await cropImage({
            src: 'blob:source',
            crop: { x: 0, y: 0, width: 5000, height: 2000 },
        });

        expect(result).toBeInstanceOf(Blob);
    });

    it('rejects when the working canvas has no context', async () => {
        stubCanvas({ nullContextAfter: 0 });

        await expect(cropImage({ src: 'blob:source', crop })).rejects.toThrow(
            'Unable to crop the image.',
        );
    });

    it('rejects when the output canvas has no context', async () => {
        stubCanvas({ nullContextAfter: 1 });

        await expect(cropImage({ src: 'blob:source', crop })).rejects.toThrow(
            'Unable to crop the image.',
        );
    });

    it('rejects when the output canvas produces no blob', async () => {
        stubCanvas({ blob: null });

        await expect(cropImage({ src: 'blob:source', crop })).rejects.toThrow(
            'Unable to crop the image.',
        );
    });
});
