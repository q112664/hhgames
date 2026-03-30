import type { Auth } from '@/types/auth';
import type { SiteConfig } from '@/types/site';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            site: SiteConfig;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
