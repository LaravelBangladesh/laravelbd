export type SharedSeo = {
    url: string;
    default_image: string;
    site_name: string;
};

export type SeoType = 'website' | 'article' | 'profile' | 'event';

export type JsonLd = Record<string, unknown>;
