import { LegalPage } from '@/components/legal-page';
import type { JsonLd } from '@/types/seo';

export default function Terms({ json_ld }: { json_ld: JsonLd[] }) {
    return <LegalPage prefix="terms" sections={13} jsonLd={json_ld} />;
}
