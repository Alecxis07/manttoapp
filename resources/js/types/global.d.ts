/// <reference types="vite/client" />

import type { route as routeFn } from 'ziggy-js';
import type { AppPageProps } from './page';

declare module '*.vue' {
    import type { DefineComponent } from 'vue';

    const component: DefineComponent<object, object, unknown>;
    export default component;
}

declare module 'laravel-vite-plugin/inertia-helpers' {
    import type { DefineComponent } from 'vue';

    export function resolvePageComponent(
        path: string,
        pages: Record<string, DefineComponent | (() => Promise<DefineComponent>)>,
    ): Promise<DefineComponent>;
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: AppPageProps;
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
    }
}

declare global {
    const route: typeof routeFn;
}

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T = unknown>(pattern: string) => Record<string, T>;
}

export {};
