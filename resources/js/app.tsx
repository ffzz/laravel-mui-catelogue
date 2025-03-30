// import '../css/app.css';
import '@fontsource/roboto/300.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import { createRoot } from 'react-dom/client';
import { ErrorBoundary } from 'react-error-boundary';
import { initializeTheme } from './hooks/use-appearance';
import QueryProvider from './lib/react-query/QueryProvider';
import ThemeProvider from './theme/ThemeProvider';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ErrorFallback from './components/ErrorFallback';

// Get application name from env
const appName = import.meta.env.VITE_APP_NAME || 'Laravel MUI Catalogue';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <ErrorBoundary FallbackComponent={ErrorFallback} onReset={() => window.location.reload()}>
                <ThemeProvider>
                    <QueryProvider>
                        {/* Your app routes or main component will go here */}
                        <App {...props} />
                    </QueryProvider>
                </ThemeProvider>
            </ErrorBoundary>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
