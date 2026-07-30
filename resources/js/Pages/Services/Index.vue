<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { activeFlagMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string | number;
    label: string;
}

interface ServiceRow {
    id: number;
    code: string;
    description: string;
    category?: { name?: string } | null;
    base_price: number | string;
    is_active: boolean;
}

const props = defineProps<{
    services: LaravelPaginator;
    filters: {
        search?: string;
        active?: string;
        service_category_id?: string | number;
    };
    categories: SelectOption[];
}>();

const search = ref(props.filters.search ?? '');
const active = ref(props.filters.active ?? '');
const serviceCategoryId = ref(
    props.filters.service_category_id != null ? String(props.filters.service_category_id) : '',
);

const confirmOpen = ref(false);
const processing = ref(false);
const pending = ref<ServiceRow | null>(null);

const headers: DataTableHeader[] = [
    { title: 'Código', key: 'code' },
    { title: 'Descripción', key: 'description' },
    { title: 'Categoría', key: 'category' },
    { title: 'Precio base', key: 'base_price', align: 'end' },
    { title: 'Estatus', key: 'is_active', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const categoryItems = computed(() => [
    { value: '', title: 'Todas las categorías' },
    ...props.categories.map((category) => ({
        value: String(category.value),
        title: category.label,
    })),
]);

const filterParams = computed(() => ({
    search: search.value || undefined,
    active: active.value || undefined,
    service_category_id: serviceCategoryId.value || undefined,
}));

watch([search, active, serviceCategoryId], () => {
    router.get(route('service-catalog.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function askDeactivate(service: ServiceRow): void {
    pending.value = service;
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    if (!pending.value) {
        return;
    }

    processing.value = true;
    router.post(route('service-catalog.deactivate', pending.value.id), {}, {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
            pending.value = null;
        },
    });
}

function row(item: unknown): ServiceRow {
    return item as ServiceRow;
}
</script>

<template>
    <AppLayout title="Catálogo de servicios">
        <PageHeader title="Catálogo de servicios" subtitle="Servicios referenciales del taller.">
            <template #actions>
                <Link :href="route('service-catalog.create')">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nuevo servicio
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="search"
                type="search"
                label="Buscar"
                placeholder="Código o descripción"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 18rem"
            />
            <v-select
                v-model="active"
                :items="[
                    { value: '', title: 'Todos' },
                    { value: '1', title: 'Activos' },
                    { value: '0', title: 'Inactivos' },
                ]"
                label="Estatus"
                hide-details
                style="max-width: 12rem"
            />
            <v-select
                v-model="serviceCategoryId"
                :items="categoryItems"
                label="Categoría"
                hide-details
                style="max-width: 14rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="services"
                route-name="service-catalog.index"
                :filters="filterParams"
                empty-title="Sin servicios"
                empty-message="No hay servicios para mostrar."
            >
                <template #item.category="{ item }">
                    {{ row(item).category?.name ?? '—' }}
                </template>
                <template #item.base_price="{ item }">
                    <MoneyText :amount="row(item).base_price" />
                </template>
                <template #item.is_active="{ item }">
                    <StatusChip
                        :status="row(item).is_active ? 'active' : 'inactive'"
                        :map="activeFlagMap"
                    />
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <Link :href="route('service-catalog.show', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">Ver</v-btn>
                        </Link>
                        <Link :href="route('service-catalog.edit', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">Editar</v-btn>
                        </Link>
                        <v-btn
                            v-if="row(item).is_active"
                            variant="text"
                            size="small"
                            color="warning"
                            @click="askDeactivate(row(item))"
                        >
                            Desactivar
                        </v-btn>
                    </div>
                </template>
            </ServerDataTable>
        </PanelCard>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Desactivar servicio"
            :message="pending ? `¿Desactivar el servicio ${pending.code}?` : undefined"
            confirm-text="Desactivar"
            confirm-color="warning"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
