import { Head, Link, usePage } from '@inertiajs/react';
import HomeNavbar from '@/components/home-navbar';
import ResourceDetailSections from '@/components/resource-detail-sections';
import ResourceOverviewCard from '@/components/resource-overview-card';
import SiteFooter from '@/components/site-footer';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import resources from '@/routes/resources';
import type {
    ResourceOverviewData,
    ResourceSectionData,
    ResourceDetailSection,
} from '@/types';

export default function ResourceShow({
    resource,
    section,
    sectionData,
}: {
    resource: ResourceOverviewData;
    section: ResourceDetailSection;
    sectionData: ResourceSectionData;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title={resource.title} />

            <div className="min-h-screen bg-background text-foreground">
                <HomeNavbar user={auth.user} />

                <main className="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
                    <Breadcrumb>
                        <BreadcrumbList>
                            <BreadcrumbItem>
                                <BreadcrumbLink asChild>
                                    <Link href="/">主页</Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbLink asChild>
                                    <Link
                                        href={resources.index({
                                            query: {
                                                category: resource.category,
                                            },
                                        }).url}
                                    >
                                        {resource.category}
                                    </Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbPage>{resource.title}</BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>

                    <ResourceOverviewCard resource={resource} />
                    <ResourceDetailSections
                        resourceSlug={resource.slug}
                        section={section}
                        sectionData={sectionData}
                    />
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
