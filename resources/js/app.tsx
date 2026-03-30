import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { Toaster } from 'sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import '../css/app.css';
import { initializeTheme } from '@/hooks/use-appearance';

let appName = import.meta.env.VITE_APP_NAME || 'Laravel';

initializeTheme();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        appName =
            props.initialPage.props.site?.name ??
            props.initialPage.props.name ??
            appName;

        const root = createRoot(el);

        root.render(
            <StrictMode>
                <TooltipProvider delayDuration={0}>
                    <App {...props} />
                    <Toaster richColors position="bottom-center" />
                </TooltipProvider>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
