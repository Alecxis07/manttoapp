type ZiggyRouter = {
    current(name?: string, params?: unknown): boolean | string | undefined;
};

/**
 * Ziggy zero-arg `route()` can lose Router overloads under vue-tsc;
 * cast through a narrow Router shape for `current()` checks.
 */
export function isCurrentRoute(name: string): boolean {
    return (route as unknown as () => ZiggyRouter)().current(name) === true;
}
