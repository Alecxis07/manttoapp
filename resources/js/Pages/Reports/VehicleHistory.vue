<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { orderStatusMap } from '@/Components/Ui/statusMaps';

interface VehicleOption {
    id: number;
    label: string;
}

interface OrderRow {
    id: number;
    folio: string;
    status: string;
    status_label: string;
    services?: string[];
    parts?: Array<{ description?: string }>;
    total?: number | string | null;
}

const props = defineProps<{
    filters: {
        vehicle_id?: string | number;
        from?: string;
        to?: string;
    };
    vehicle?: {
        license_plate: string;
        brand?: string;
        model?: string;
        customer?: { name?: string } | null;
    } | null;
    orders?: LaravelPaginator | null;
    vehicles: VehicleOption[];
    can: {
        exportPdf?: boolean;
    };
}>();

const vehicleId = ref(props.filters.vehicle_id ? String(props.filters.vehicle_id) : '');
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');

const headers: DataTableHeader[] = [
    { title: 'Folio', key: 'folio' },
    { title: 'Estado', key: 'status', sortable: false },
    { title: 'Servicios', key: 'services', sortable: false },
    { title: 'Refacciones', key: 'parts', sortable: false },
    { title: 'Total', key: 'total', align: 'end', sortable: false },
];

const vehicleItems = computed(() => [
    { value: '', title: 'Selecciona unidad' },
    ...props.vehicles.map((option) => ({ value: String(option.id), title: option.label })),
]);

const filterParams = computed(() => ({
    vehicle_id: vehicleId.value || undefined,
    from: from.value || undefined,
    to: to.value || undefined,
}));

watch([vehicleId, from, to], () => {
    router.get(route('reports.vehicle-history'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function pdfUrl(): string {
    return route('reports.vehicle-history.pdf', {
        vehicle_id: vehicleId.value,
        from: from.value || undefined,
        to: to.value || undefined,
    });
}

function row(item: unknown): OrderRow {
    return item as OrderRow;
}
</script>

<template>
    <AppLayout title="Historial por unidad">
        <PageHeader
            title="Historial por unidad"
            :breadcrumbs="[
                { title: 'Reportes', href: route('reports.index') },
                { title: 'Historial', disabled: true },
            ]"
        >
            <template #actions>
                <v-btn
                    v-if="can?.exportPdf && vehicleId"
                    :href="pdfUrl()"
                    variant="tonal"
                    prepend-icon="mdi-file-pdf-box"
                >
                    Exportar PDF
                </v-btn>
                <Link :href="route('reports.index')">
                    <v-btn variant="text">Volver</v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-select
                v-model="vehicleId"
                :items="vehicleItems"
                label="Unidad"
                hide-details
                style="max-width: 24rem"
            />
            <v-text-field v-model="from" type="date" label="Desde" hide-details style="max-width: 11rem" />
            <v-text-field v-model="to" type="date" label="Hasta" hide-details style="max-width: 11rem" />
        </FilterBar>

        <PanelCard
            v-if="vehicle"
            :title="`${vehicle.license_plate} — ${vehicle.customer?.name ?? ''}`"
            :subtitle="`${vehicle.brand ?? ''} ${vehicle.model ?? ''}`.trim()"
            class="mb-4"
        />

        <PanelCard v-if="orders">
            <ServerDataTable
                :headers="headers"
                :items="orders"
                route-name="reports.vehicle-history"
                :filters="filterParams"
                empty-title="Sin historial"
                empty-message="No hay órdenes para esta unidad en el periodo."
            >
                <template #item.folio="{ item }">
                    <Link
                        :href="route('maintenance-orders.show', row(item).id)"
                        class="text-primary text-decoration-none font-weight-medium"
                    >
                        {{ row(item).folio }}
                    </Link>
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="row(item).status" :map="orderStatusMap" />
                </template>
                <template #item.services="{ item }">
                    {{ row(item).services?.join(', ') || '—' }}
                </template>
                <template #item.parts="{ item }">
                    {{ row(item).parts?.map((part) => part.description).join(', ') || '—' }}
                </template>
                <template #item.total="{ item }">
                    <MoneyText
                        v-if="row(item).total != null"
                        :amount="row(item).total"
                    />
                    <span v-else>—</span>
                </template>
            </ServerDataTable>
        </PanelCard>

        <PanelCard v-else>
            <EmptyState
                title="Selecciona una unidad"
                message="Elige una unidad para ver su historial de órdenes."
                icon="mdi-truck-outline"
            />
        </PanelCard>
    </AppLayout>
</template>
