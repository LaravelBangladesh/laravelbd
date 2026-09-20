import '@testing-library/jest-dom/vitest';

// jsdom has no ResizeObserver; Headless UI's overlays need one to measure.
if (!('ResizeObserver' in globalThis)) {
    globalThis.ResizeObserver = class {
        observe() {}
        unobserve() {}
        disconnect() {}
    };
}

// jsdom does not implement scrollIntoView, which form validation calls when
// it focuses the first invalid field.
if (!Element.prototype.scrollIntoView) {
    Element.prototype.scrollIntoView = () => {};
}
