import { Link, usePage } from '@inertiajs/react';
import HomeNavbar from '@/components/home-navbar';
import SiteFooter from '@/components/site-footer';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { User } from '@/types';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const page = usePage();
    const { auth } = page.props;
    const canRegister =
        page.component === 'auth/register'
            ? false
            : Boolean(page.props.canRegister ?? true);
    const user = (auth?.user ?? null) as User | null;

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <HomeNavbar user={user} canRegister={canRegister} />

            <main className="mx-auto flex w-full max-w-7xl flex-1 items-center px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <div className="mx-auto flex w-full max-w-md flex-col gap-6">
                    <div className="space-y-2 text-center">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {description}
                        </p>
                    </div>

                    <Card className="rounded-3xl">
                        <CardHeader className="sr-only">
                            <CardTitle>{title}</CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-6 py-6 sm:px-8 sm:py-8">
                            {children}
                        </CardContent>
                    </Card>

                    <div className="text-center text-sm text-muted-foreground">
                        <Link
                            href="/"
                            className="transition-colors hover:text-foreground"
                        >
                            返回首页
                        </Link>
                    </div>
                </div>
            </main>

            <SiteFooter />
        </div>
    );
}
