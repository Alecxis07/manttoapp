<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import SideNavLink from '@/Components/SideNavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    title: String,
});

const page = usePage();
const sidebarOpen = ref(false);
const globalSearch = ref('');

watch(
    () => page.url,
    () => {
        sidebarOpen.value = false;
    },
);

const can = computed(() => page.props.can ?? {});

const navSections = computed(() => {
    // Track Inertia URL so active states update on navigation.
    void page.url;

    const sections = [
        {
            label: 'Operación',
            items: [
                {
                    key: 'dashboard',
                    label: 'Dashboard',
                    href: route('dashboard'),
                    active: route().current('dashboard'),
                    show: true,
                    icon: 'dashboard',
                },
                {
                    key: 'orders',
                    label: 'Órdenes',
                    href: route('maintenance-orders.index'),
                    active: route().current('maintenance-orders.*'),
                    show: !! can.value.viewMaintenanceOrders,
                    icon: 'orders',
                },
                {
                    key: 'quotations',
                    label: 'Cotizaciones',
                    href: route('quotations.index'),
                    active: route().current('quotations.*'),
                    show: !! can.value.viewQuotations,
                    icon: 'quotations',
                },
                {
                    key: 'billing',
                    label: 'Facturación',
                    href: route('billing-requests.index'),
                    active: route().current('billing-requests.*'),
                    show: !! can.value.viewBillingRequests,
                    icon: 'billing',
                },
                {
                    key: 'vehicles',
                    label: 'Unidades',
                    href: route('vehicles.index'),
                    active: route().current('vehicles.*') || route().current('customers.vehicles.*'),
                    show: !! can.value.viewVehicles,
                    icon: 'vehicles',
                },
                {
                    key: 'customers',
                    label: 'Clientes',
                    href: route('customers.index'),
                    active: route().current('customers.*') && ! route().current('customers.vehicles.*'),
                    show: !! can.value.viewCustomers,
                    icon: 'customers',
                },
            ],
        },
        {
            label: 'Catálogos',
            items: [
                {
                    key: 'categories',
                    label: 'Categorías',
                    href: route('service-categories.index'),
                    active: route().current('service-categories.*'),
                    show: !! can.value.manageServiceCategories,
                    icon: 'categories',
                },
                {
                    key: 'services',
                    label: 'Servicios',
                    href: route('service-catalog.index'),
                    active: route().current('service-catalog.*'),
                    show: !! can.value.manageServices,
                    icon: 'services',
                },
                {
                    key: 'parts',
                    label: 'Refacciones',
                    href: route('part-catalog.index'),
                    active: route().current('part-catalog.*'),
                    show: !! can.value.manageParts,
                    icon: 'parts',
                },
            ],
        },
        {
            label: 'Administración',
            items: [
                {
                    key: 'reports',
                    label: 'Reportes',
                    href: route('reports.index'),
                    active: route().current('reports.*') || route().current('search'),
                    show: !! can.value.viewReports,
                    icon: 'reports',
                },
                {
                    key: 'users',
                    label: 'Usuarios',
                    href: route('users.index'),
                    active: route().current('users.*'),
                    show: !! can.value.manageUsers,
                    icon: 'users',
                },
                {
                    key: 'audit',
                    label: 'Auditoría',
                    href: route('audit.index'),
                    active: route().current('audit.*'),
                    show: !! can.value.viewAudit,
                    icon: 'audit',
                },
                {
                    key: 'settings',
                    label: 'Configuración',
                    href: route('settings.edit'),
                    active: route().current('settings.*'),
                    show: !! can.value.manageSettings,
                    icon: 'settings',
                },
            ],
        },
    ];

    return sections
        .map((section) => ({
            ...section,
            items: section.items.filter((item) => item.show),
        }))
        .filter((section) => section.items.length > 0);
});

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const submitGlobalSearch = () => {
    const q = globalSearch.value.trim();

    if (! q) {
        return;
    }

    router.get(route('search'), { q }, {
        preserveState: false,
    });
};
</script>

