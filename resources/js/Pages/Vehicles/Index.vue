<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { vehicleStatusMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string;
    label: string;
}

interface CustomerOption {
    id: number;
    name: string;
}

interface VehicleRow {
    id: number;
    customer_id: number;
    license_plate: string;
    customer_name: string;
    vehicle_type: string;
    brand: string;
    model: string;
    year: number;
    current_mileage?: number | null;
    status: string;
    status_label: string;
}

const props = defineProps<{
    vehicles: LaravelPaginator;
    filters: {
        search?: string;
        status?: string;
        brand?: string;
        customer_id?: string | number;
    };
    statuses: SelectOption[];
    customers: CustomerOption[];
    can: {
        create?: boolean;
    };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const brand = ref(props.filters.brand ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');
const plateSearch = ref('');

const headers: DataTableHeader[] = [
    { title: 'Placas', key: 'license_plate' },
    { title: 'Cliente', key: 'customer_name' },
    { title: 'Tipo', key: 'vehicle_type' },
    { title: 'Marca / Modelo', key: 'brand' },
    { title: 'Año', key: 'year' },
    { title: 'Km', key: 'current_mileage' },
    { title: 'Estatus', key: 'status', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const statusItems = computed(() => [
    { value: '', title: 'Todos los estatus' },
    ...props.statuses.map((option) => ({ value: option.value, title: option.label })),
]);

const customerItems = computed(() => [
    { value: '', title: 'Todos los clientes' },
    ...props.customers.map((customer) => ({ value: String(customer.id), title: customer.name })),
]);

const filterParams = computed(() => ({
    search: search.value || undefined,
    status: status.value || undefined,
    brand: brand.value || undefined,
    customer_id: customerId.value || undefined,
}));

watch([search, status, brand, customerId], () => {
    router.get(route('vehicles.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function quickPlateSearch(): void {
    if (!plateSearch.value.trim()) {
        return;
    }

    router.get(route('vehicles.search'), {
        plate: plateSearch.value,
    });
}

function row(item: unknown): VehicleRow {
    return item as VehicleRow;
}
</script>

<template>
    <AppLayout title="Unidades">
        <PageHeader
            title="Unidades"
            subtitle="Flota asociada a clientes activos."
        >
            <template #actions>
                <Link v-if="can?.create" :href="route('vehicles.create')">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nueva unidad
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="plateSearch"
                type="search"
                label="Búsqueda rápida"
                placeholder="Placas"
                prepend-inner-icon="mdi-card-text-outline"
                hide-details
                style="max-width: 16rem"
                @keyup.enter="quickPlateSearch"
            />
            <v-btn variant="tonal" @click="quickPlateSearch">
                Buscar placas
            </v-btn>
            <v-text-field
                v-model="search"
                type="search"
                label="Buscar"
                placeholder="Placas, VIN, marca…"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 16rem"
            />
            <v-select
                v-model="customerId"
                :items="customerItems"
                label="Cliente"
                hide-details
                style="max-width: 14rem"
            />
            <v-select
                v-model="status"
                :items="statusItems"
                label="Estatus"
                hide-details
                style="max-width: 12rem"
            />
            <v-text-field
                v-model="brand"
                type="search"
                label="Marca"
                clearable
                hide-details
                style="max-width: 10rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="vehicles"
                route-name="vehicles.index"
                :filters="filterParams"
                empty-title="Sin unidades"
                empty-message="No se encontraron unidades."
            >
                <template #item.brand="{ item }">
                    {{ row(item).brand }} {{ row(item).model }}
                </template>
                <template #item.current_mileage="{ item }">
                    {{ row(item).current_mileage?.toLocaleString() ?? '—' }}
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="row(item).status" :map="vehicleStatusMap" />
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex justify-end">
                        <Link
                            :href="route('customers.vehicles.show', [row(item).customer_id, row(item).id])"
                        >
                            <v-btn variant="text" size="small" color="primary">
                                Ver
                            </v-btn>
                        </Link>
                    </div>
                </template>
                <template v-if="can?.create" #empty-action>
                    <Link :href="route('vehicles.create')">
                        <v-btn color="primary" variant="flat">Nueva unidad</v-btn>
                    </Link>
                </template>
            </ServerDataTable>
        </PanelCard>
    </AppLayout>
</template>
