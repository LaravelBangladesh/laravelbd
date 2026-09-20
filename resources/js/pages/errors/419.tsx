import { Seo } from '@/components/seo';
import {
    actionRowClass,
    Button,
    Container,
    Display,
    Eyebrow,
    Lead,
    Mesh,
    Section,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';

export default function PageExpired() {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t('errors.419.title')}
                description={t('meta.page_expired')}
                noindex
            />
            <Section className="relative overflow-hidden">
                <Mesh />
                <Container className="relative py-20 sm:py-28">
                    <Eyebrow>{t('errors.419.eyebrow')}</Eyebrow>
                    <p className="text-brand-red mt-6 text-7xl font-semibold tracking-[-0.08em] sm:text-8xl">
                        419
                    </p>
                    <Display as="h1" className="mt-4 max-w-xl">
                        {t('errors.419.title')}
                    </Display>
                    <Lead className="mt-5 max-w-xl">
                        {t('errors.419.lead')}
                    </Lead>
                    <div className={`${actionRowClass} mt-10`}>
                        <Button href="/">{t('errors.419.home')}</Button>
                        <Button
                            variant="outline"
                            onClick={() => window.location.reload()}
                        >
                            {t('errors.419.retry')}
                        </Button>
                    </div>
                </Container>
            </Section>
        </>
    );
}
