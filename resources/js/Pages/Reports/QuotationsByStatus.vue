<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DateText from '@/Components/Ui/DateText.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { quotationStatusMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string;
    label: string;
}

interface QuotationRow {
    id: number;
    folio: string;
    version: number | string;
    status: string;
    status_label: string;
    created_at?: string | null;
    total?: number | string | null;
    customer?: { name?: string } | null;
    vehicle?: { license_plate?: string } | null;
}

const props = defineProps<{
    quotations: LaravelPaginator;
    filters: {
        from?: string;
        to?: string;
        status?: string;
    };
    statuses: SelectOption[];
    status_counts?: Record<string, number>;
    conversion: {
        total_issued: number;
        accepted: number;
        converted_to_order: number;
        rate: number | string;
    };
    can: {
        export?: boolean;
        viewFull?: boolean;
    };
}>();

const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');
const status = ref(props.filters.status ?? '');

const headers = computed((): DataTableHeader[] => {
    const cols: DataTableHeader[] = [
        { title: 'Folio', key: 'folio' },
        { title: 'Cliente', key: 'customer' },
        { title: 'Unidad', key: 'vehicle' },
        { title: 'Estado', key: 'status', sortable: false },
        { title: 'Fecha', key: 'created_at' },
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

const filterParams = computed(() => ({
    from: from.value || undefined,
    to: to.value || undefined,
    status: status.value || undefined,
}));

watch([from, to, status], () => {
    router.get(route('reports.quotations'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function exportUrl(): string {
    return route('reports.quotations', {
        ...filterParams.value,
        export: 1,
    });
}

function row(item: unknown): QuotationRow {
    return item as QuotationRow;
}
</script>

<template>
    <AppLayout title="Cotizaciones por estado">
        <PageHeader
            title="Cotizaciones por estado"
            :breadcrumbs="[
                { title: 'Reportes', href: route('reports.index') },
                { title: 'Cotizaciones', disabled: true },
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
                v-model="status"
                :items="statusItems"
                label="Estatus"
                hide-details
                style="max-width: 12rem"
            />
        </FilterBar>

        <v-alert type="info" variant="tonal" class="mb-4">
            Emitidas: {{ conversion.total_issued }}
            · Aceptadas: {{ conversion.accepted }}
            · Convertidas a orden: {{ conversion.converted_to_order }}
            · Tasa conversión: {{ conversion.rate }}%
        </v-alert>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="quotations"
                route-name="reports.quotations"
                :filters="filterParams"
                empty-title="Sin cotizaciones"
                empty-message="No hay cotizaciones para los filtros seleccionados."
            >
                <template #item.folio="{ item }">
                    <Link
                        :href="route('quotations.show', row(item).id)"
                        class="text-primary text-decoration-none font-weight-medium"
                    >
                        {{ row(item).folio }}
                        <span class="text-medium-emphasis">v{{ row(item).version }}</span>
                    </Link>
                </template>
                <template #item.customer="{ item }">
                    {{ row(item).customer?.name }}
                </template>
                <template #item.vehicle="{ item }">
                    {{ row(item).vehicle?.license_plate }}
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="row(item).status" :map="quotationStatusMap" />
                </template>
                <template #item.created_at="{ item }">
                    <DateText :value="row(item).created_at" />
                </template>
                <template v-if="can?.viewFull" #item.total="{ item }">
                    <MoneyText :amount="row(item).total" />
                </template>
            </ServerDataTable>
        </PanelCard>
    </AppLayout>
</template>
