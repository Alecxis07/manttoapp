import { usePage as useInertiaPage } from '@inertiajs/vue3';
import type { AppPageProps } from '@/types/page';

export function useAppPage() {
    return useInertiaPage<AppPageProps>();
}
