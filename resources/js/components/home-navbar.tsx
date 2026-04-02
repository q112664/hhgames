import { Link, usePage } from '@inertiajs/react';
import {
    Compass,
    Flame,
    Grid2x2,
    House,
    Link2,
    type LucideIcon,
    LogIn,
    Menu,
    Moon,
    Sun,
    SunMoon,
    UserPlus,
    X,
} from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { UserMenuContent } from '@/components/user-menu-content';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import { login, register } from '@/routes';
import type { SiteConfig, SiteNavigationItem, User } from '@/types';

type HomeNavbarProps = {
    canRegister?: boolean;
    user?: User | null;
};

type ResolvedSiteNavigationItem = SiteNavigationItem & {
    hrefKey: string;
    pathname: string;
    queryString: string;
    icon: LucideIcon;
};

const navbarButtonClass =
    'focus-visible:ring-0 focus-visible:ring-offset-0 focus-visible:border-transparent focus-visible:outline-none';

const mobileNavItemClass =
    'h-11 justify-start rounded-xl px-3 text-sm font-medium hover:bg-primary/10 hover:text-primary';

const mobileNavItemActiveClass = 'bg-primary/10 text-primary hover:bg-primary/15';
const fallbackNavigationItems: SiteNavigationItem[] = [
    { label: '首页', href: '/', group: '站点入口' },
    { label: '全部资源', href: '/resources', group: '站点入口' },
    {
        label: '最新资源',
        href: '/resources?sort=latest',
        group: '资源浏览',
    },
    {
        label: '热门资源',
        href: '/resources?sort=popular',
        group: '资源浏览',
    },
];
const preferredMobileNavGroupOrder: readonly string[] = ['站点入口', '资源浏览'];

const appearanceItems: {
    value: Appearance;
    label: string;
    icon: typeof Sun;
}[] = [
    { value: 'light', label: '浅色', icon: Sun },
    { value: 'dark', label: '深色', icon: Moon },
    { value: 'system', label: '跟随系统', icon: SunMoon },
];

