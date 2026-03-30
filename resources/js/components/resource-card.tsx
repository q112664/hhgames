import { Link } from '@inertiajs/react';
import { Clock3, Download, Eye, Heart } from 'lucide-react';
import { Card } from '@/components/ui/card';
import type { ResourceCardData } from '@/types';

function createPlaceholderImage(title: string, category: string) {
    const palette = ['#e5e7eb', '#dbeafe', '#e9d5ff', '#fde68a'];
    const seed = `${title}${category}`
        .split('')
        .reduce((sum, char) => sum + char.charCodeAt(0), 0);
    const fill = palette[seed % palette.length];
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 400">
            <rect width="640" height="400" fill="${fill}" />
        </svg>
    `;

    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
}

function StatItem({
    icon: Icon,
    value,
    className,
}: {
    icon: typeof Eye;
    value: string;
    className?: string;
}) {
    return (
        <div className={className ?? 'flex items-center gap-1.5'}>
            <Icon className="size-3.25" />
            <span>{value}</span>
        </div>
    );
}

export default function ResourceCard({
    resource,
}: {
    resource: ResourceCardData;
}) {
    return (
        <Card className="group h-full gap-0 overflow-hidden rounded-xl border-border/70 bg-card py-0 shadow-sm transition-shadow hover:shadow-md sm:rounded-2xl">
            <Link href={resource.href} className="relative block overflow-hidden">
                <img
                    src={
                        resource.cover ??
                        createPlaceholderImage(
                            resource.title,
                            resource.category,
                        )
                    }
                    alt={resource.title}
                    className="block aspect-[4/3] w-full rounded-t-xl object-cover transition-transform duration-300 group-hover:scale-[1.02] sm:aspect-[16/10] sm:rounded-t-2xl"
                    loading="lazy"
                />

                <div className="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/78 via-black/44 to-transparent px-2.5 pb-2 pt-5 sm:px-3.5 sm:pb-2.5 sm:pt-7">
                    <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-medium text-white/95 [text-shadow:0_1px_2px_rgba(0,0,0,0.45)] sm:gap-x-2.5 sm:text-[12.5px]">
                        <StatItem
                            icon={Eye}
                            value={resource.stats.views}
                            className="flex items-center gap-1"
                        />
                        <StatItem
                            icon={Download}
                            value={resource.stats.downloads}
                            className="flex items-center gap-1"
                        />
                        <StatItem
                            icon={Heart}
                            value={resource.stats.favorites}
                            className="flex items-center gap-1"
                        />
                    </div>
                </div>
            </Link>

            <div className="flex min-h-[5.35rem] flex-1 flex-col gap-1.5 px-3 pt-2.5 pb-3 sm:min-h-[6.25rem] sm:gap-2 sm:px-4 sm:pt-3 sm:pb-3.5">
                <h3 className="line-clamp-2 text-[13px] leading-[1.35] font-medium sm:text-base sm:leading-[1.45]">
                    <Link
                        href={resource.href}
                        className="transition-colors hover:text-primary"
                    >
                        {resource.title}
                    </Link>
                </h3>

                <div className="mt-auto text-[11px] text-muted-foreground sm:text-[13px]">
                    <div className="flex w-full items-center gap-2 sm:gap-3">
                        <span className="min-w-0 truncate font-medium text-foreground/80">
                            {resource.category}
                        </span>
                        <div className="ml-auto flex shrink-0 items-center gap-1 sm:gap-1.5">
                            <Clock3 className="size-3 sm:size-3.5" />
                            <time dateTime={resource.publishedAt ?? undefined}>
                                {resource.publishedLabel ?? '刚刚'}
                            </time>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
