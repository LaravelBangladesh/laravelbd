import { execSync } from 'node:child_process';
import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import babel from '@rolldown/plugin-babel';
import tailwindcss from '@tailwindcss/vite';
import react, { reactCompilerPreset } from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';

function wayfinderCommand(): string {
    if (process.env.WAYFINDER_COMMAND) {
        return process.env.WAYFINDER_COMMAND;
    }

    try {
        execSync('php -v', { stdio: 'ignore' });

        return 'php artisan wayfinder:generate';
    } catch {
        // Node-only Docker images generate types from the PHP container instead.
        return 'true';
    }
}

const isTest = process.env.VITEST === 'true';

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Figtree', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        inertia(),
        react(),
        // The React Compiler's generated memoization caches are not source
        // code, so they are skipped under test to keep coverage honest.
        ...(isTest
            ? []
            : [
                  babel({
                      presets: [reactCompilerPreset()],
                  }),
              ]),
        tailwindcss(),
        wayfinder({
            command: wayfinderCommand(),
            formVariants: true,
        }),
    ]),
    server: {
        host: '0.0.0.0',
        port: 5173,
        // The php-fpm container reaches the dev server (and its Inertia SSR
        // endpoint) over the compose network, where the Host header is the
        // service name rather than localhost.
        allowedHosts: ['vite'],
        hmr: {
            host: process.env.VITE_HMR_HOST ?? 'localhost',
        },
        watch: {
            usePolling: true,
            interval: 300,
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
                '**/storage/**',
                '**/bootstrap/cache/**',
                '**/bootstrap/ssr/**',
                '**/public/hot',
                '**/public/build/**',
                '**/public/fonts-manifest.dev.json',
            ],
        },
    },
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['resources/js/test/setup.ts'],
        include: ['resources/js/**/*.test.{ts,tsx}'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json-summary'],
            reportsDirectory: 'coverage/js',
            include: ['resources/js/**/*.{ts,tsx}'],
            exclude: [
                'resources/js/actions/**',
                'resources/js/routes/**',
                'resources/js/wayfinder/**',
                'resources/js/**/*.test.{ts,tsx}',
                'resources/js/test/**',
                'resources/js/app.tsx',
                'resources/js/ssr.tsx',
            ],
            thresholds: {
                statements: 100,
                branches: 100,
                functions: 100,
                lines: 100,
            },
        },
    },
    lint: {
        ignorePatterns: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'tailwind.config.js',
            'resources/js/actions/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: 'css',
        ignorePatterns: [
            '.github/**',
            'composer.json',
            'resources/views/mail/*',
        ],
        sortTailwindcss: {
            functions: ['clsx', 'cn', 'cva'],
            entryPoint: 'resources/css/app.css',
        },
    },
});
