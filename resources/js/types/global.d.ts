/// <reference types="vite/client" />

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

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T = unknown>(pattern: string) => Record<string, T>;
}