function ThemeToggleMenu() {
    const { appearance, updateAppearance } = useAppearance();
    const currentAppearance =
        appearanceItems.find((item) => item.value === appearance) ??
        appearanceItems[2];
    const CurrentIcon = currentAppearance.icon;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className={cn('rounded-full', navbarButtonClass)}
                >
                    <CurrentIcon className="size-5" />
                    <span className="sr-only">Toggle theme</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="min-w-40 rounded-lg" align="end">
                <DropdownMenuLabel>主题</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuRadioGroup
                    value={appearance}
                    onValueChange={(value) =>
                        updateAppearance(value as Appearance)
                    }
                >
                    {appearanceItems.map(({ value, label, icon: Icon }) => (
                        <DropdownMenuRadioItem key={value} value={value}>
                            <Icon className="size-[1.1rem]" />
                            {label}
                        </DropdownMenuRadioItem>
                    ))}
                </DropdownMenuRadioGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function UserAvatarMenu({ user }: { user: User }) {
    const getInitials = useInitials();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className={cn('rounded-full', navbarButtonClass)}
                >
                    <Avatar className="size-8.5">
                        <AvatarImage src={user.avatar} alt={user.name} />
                        <AvatarFallback>
                            {getInitials(user.name)}
                        </AvatarFallback>
                    </Avatar>
                    <span className="sr-only">Open user menu</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="min-w-56 rounded-lg" align="end">
                <UserMenuContent user={user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function getSiteInitials(name: string) {
    return name
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase();
}

function SiteBrand({ site }: { site: SiteConfig }) {
    return (
        <Link href="/" className="flex min-w-0 items-center gap-3">
            <div className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-muted">
                {site.logo ? (
                    <img
                        src={site.logo}
                        alt={site.name}
                        className="size-full object-cover"
                    />
                ) : (
                    <span className="text-xs font-semibold text-foreground/80">
                        {getSiteInitials(site.name)}
                    </span>
                )}
            </div>

            <span className="truncate text-lg font-semibold">{site.name}</span>
        </Link>
    );
}

function normalizeMenuItems(items: SiteNavigationItem[]) {
    return (items.length > 0 ? items : fallbackNavigationItems).map((item) => {
        const itemUrl = new URL(item.href, 'http://localhost');
        const pathname = itemUrl.pathname;
        const queryString = itemUrl.searchParams.toString();
        let icon: LucideIcon = Link2;

        if (pathname === '/') {
            icon = House;
        } else if (
            pathname === '/resources' &&
            itemUrl.searchParams.get('sort') === 'latest'
        ) {
            icon = Compass;
        } else if (
            pathname === '/resources' &&
            itemUrl.searchParams.get('sort') === 'popular'
        ) {
            icon = Flame;
        } else if (pathname === '/resources' && queryString === '') {
            icon = Grid2x2;
        }

        return {
            ...item,
            hrefKey: `${item.group}-${item.label}-${item.href}`,
            pathname,
            queryString,
            icon,
        };
    });
}

function isItemActive(
    item: ResolvedSiteNavigationItem,
    pathname: string,
    currentQuery: string,
) {
    if (item.pathname === '/' && item.queryString === '') {
        return pathname === '/';
    }

    if (item.pathname === '/resources' && item.queryString === '') {
        return pathname === '/resources' && currentQuery === '';
    }

    return pathname === item.pathname && currentQuery === item.queryString;
}

function buildMobileNavGroups(items: ResolvedSiteNavigationItem[]) {
    const titles = [
        ...preferredMobileNavGroupOrder.filter((group) =>
            items.some((item) => item.group === group),
        ),
        ...Array.from(new Set(items.map((item) => item.group))).filter(
            (group) => !preferredMobileNavGroupOrder.includes(group),
        ),
    ];

    return titles.map((title) => ({
        title,
        items: items.filter((item) => item.group === title),
    }));
}

function DesktopNavigation({
    items,
    pathname,
    currentQuery,
}: {
    items: ResolvedSiteNavigationItem[];
    pathname: string;
    currentQuery: string;
}) {
    return (
        <nav className="hidden items-center lg:flex">
            <NavigationMenu viewport={false}>
                <NavigationMenuList className="gap-1.5">
                    {items.map((item) => (
                        <NavigationMenuItem key={item.hrefKey}>
                            <Link
                                href={item.href}
                                className={cn(
                                    navigationMenuTriggerStyle(),
                                    navbarButtonClass,
                                    isItemActive(item, pathname, currentQuery) &&
                                        'bg-muted text-foreground',
                                )}
                            >
                                {item.label}
                            </Link>
                        </NavigationMenuItem>
                    ))}
                </NavigationMenuList>
            </NavigationMenu>
        </nav>
    );
}

function MobileNavigation({
    canRegister,
    user,
    pathname,
    currentQuery,
    site,
    items,
}: Pick<HomeNavbarProps, 'canRegister' | 'user'> & {
    pathname: string;
    currentQuery: string;
    site: SiteConfig;
    items: ResolvedSiteNavigationItem[];
}) {
    const groups = buildMobileNavGroups(items);

    return (
        <div className="flex items-center lg:hidden">
            <Sheet>
                <SheetTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className={navbarButtonClass}
                    >
                        <Menu />
                        <span className="sr-only">Open navigation menu</span>
                    </Button>
                </SheetTrigger>

                <SheetContent
                    side="left"
                    showCloseButton={false}
                    className="gap-0 data-[side=left]:w-[19rem] max-sm:data-[side=left]:w-[64vw]"
                >
                    <SheetHeader className="border-b px-4 py-3">
                        <div className="flex items-center gap-3">
                            <Avatar className="size-9 rounded-xl">
                                {site.logo ? (
                                    <AvatarImage src={site.logo} alt={site.name} />
                                ) : null}
                                <AvatarFallback className="rounded-xl text-xs font-semibold">
                                    {getSiteInitials(site.name)}
                                </AvatarFallback>
                            </Avatar>

                            <div className="min-w-0 flex-1">
                                <SheetTitle className="truncate text-base">
                                    {site.name}
                                </SheetTitle>
                                <SheetDescription className="sr-only">
                                    站点移动端导航菜单
                                </SheetDescription>
                            </div>

                            <SheetClose asChild>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    className={navbarButtonClass}
                                >
                                    <X />
                                    <span className="sr-only">
                                        Close navigation menu
                                    </span>
                                </Button>
                            </SheetClose>
                        </div>
                    </SheetHeader>

                    <div className="flex flex-1 flex-col gap-6 overflow-y-auto px-3 py-4">
                        {groups.map((group, index) => (
                            <div key={group.title} className="flex flex-col gap-2">
                                {index > 0 && <Separator className="mb-2" />}

                                <nav className="flex flex-col gap-1">
                                    {group.items.map((item) => {
                                        const Icon = item.icon;

                                        return (
                                            <SheetClose
                                                key={item.hrefKey}
                                                asChild
                                            >
                                                <Button
                                                    asChild
                                                    variant="ghost"
                                                    size="lg"
                                                    className={cn(
                                                        mobileNavItemClass,
                                                        isItemActive(
                                                            item,
                                                            pathname,
                                                            currentQuery,
                                                        ) &&
                                                            mobileNavItemActiveClass,
                                                    )}
                                                >
                                                    <Link href={item.href}>
                                                        <Icon data-icon="inline-start" />
                                                        {item.label}
                                                    </Link>
                                                </Button>
                                            </SheetClose>
                                        );
                                    })}
                                </nav>
                            </div>
                        ))}

                        {!user && (
                            <div className="flex flex-col gap-2">
                                <Separator />

                                <div className="px-2 pt-3 text-xs font-semibold tracking-wide text-muted-foreground">
                                    账户
                                </div>

                                <div className="flex flex-col gap-1">
                                    <SheetClose asChild>
                                        <Button
                                            asChild
                                            variant="ghost"
                                            size="lg"
                                            className={mobileNavItemClass}
                                        >
                                            <Link href={login()}>
                                                <LogIn data-icon="inline-start" />
                                                登录
                                            </Link>
                                        </Button>
                                    </SheetClose>

                                    {canRegister && (
                                        <SheetClose asChild>
                                            <Button
                                                asChild
                                                variant="ghost"
                                                size="lg"
                                                className={mobileNavItemClass}
                                            >
                                                <Link href={register()}>
                                                    <UserPlus data-icon="inline-start" />
                                                    注册
                                                </Link>
                                            </Button>
                                        </SheetClose>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    );
}

function DesktopActions({
    canRegister,
    user,
}: Pick<HomeNavbarProps, 'canRegister' | 'user'>) {
    if (user) {
        return (
            <div className="hidden items-center gap-3 lg:flex">
                <ThemeToggleMenu />
                <UserAvatarMenu user={user} />
            </div>
        );
    }

    return (
        <div className="hidden items-center gap-3 lg:flex">
            <ThemeToggleMenu />
            <Button asChild variant="ghost" className={navbarButtonClass}>
                <Link href={login()}>登录</Link>
            </Button>
            {canRegister && (
                <Button asChild className={navbarButtonClass}>
                    <Link href={register()}>注册</Link>
                </Button>
            )}
        </div>
    );
}

export default function HomeNavbar({
    canRegister = true,
    user,
}: HomeNavbarProps) {
    const page = usePage();
    const currentUrl = new URL(page.url, 'http://localhost');
    const pathname = currentUrl.pathname;
    const currentQuery = currentUrl.searchParams.toString();
    const site = page.props.site;
    const navItems = normalizeMenuItems(site.navigation);

    return (
        <header className="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div className="mx-auto flex w-full max-w-7xl items-center gap-5 px-4 py-3 sm:px-6 lg:px-8">
                <MobileNavigation
                    canRegister={canRegister}
                    user={user}
                    pathname={pathname}
                    currentQuery={currentQuery}
                    site={site}
                    items={navItems}
                />

                <div className="flex-1 lg:flex-none">
                    <SiteBrand site={site} />
                </div>

                <div className="flex items-center gap-3 lg:hidden">
                    <ThemeToggleMenu />
                    {user && <UserAvatarMenu user={user} />}
                </div>

                <div className="hidden flex-1 justify-center lg:flex">
                    <DesktopNavigation
                        items={navItems}
                        pathname={pathname}
                        currentQuery={currentQuery}
                    />
                </div>

                <DesktopActions canRegister={canRegister} user={user} />
            </div>
        </header>
    );
}
