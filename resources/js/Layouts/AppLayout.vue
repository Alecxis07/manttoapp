<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '@/Components/Ui/AppShell.vue';
import type { NavSectionData } from '@/Components/Ui/types';
import { useAppPage } from '@/Composables/useAppPage';
import { isCurrentRoute } from '@/utils/isCurrentRoute';

withDefaults(
    defineProps<{
        title?: string;
    }>(),
    {
        title: undefined,
    },
);

const page = useAppPage();

const can = computed(() => (page.props.can ?? {}) as Record<string, boolean>);

const navSections = computed((): NavSectionData[] => {
    // Track Inertia URL so active states update on navigation.
    void page.url;

    const sections: Array<{
        label: string;
        items: Array<{
            key: string;
            label: string;
            href: string;
            active: boolean;
            show: boolean;
            icon: string;
        }>;
    }> = [
        {
            label: 'Operación',
            items: [
                {
                    key: 'dashboard',
                    label: 'Dashboard',
                    href: route('dashboard'),
                    active: isCurrentRoute('dashboard'),
                    show: true,
                    icon: 'mdi-view-dashboard-outline',
                },
                {
                    key: 'orders',
                    label: 'Órdenes',
                    href: route('maintenance-orders.index'),
                    active: isCurrentRoute('maintenance-orders.*'),
                    show: !!can.value.viewMaintenanceOrders,
                    icon: 'mdi-wrench-outline',
                },
                {
                    key: 'quotations',
                    label: 'Cotizaciones',
                    href: route('quotations.index'),
                    active: isCurrentRoute('quotations.*'),
                    show: !!can.value.viewQuotations,
                    icon: 'mdi-file-document-outline',
                },
                {
                    key: 'billing',
                    label: 'Facturación',
                    href: route('billing-requests.index'),
                    active: isCurrentRoute('billing-requests.*'),
                    show: !!can.value.viewBillingRequests,
                    icon: 'mdi-receipt-text-outline',
                },
                {
                    key: 'vehicles',
                    label: 'Unidades',
                    href: route('vehicles.index'),
                    active:
                        isCurrentRoute('vehicles.*') ||
                        isCurrentRoute('customers.vehicles.*'),
                    show: !!can.value.viewVehicles,
                    icon: 'mdi-truck-outline',
                },
                {
                    key: 'customers',
                    label: 'Clientes',
                    href: route('customers.index'),
                    active:
                        isCurrentRoute('customers.*') &&
                        !isCurrentRoute('customers.vehicles.*'),
                    show: !!can.value.viewCustomers,
                    icon: 'mdi-account-group-outline',
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
                    active: isCurrentRoute('service-categories.*'),
                    show: !!can.value.manageServiceCategories,
                    icon: 'mdi-tag-outline',
                },
                {
                    key: 'services',
                    label: 'Servicios',
                    href: route('service-catalog.index'),
                    active: isCurrentRoute('service-catalog.*'),
                    show: !!can.value.manageServices,
                    icon: 'mdi-toolbox-outline',
                },
                {
                    key: 'parts',
                    label: 'Refacciones',
                    href: route('part-catalog.index'),
                    active: isCurrentRoute('part-catalog.*'),
                    show: !!can.value.manageParts,
                    icon: 'mdi-package-variant-closed',
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
                    active:
                        isCurrentRoute('reports.*') || isCurrentRoute('search'),
                    show: !!can.value.viewReports,
                    icon: 'mdi-chart-bar',
                },
                {
                    key: 'users',
                    label: 'Usuarios',
                    href: route('users.index'),
                    active: isCurrentRoute('users.*'),
                    show: !!can.value.manageUsers,
                    icon: 'mdi-account-cog-outline',
                },
                {
                    key: 'audit',
                    label: 'Auditoría',
                    href: route('audit.index'),
                    active: isCurrentRoute('audit.*'),
                    show: !!can.value.viewAudit,
                    icon: 'mdi-shield-check-outline',
                },
                {
                    key: 'settings',
                    label: 'Configuración',
                    href: route('settings.edit'),
                    active: isCurrentRoute('settings.*'),
                    show: !!can.value.manageSettings,
                    icon: 'mdi-cog-outline',
                },
            ],
        },
    ];

    return sections
        .map((section) => ({
            label: section.label,
            items: section.items
                .filter((item) => item.show)
                .map(({ show: _show, ...item }) => item),
        }))
        .filter((section) => section.items.length > 0);
});
</script>

<template>
    <div>
        <Head :title="title" />
        <AppShell :title="title" :nav-sections="navSections">
            <template v-if="$slots.header" #header>
                <slot name="header" />
            </template>
            <slot />
        </AppShell>
    </div>
</template>
