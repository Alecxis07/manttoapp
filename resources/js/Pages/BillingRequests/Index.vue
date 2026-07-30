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
import { billingStatusMap } from '@/Components/Ui/statusMaps';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface BillingRow {
    id: number;
    folio: string;
    status: string;
    status_label?: string;
    total: number | string;
    invoice_reference?: string | null;
    customer?: { name?: string } | null;
}

const props = defineProps<{
    billingRequests: LaravelPaginator;
    filters: { search?: string; status?: string };
    statuses: SelectOption[];
    can?: { create?: boolean };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const activeFilters = computed(() => ({
    search: search.value || undefined,
    status: status.value || undefined,
}));

watch([search, status], () => {
    router.get(route('billing-requests.index'), activeFilters.value, {
        preserveState: true,
        replace: true,
    });
});

const headers: DataTableHeader[] = [
    { title: 'Folio', key: 'folio', sortable: false },
    { title: 'Cliente', key: 'customer', sortable: false },
    { title: 'Estado', key: 'status', sortable: false },
    { title: 'Total', key: 'total', align: 'end', sortable: false },
    { title: 'Factura', key: 'invoice_reference', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const statusItems = computed(() => [
    { title: 'Todos los estatus', value: '' },
    ...props.statuses.map((option) => ({ title: option.label, value: option.value })),
]);
</script>

<template>
    <AppLayout title="Facturación">
        <PageHeader title="Solicitudes de facturación">
            <template v-if="can?.create" #actions>
                <Link :href="route('billing-requests.create')" class="text-decoration-none">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nueva solicitud
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="search"
                label="Buscar"
                placeholder="Folio, cliente o referencia"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 18rem"
            />
            <v-select
                v-model="status"
                :items="statusItems"
                label="Estatus"
                hide-details
                style="max-width: 14rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="billingRequests"
                route-name="billing-requests.index"
                :filters="activeFilters"
                empty-title="Sin solicitudes"
                empty-message="No hay solicitudes de facturación."
            >
                <template #item.folio="{ item }">
                    <span class="font-weight-medium">{{ (item as BillingRow).folio }}</span>
                </template>
                <template #item.customer="{ item }">
                    {{ (item as BillingRow).customer?.name }}
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="(item as BillingRow).status" :map="billingStatusMap" />
                </template>
                <template #item.total="{ item }">
                    <MoneyText :amount="(item as BillingRow).total" />
                </template>
                <template #item.invoice_reference="{ item }">
                    {{ (item as BillingRow).invoice_reference || '—' }}
                </template>
                <template #item.actions="{ item }">
                    <Link
                        :href="route('billing-requests.show', (item as BillingRow).id)"
                        class="text-decoration-none"
                    >
                        <v-btn size="small" variant="text" color="primary">
                            Ver
                        </v-btn>
                    </Link>
                </template>
                <template v-if="can?.create" #empty-action>
                    <Link :href="route('billing-requests.create')" class="text-decoration-none">
                        <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                            Nueva solicitud
                        </v-btn>
                    </Link>
                </template>
            </ServerDataTable>
        </PanelCard>
    </AppLayout>
</template>
