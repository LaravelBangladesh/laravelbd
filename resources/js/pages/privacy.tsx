import { LegalPage } from '@/components/legal-page';
import type { JsonLd } from '@/types/seo';

export default function Privacy({ json_ld }: { json_ld: JsonLd[] }) {
    return <LegalPage prefix="privacy" sections={12} jsonLd={json_ld} />;
}
