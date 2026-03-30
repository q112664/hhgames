import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import HomeNavbar from '@/components/home-navbar';
import SiteFooter from '@/components/site-footer';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const settingsNavItems: NavItem[] = [
    {
        title: '个人资料',
        href: edit(),
        icon: null,
    },
    {
        title: '安全设置',
        href: editSecurity(),
        icon: null,
    },
    {
        title: '外观设置',
        href: editAppearance(),
        icon: null,
    },
];

export default function FrontSettingsLayout({ children }: PropsWithChildren) {
    const { auth } = usePage().props;
    const { currentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="min-h-screen bg-background text-foreground">
            <HomeNavbar user={auth.user} />

            <main className="mx-auto flex w-full max-w-7xl flex-col gap-5 px-4 py-8 sm:px-6 lg:px-8">
                <header className="space-y-1">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        用户设置
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        管理你的个人资料、安全设置与主题偏好。
                    </p>
                </header>

                <nav
                    className="md:hidden"
                    aria-label="用户设置导航"
                >
                    <div className="grid w-full grid-cols-3 rounded-xl border bg-muted/50 p-1">
                        {settingsNavItems.map((item, index) => {
                            const isActive = currentUrl === toUrl(item.href);

                            return (
                                <Link
                                    key={`${toUrl(item.href)}-mobile-${index}`}
                                    href={item.href}
                                    className={cn(
                                        'flex h-9 min-w-0 items-center justify-center rounded-lg px-3 text-center text-sm font-medium transition-colors',
                                        isActive
                                            ? 'bg-background text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    {item.title}
                                </Link>
                            );
                        })}
                    </div>
                </nav>

                <div className="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <aside className="hidden h-fit md:block">
                        <nav
                            className="flex flex-col gap-1"
                            aria-label="用户设置导航"
                        >
                            {settingsNavItems.map((item, index) => (
                                <Button
                                    key={`${toUrl(item.href)}-${index}`}
                                    size="sm"
                                    variant="ghost"
                                    asChild
                                    className={cn('w-full justify-start', {
                                        'bg-muted':
                                            isCurrentOrParentUrl(item.href),
                                    })}
                                >
                                    <Link href={item.href}>{item.title}</Link>
                                </Button>
                            ))}
                        </nav>
                    </aside>

                    <div className="rounded-2xl border bg-card p-6 sm:p-8">
                        <section className="max-w-2xl space-y-12">
                            {children}
                        </section>
                    </div>
                </div>
            </main>

            <SiteFooter />
        </div>
    );
}
