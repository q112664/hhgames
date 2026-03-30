import { Link } from '@inertiajs/react';
import ResourceCard from '@/components/resource-card';
import { Button } from '@/components/ui/button';
import type { ResourceCardData } from '@/types';

export default function LatestResourcesGrid({
    resources,
    viewAllHref,
}: {
    resources: ResourceCardData[];
    viewAllHref: string;
}) {
    return (
        <section
            id="latest"
            className="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
        >
            <div className="flex items-center justify-between gap-4">
                <h2 className="text-2xl font-semibold tracking-tight">
                    最新资源
                </h2>

                <Button asChild variant="outline" className="shrink-0">
                    <Link href={viewAllHref}>查看全部资源</Link>
                </Button>
            </div>

            {resources.length === 0 ? (
                <div className="mt-6 rounded-2xl border border-dashed px-6 py-12 text-center text-sm text-muted-foreground">
                    暂时还没有资源，稍后再来看看。
                </div>
            ) : (
                <div className="mt-6 grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
                    {resources.map((resource) => (
                        <ResourceCard key={resource.slug} resource={resource} />
                    ))}
                </div>
            )}
        </section>
    );
}
