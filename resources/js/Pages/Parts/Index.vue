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
    value: string;
    label: string;
}

interface PartRow {
    id: number;
    code: string;
    description: string;
    type_label: string;
    base_price: number | string;
    is_active: boolean;
}

const props = defineProps<{
    parts: LaravelPaginator;
    filters: {
        search?: string;
        active?: string;
        type?: string;
    };
    types: SelectOption[];
}>();

const search = ref(props.filters.search ?? '');
const active = ref(props.filters.active ?? '');
const type = ref(props.filters.type ?? '');

const confirmOpen = ref(false);
const processing = ref(false);
const pending = ref<PartRow | null>(null);

const headers: DataTableHeader[] = [
    { title: 'Código', key: 'code' },
    { title: 'Descripción', key: 'description' },
    { title: 'Tipo', key: 'type_label' },
    { title: 'Precio base', key: 'base_price', align: 'end' },
    { title: 'Estatus', key: 'is_active', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const typeItems = computed(() => [
    { value: '', title: 'Todos los tipos' },
    ...props.types.map((option) => ({ value: option.value, title: option.label })),
]);

const filterParams = computed(() => ({
    search: search.value || undefined,
    active: active.value || undefined,
    type: type.value || undefined,
}));

watch([search, active, type], () => {
    router.get(route('part-catalog.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function askDeactivate(part: PartRow): void {
    pending.value = part;
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    if (!pending.value) {
        return;
    }

    processing.value = true;
    router.post(route('part-catalog.deactivate', pending.value.id), {}, {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
            pending.value = null;
        },
    });
}

function row(item: unknown): PartRow {
    return item as PartRow;
}
</script>

<template>
    <AppLayout title="Catálogo de refacciones">
        <PageHeader
            title="Catálogo de refacciones y conceptos"
            subtitle="Refacciones y conceptos referenciales."
        >
            <template #actions>
                <Link :href="route('part-catalog.create')">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nuevo concepto
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
                v-model="type"
                :items="typeItems"
                label="Tipo"
                hide-details
                style="max-width: 12rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="parts"
                route-name="part-catalog.index"
                :filters="filterParams"
                empty-title="Sin conceptos"
                empty-message="No hay conceptos para mostrar."
            >
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
                        <Link :href="route('part-catalog.show', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">Ver</v-btn>
                        </Link>
                        <Link :href="route('part-catalog.edit', row(item).id)">
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
            title="Desactivar concepto"
            :message="pending ? `¿Desactivar el concepto ${pending.code}?` : undefined"
            confirm-text="Desactivar"
            confirm-color="warning"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
