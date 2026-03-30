import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';
import { Toaster } from 'sonner';
import { TooltipProvider } from '@/components/ui/tooltip';

let appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) => {
    appName =
        (page.props as { site?: { name?: string }; name?: string }).site?.name ??
        (page.props as { name?: string }).name ??
        appName;

    return createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) =>
            resolvePageComponent(
                `./pages/${name}.tsx`,
                import.meta.glob('./pages/**/*.tsx'),
            ),
        setup: ({ App, props }) => {
            return (
                <TooltipProvider delayDuration={0}>
                    <App {...props} />
                    <Toaster richColors position="bottom-center" />
                </TooltipProvider>
            );
        },
    });
});
