/* eslint-disable @typescript-eslint/no-explicit-any */
declare module 'ziggy-js' {
    export interface Config {
        url: string;
        port: number | null;
        defaults: Record<string, unknown>;
        routes: Record<string, unknown>;
    }

    export function route(
        name?: string,
        params?: Record<string, any> | any,
        absolute?: boolean,
        config?: Config,
    ): string;
}

declare global {
    function route(
        name?: string,
        params?: Record<string, unknown> | unknown,
        absolute?: boolean,
    ): string;
}

export {};
