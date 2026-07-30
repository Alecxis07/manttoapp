<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { orderStatusMap } from '@/Components/Ui/statusMaps';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';

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
    customer_name?: string;
    vehicle_plate?: string;
    type_label?: string;
    status: string;
    status_label?: string;
    total: number | string;
}

const props = defineProps<{
    orders: LaravelPaginator;
    filters: {
        search?: string;
        status?: string;
        type?: string;
        customer_id?: string | number;
    };
    statuses: SelectOption[];
    types: SelectOption[];
    customers: CustomerOption[];
    can?: { create?: boolean };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const type = ref(props.filters.type ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');

const activeFilters = computed(() => ({
    search: search.value || undefined,
    status: status.value || undefined,
    type: type.value || undefined,
    customer_id: customerId.value || undefined,
}));

watch([search, status, type, customerId], () => {
    router.get(route('maintenance-orders.index'), activeFilters.value, {
        preserveState: true,
        replace: true,
    });
});

const headers: DataTableHeader[] = [
    { title: 'Folio', key: 'folio', sortable: false },
    { title: 'Cliente', key: 'customer_name', sortable: false },
    { title: 'Unidad', key: 'vehicle_plate', sortable: false },
    { title: 'Tipo', key: 'type_label', sortable: false },
    { title: 'Estado', key: 'status', sortable: false },
    { title: 'Total', key: 'total', align: 'end', sortable: false },
];

const statusItems = computed(() => [
    { title: 'Todos los estados', value: '' },
    ...props.statuses.map((option) => ({ title: option.label, value: option.value })),
]);

const typeItems = computed(() => [
    { title: 'Todos los tipos', value: '' },
    ...props.types.map((option) => ({ title: option.label, value: option.value })),
]);

const customerItems = computed(() => [
    { title: 'Todos los clientes', value: '' },
    ...props.customers.map((customer) => ({
        title: customer.name,
        value: String(customer.id),
    })),
]);
</script>

<template>
    <AppLayout title="Órdenes">
        <PageHeader title="Órdenes de mantenimiento">
            <template v-if="can?.create" #actions>
                <Link :href="route('maintenance-orders.create')" class="text-decoration-none">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nueva orden
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="search"
                label="Buscar"
                placeholder="Folio, cliente, placas…"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 18rem"
            />
            <v-select
                v-model="status"
                :items="statusItems"
                label="Estado"
                hide-details
                style="max-width: 14rem"
            />
            <v-select
                v-model="type"
                :items="typeItems"
                label="Tipo"
                hide-details
                style="max-width: 12rem"
            />
            <v-select
                v-model="customerId"
                :items="customerItems"
                label="Cliente"
                hide-details
                style="max-width: 16rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="orders"
                route-name="maintenance-orders.index"
                :filters="activeFilters"
                empty-title="Sin órdenes"
                empty-message="No hay órdenes registradas."
            >
                <template #item.folio="{ item }">
                    <Link
                        :href="route('maintenance-orders.show', (item as OrderRow).id)"
                        class="text-primary text-decoration-none font-weight-medium"
                    >
                        {{ (item as OrderRow).folio }}
                    </Link>
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="(item as OrderRow).status" :map="orderStatusMap" />
                </template>
                <template #item.total="{ item }">
                    <MoneyText :amount="(item as OrderRow).total" />
                </template>
                <template v-if="can?.create" #empty-action>
                    <Link :href="route('maintenance-orders.create')" class="text-decoration-none">
                        <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                            Nueva orden
                        </v-btn>
                    </Link>
                </template>
            </ServerDataTable>
        </PanelCard>
    </AppLayout>
</template>