<template>
    <div class="min-h-screen bg-[var(--mantto-canvas)]">
        <Head :title="title" />

        <Banner />

        <!-- Mobile overlay -->
        <div
            v-show="sidebarOpen"
            class="fixed inset-0 z-40 bg-steel-950/60 backdrop-blur-[2px] lg:hidden"
            aria-hidden="true"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            id="app-sidebar"
            class="fixed inset-y-0 start-0 z-50 flex w-[17.5rem] flex-col border-e border-steel-800 bg-steel-950 text-steel-100 transition-transform duration-200 ease-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            aria-label="Navegación principal"
        >
            <div class="mantto-hazard-rail h-1.5 w-full shrink-0" aria-hidden="true" />

            <div class="flex h-16 items-center gap-3 border-b border-steel-800 px-5">
                <Link :href="route('dashboard')" class="group flex min-w-0 items-center gap-3 outline-none focus-visible:ring-2 focus-visible:ring-hazard">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded border border-hazard/40 bg-steel-900 font-display text-lg font-semibold tracking-wide text-hazard">
                        M
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-xl font-semibold uppercase tracking-[0.12em] text-white group-hover:text-hazard-soft">
                            Mantto
                        </span>
                        <span class="block truncate font-mono text-[10px] uppercase tracking-[0.18em] text-steel-400">
                            Taller · flota
                        </span>
                    </span>
                </Link>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Módulos">
                <div v-for="section in navSections" :key="section.label">
                    <p class="mb-2 px-3 font-mono text-[10px] font-medium uppercase tracking-[0.2em] text-steel-500">
                        {{ section.label }}
                    </p>
                    <div class="space-y-0.5">
                        <SideNavLink
                            v-for="item in section.items"
                            :key="item.key"
                            :href="item.href"
                            :active="item.active"
                        >
                            <template #icon>
                                <!-- dashboard -->
                                <svg v-if="item.icon === 'dashboard'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 018.25 20.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                                <!-- orders -->
                                <svg v-else-if="item.icon === 'orders'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.59 2.69a1.5 1.5 0 01-2.12-1.74l1.35-5.9a1.5 1.5 0 01.4-.7l8.1-8.1a1.5 1.5 0 012.12 0l3.18 3.18a1.5 1.5 0 010 2.12l-8.1 8.1a1.5 1.5 0 01-.7.4l-1.14.25z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75l.75.75" /></svg>
                                <!-- quotations -->
                                <svg v-else-if="item.icon === 'quotations'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                <!-- billing -->
                                <svg v-else-if="item.icon === 'billing'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                                <!-- vehicles -->
                                <svg v-else-if="item.icon === 'vehicles'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.038v-.591a2.25 2.25 0 00-.659-1.591L11.75 3.25a2.25 2.25 0 00-1.591-.659H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v3.026" /></svg>
                                <!-- customers -->
                                <svg v-else-if="item.icon === 'customers'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                                <!-- categories -->
                                <svg v-else-if="item.icon === 'categories'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                                <!-- services -->
                                <svg v-else-if="item.icon === 'services'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.59 2.69a1.5 1.5 0 01-2.12-1.74l1.35-5.9a1.5 1.5 0 01.4-.7l8.1-8.1a1.5 1.5 0 012.12 0l3.18 3.18a1.5 1.5 0 010 2.12l-8.1 8.1a1.5 1.5 0 01-.7.4l-1.14.25z" /></svg>
                                <!-- parts -->
                                <svg v-else-if="item.icon === 'parts'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                                <!-- reports -->
                                <svg v-else-if="item.icon === 'reports'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                                <!-- users -->
                                <svg v-else-if="item.icon === 'users'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                <!-- audit -->
                                <svg v-else-if="item.icon === 'audit'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                                <!-- settings -->
                                <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.73-.52a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.52.73c-.25.35-.272.807-.107 1.204.166.397.506.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.52.73c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.73-.52c-.35-.25-.807-.272-1.204-.107-.397.165-.71.505-.78.929l-.15.894c-.09.542-.56.94-1.109.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.73.52a1.125 1.125 0 01-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.52-.73c.25-.35.273-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.52-.73a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.73.52c.35.25.806.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </template>
                            {{ item.label }}
                        </SideNavLink>
                    </div>
                </div>
            </nav>

            <div class="border-t border-steel-800 px-4 py-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-steel-500">
                    ManttoApp
                </p>
                <p class="mt-1 truncate text-xs text-steel-400">
                    {{ $page.props.auth.user.email }}
                </p>
            </div>
        </aside>

        <!-- Main column -->
        <div class="lg:ps-[17.5rem]">
            <header class="sticky top-0 z-30 border-b border-steel-200/80 bg-[var(--mantto-surface)]/95 backdrop-blur dark:border-steel-800">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-steel-200 bg-white text-steel-600 outline-none transition hover:border-hazard/40 hover:text-hazard-deep focus-visible:ring-2 focus-visible:ring-hazard dark:border-steel-700 dark:bg-steel-900 dark:text-steel-300 lg:hidden"
                        :aria-expanded="sidebarOpen"
                        aria-controls="app-sidebar"
                        aria-label="Abrir menú"
                        @click="sidebarOpen = ! sidebarOpen"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-display text-lg font-semibold uppercase tracking-[0.08em] text-steel-800 dark:text-steel-100 sm:text-xl">
                            {{ title || 'ManttoApp' }}
                        </p>
                    </div>

                    <form
                        v-if="$page.props.can?.viewReports"
                        class="relative hidden md:block"
                        @submit.prevent="submitGlobalSearch"
                    >
                        <label class="sr-only" for="global-search">Búsqueda global</label>
                        <input
                            id="global-search"
                            v-model="globalSearch"
                            type="search"
                            placeholder="Buscar folio, placa…"
                            class="w-52 rounded-md border-steel-300 bg-white text-sm text-steel-800 shadow-sm placeholder:text-steel-400 focus:border-hazard focus:ring-hazard dark:border-steel-700 dark:bg-steel-900 dark:text-steel-100 lg:w-64"
                        >
                    </form>

                    <ThemeToggle />

                    <div v-if="$page.props.jetstream?.hasTeamFeatures" class="hidden sm:block">
                        <Dropdown align="right" width="60" :content-classes="['py-1', 'bg-white', 'dark:bg-steel-900']">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-steel-200 bg-white px-3 py-2 text-sm font-medium text-steel-600 outline-none transition hover:text-steel-800 focus-visible:ring-2 focus-visible:ring-hazard dark:border-steel-700 dark:bg-steel-900 dark:text-steel-300"
                                >
                                    {{ $page.props.auth.user.current_team.name }}
                                    <svg class="ms-2 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="w-60">
                                    <div class="block px-4 py-2 text-xs text-steel-400">
                                        Manage Team
                                    </div>
                                    <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                        Team Settings
                                    </DropdownLink>
                                    <DropdownLink v-if="$page.props.jetstream?.canCreateTeams" :href="route('teams.create')">
                                        Create New Team
                                    </DropdownLink>
                                    <template v-if="$page.props.auth.user.all_teams.length > 1">
                                        <div class="border-t border-steel-200 dark:border-steel-700" />
                                        <div class="block px-4 py-2 text-xs text-steel-400">
                                            Switch Teams
                                        </div>
                                        <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                            <form @submit.prevent="switchToTeam(team)">
                                                <DropdownLink as="button">
                                                    <div class="flex items-center">
                                                        <svg
                                                            v-if="team.id == $page.props.auth.user.current_team_id"
                                                            class="me-2 size-5 text-emerald-400"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <div>{{ team.name }}</div>
                                                    </div>
                                                </DropdownLink>
                                            </form>
                                        </template>
                                    </template>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <Dropdown align="right" width="48" :content-classes="['py-1', 'bg-white', 'dark:bg-steel-900']">
                        <template #trigger>
                            <button
                                v-if="$page.props.jetstream?.managesProfilePhotos"
                                type="button"
                                class="flex rounded-full border-2 border-transparent outline-none focus-visible:ring-2 focus-visible:ring-hazard"
                            >
                                <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </button>
                            <button
                                v-else
                                type="button"
                                class="inline-flex items-center rounded-md border border-steel-200 bg-white px-3 py-2 text-sm font-medium text-steel-600 outline-none transition hover:text-steel-800 focus-visible:ring-2 focus-visible:ring-hazard dark:border-steel-700 dark:bg-steel-900 dark:text-steel-300"
                            >
                                <span class="max-w-[8rem] truncate">{{ $page.props.auth.user.name }}</span>
                                <svg class="ms-2 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="block px-4 py-2 text-xs text-steel-400">
                                Cuenta
                            </div>
                            <DropdownLink :href="route('profile.show')">
                                Perfil
                            </DropdownLink>
                            <DropdownLink v-if="$page.props.jetstream?.hasApiFeatures" :href="route('api-tokens.index')">
                                API Tokens
                            </DropdownLink>
                            <div class="border-t border-steel-200 dark:border-steel-700" />
                            <form @submit.prevent="logout">
                                <DropdownLink as="button">
                                    Cerrar sesión
                                </DropdownLink>
                            </form>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <div v-if="$slots.header" class="border-b border-steel-200/80 bg-[var(--mantto-surface)] dark:border-steel-800">
                <div class="px-4 py-5 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </div>

            <main class="mantto-page">
                <slot />
            </main>
        </div>
    </div>
</template>
