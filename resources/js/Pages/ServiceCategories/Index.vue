<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { activeFlagFeminineMap } from '@/Components/Ui/statusMaps';

interface CategoryRow {
    id: number;
    code: string;
    name: string;
    services_count: number;
    is_active: boolean;
    in_use?: boolean;
}

const props = defineProps<{
    categories: LaravelPaginator;
    filters: {
        search?: string;
        active?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const active = ref(props.filters.active ?? '');

const confirmOpen = ref(false);
const processing = ref(false);
const pending = ref<CategoryRow | null>(null);
const confirmMode = ref<'deactivate' | 'delete'>('deactivate');

const headers: DataTableHeader[] = [
    { title: 'Código', key: 'code' },
    { title: 'Nombre', key: 'name' },
    { title: 'Servicios', key: 'services_count' },
    { title: 'Estatus', key: 'is_active', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const filterParams = computed(() => ({
    search: search.value || undefined,
    active: active.value || undefined,
}));

watch([search, active], () => {
    router.get(route('service-categories.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function askDeactivate(category: CategoryRow): void {
    pending.value = category;
    confirmMode.value = 'deactivate';
    confirmOpen.value = true;
}

function askDelete(category: CategoryRow): void {
    if (category.in_use) {
        return;
    }

    pending.value = category;
    confirmMode.value = 'delete';
    confirmOpen.value = true;
}

function confirmAction(): void {
    if (!pending.value) {
        return;
    }

    processing.value = true;
    const finish = () => {
        processing.value = false;
        confirmOpen.value = false;
        pending.value = null;
    };

    if (confirmMode.value === 'deactivate') {
        router.post(route('service-categories.deactivate', pending.value.id), {}, { onFinish: finish });
        return;
    }

    router.delete(route('service-categories.destroy', pending.value.id), { onFinish: finish });
}

function row(item: unknown): CategoryRow {
    return item as CategoryRow;
}
</script>

<template>
    <AppLayout title="Categorías de servicio">
        <PageHeader title="Categorías de servicio" subtitle="Agrupación del catálogo de servicios.">
            <template #actions>
                <Link :href="route('service-categories.create')">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nueva categoría
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="search"
                type="search"
                label="Buscar"
                placeholder="Código o nombre"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 18rem"
            />
            <v-select
                v-model="active"
                :items="[
                    { value: '', title: 'Todos' },
                    { value: '1', title: 'Activas' },
                    { value: '0', title: 'Inactivas' },
                ]"
                label="Estatus"
                hide-details
                style="max-width: 12rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="categories"
                route-name="service-categories.index"
                :filters="filterParams"
                empty-title="Sin categorías"
                empty-message="No hay categorías para mostrar."
            >
                <template #item.is_active="{ item }">
                    <StatusChip
                        :status="row(item).is_active ? 'active' : 'inactive'"
                        :map="activeFlagFeminineMap"
                    />
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <Link :href="route('service-categories.show', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">Ver</v-btn>
                        </Link>
                        <Link :href="route('service-categories.edit', row(item).id)">
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
                        <v-btn
                            v-if="!row(item).in_use"
                            variant="text"
                            size="small"
                            color="error"
                            @click="askDelete(row(item))"
                        >
                            Eliminar
                        </v-btn>
                    </div>
                </template>
            </ServerDataTable>
        </PanelCard>

        <ConfirmDialog
            v-model="confirmOpen"
            :title="confirmMode === 'deactivate' ? 'Desactivar categoría' : 'Eliminar categoría'"
            :message="pending
                ? (confirmMode === 'deactivate'
                    ? `¿Desactivar la categoría ${pending.name}?`
                    : `¿Eliminar la categoría ${pending.name}?`)
                : undefined"
            :confirm-text="confirmMode === 'deactivate' ? 'Desactivar' : 'Eliminar'"
            :loading="processing"
            @confirm="confirmAction"
        />
    </AppLayout>
</template>
