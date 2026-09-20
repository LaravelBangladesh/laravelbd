import type { Auth } from '@/types/auth';
import type { SharedSeo } from '@/types/seo';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            version: string;
            auth: Auth;
            locale: string;
            locales: Record<string, string>;
            translations: Record<string, string>;
            seo: SharedSeo;
            [key: string]: unknown;
        };
    }
}
