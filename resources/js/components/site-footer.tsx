import { usePage } from '@inertiajs/react';

export default function SiteFooter() {
    const { site } = usePage().props;

    return (
        <footer className="border-t">
            <div className="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-6 text-sm text-muted-foreground sm:px-6 lg:px-8">
                <div className="flex min-w-0 items-center gap-3">
                    <div className="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-muted">
                        {site.logo ? (
                            <img
                                src={site.logo}
                                alt={site.name}
                                className="size-full object-cover"
                            />
                        ) : (
                            <span className="text-xs font-semibold text-foreground/80">
                                {site.name
                                    .trim()
                                    .split(/\s+/)
                                    .filter(Boolean)
                                    .slice(0, 2)
                                    .map((part) => part.charAt(0))
                                    .join('')
                                    .toUpperCase()}
                            </span>
                        )}
                    </div>
                    <a
                        href={site.url}
                        className="truncate text-foreground transition-colors hover:text-primary"
                    >
                        {site.name}
                    </a>
                </div>
                <p>精选游戏资源整理与更新。</p>
            </div>
        </footer>
    );
}
