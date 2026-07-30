<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { orderStatusMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string;
    label: string;
}

interface CustomerOption {
    id: number;
    name: string;
}

interface OrderRow {
    id: number;
    folio: string;
    status: string;
    status_label: string;
    total?: number | string | null;
    services?: string[];
    customer?: { name?: string } | null;
    vehicle?: { license_plate?: string } | null;
}

const props = defineProps<{
    orders: LaravelPaginator;
    filters: {
        from?: string;
        to?: string;
        date_field?: string;
        status?: string;
        customer_id?: string | number;
    };
    statuses: SelectOption[];
    customers: CustomerOption[];
    summary: {
        count: number;
        total_amount?: number | string | null;
    };
    can: {
        export?: boolean;
        viewFull?: boolean;
    };
}>();

const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');
const dateField = ref(props.filters.date_field ?? 'received_at');
const status = ref(props.filters.status ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');

const headers = computed((): DataTableHeader[] => {
    const cols: DataTableHeader[] = [
        { title: 'Folio', key: 'folio' },
        { title: 'Cliente', key: 'customer' },
        { title: 'Unidad', key: 'vehicle' },
        { title: 'Estado', key: 'status', sortable: false },
        { title: 'Servicios', key: 'services', sortable: false },
    ];

    if (props.can?.viewFull) {
        cols.push({ title: 'Total', key: 'total', align: 'end', sortable: false });
    }

    return cols;
});

const statusItems = computed(() => [
    { value: '', title: 'Todos los estatus' },
    ...props.statuses.map((option) => ({ value: option.value, title: option.label })),
]);

const customerItems = computed(() => [
    { value: '', title: 'Todos los clientes' },
    ...props.customers.map((customer) => ({ value: String(customer.id), title: customer.name })),
]);

const filterParams = computed(() => ({
    from: from.value || undefined,
    to: to.value || undefined,
    date_field: dateField.value || undefined,
    status: status.value || undefined,
    customer_id: customerId.value || undefined,
}));

watch([from, to, dateField, status, customerId], () => {
    router.get(route('reports.orders'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function exportUrl(): string {
    return route('reports.orders', {
        ...filterParams.value,
        export: 1,
    });
}

function row(item: unknown): OrderRow {
    return item as OrderRow;
}
</script>

<template>
    <AppLayout title="Órdenes por periodo">
        <PageHeader
            title="Órdenes por periodo"
            :breadcrumbs="[
                { title: 'Reportes', href: route('reports.index') },
                { title: 'Órdenes', disabled: true },
            ]"
        >
            <template #actions>
                <v-btn
                    v-if="can?.export"
                    :href="exportUrl()"
                    variant="tonal"
                    prepend-icon="mdi-download"
                >
                    Exportar CSV
                </v-btn>
                <Link :href="route('reports.index')">
                    <v-btn variant="text">Volver</v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field v-model="from" type="date" label="Desde" hide-details style="max-width: 11rem" />
            <v-text-field v-model="to" type="date" label="Hasta" hide-details style="max-width: 11rem" />
            <v-select
                v-model="dateField"
                :items="[
                    { value: 'received_at', title: 'Recepción' },
                    { value: 'completed_at', title: 'Terminación' },
                    { value: 'delivered_at', title: 'Entrega' },
                ]"
                label="Campo fecha"
                hide-details
                style="max-width: 12rem"
            />
            <v-select
                v-model="status"
                :items="statusItems"
                label="Estatus"
                hide-details
                style="max-width: 12rem"
            />
            <v-select
                v-model="customerId"
                :items="customerItems"
                label="Cliente"
                hide-details
                style="max-width: 14rem"
            />
        </FilterBar>

        <v-alert type="info" variant="tonal" class="mb-4">
            {{ summary.count }} órdenes
            <template v-if="summary.total_amount != null">
                · Total <MoneyText :amount="summary.total_amount" />
            </template>
        </v-alert>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="orders"
                route-name="reports.orders"
                :filters="filterParams"
                empty-title="Sin órdenes"
                empty-message="No hay órdenes para el periodo seleccionado."
            >
                <template #item.folio="{ item }">
                    <Link
                        :href="route('maintenance-orders.show', row(item).id)"
                        class="text-primary text-decoration-none font-weight-medium"
                    >
                        {{ row(item).folio }}
                    </Link>
                </template>
                <template #item.customer="{ item }">
                    {{ row(item).customer?.name }}
                </template>
                <template #item.vehicle="{ item }">
                    {{ row(item).vehicle?.license_plate }}
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="row(item).status" :map="orderStatusMap" />
                </template>
                <template #item.services="{ item }">
                    {{ row(item).services?.join(', ') || '—' }}
                </template>
                <template v-if="can?.viewFull" #item.total="{ item }">
                    <MoneyText :amount="row(item).total" />
                </template>
            </ServerDataTable>
        </PanelCard>
    </AppLayout>
</template>
