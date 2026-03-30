import { Head, usePage } from '@inertiajs/react';
import HomeNavbar from '@/components/home-navbar';
import LatestResourcesGrid from '@/components/latest-resources-grid';
import SiteFooter from '@/components/site-footer';
import type { ResourceCardData } from '@/types';

export default function Welcome({
    canRegister = true,
    latestResources,
    resourcesIndexUrl,
}: {
    canRegister?: boolean;
    latestResources: ResourceCardData[];
    resourcesIndexUrl: string;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="" />

            <div className="min-h-screen bg-background text-foreground">
                <HomeNavbar canRegister={canRegister} user={auth.user} />
                <main>
                    <LatestResourcesGrid
                        resources={latestResources}
                        viewAllHref={resourcesIndexUrl}
                    />
                </main>
                <SiteFooter />
            </div>
        </>
    );
}
