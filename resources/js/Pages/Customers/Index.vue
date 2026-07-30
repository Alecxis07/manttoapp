<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { customerStatusMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string;
    label: string;
}

interface CustomerRow {
    id: number;
    name: string;
    trade_name?: string | null;
    type_label: string;
    email?: string | null;
    rfc?: string | null;
    status: string;
}

const props = defineProps<{
    customers: LaravelPaginator;
    filters: {
        search?: string;
        status?: string;
        type?: string;
    };
    statuses: SelectOption[];
    types: SelectOption[];
    can: {
        create?: boolean;
    };
}>();

const page = usePage();
const permissions = computed(
    () => (page.props.permissions as string[] | undefined) ?? [],
);
const canUpdate = computed(() => permissions.value.includes('customers.update'));
const canCreateCustomers = computed(
    () =>
        !!(page.props.can as Record<string, boolean> | undefined)?.createCustomers ||
        canUpdate.value,
);

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const type = ref(props.filters.type ?? '');

const confirmOpen = ref(false);
const processing = ref(false);
const pendingCustomer = ref<CustomerRow | null>(null);

const headers: DataTableHeader[] = [
    { title: 'Nombre', key: 'name' },
    { title: 'Tipo', key: 'type_label' },
    { title: 'Email', key: 'email' },
    { title: 'RFC', key: 'rfc' },
    { title: 'Estatus', key: 'status', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const statusItems = computed(() => [
    { value: '', title: 'Todos los estatus' },
    ...props.statuses.map((option) => ({ value: option.value, title: option.label })),
]);

const typeItems = computed(() => [
    { value: '', title: 'Todos los tipos' },
    ...props.types.map((option) => ({ value: option.value, title: option.label })),
]);

const filterParams = computed(() => ({
    search: search.value || undefined,
    status: status.value || undefined,
    type: type.value || undefined,
}));

watch([search, status, type], () => {
    router.get(route('customers.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function askDeactivate(customer: CustomerRow): void {
    pendingCustomer.value = customer;
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    if (!pendingCustomer.value) {
        return;
    }

    processing.value = true;
    router.delete(route('customers.destroy', pendingCustomer.value.id), {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
            pendingCustomer.value = null;
        },
    });
}

function row(item: unknown): CustomerRow {
    return item as CustomerRow;
}
</script>

<template>
    <AppLayout title="Clientes">
        <PageHeader
            title="Clientes"
            subtitle="Consulta y administra el padrón de clientes."
        >
            <template #actions>
                <Link v-if="can?.create" :href="route('customers.create')">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nuevo cliente
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="search"
                type="search"
                label="Buscar"
                placeholder="Nombre, email o teléfono"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 20rem"
            />
            <v-select
                v-model="status"
                :items="statusItems"
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
                :items="customers"
                route-name="customers.index"
                :filters="filterParams"
                empty-title="Sin clientes"
                empty-message="No hay clientes para mostrar."
            >
                <template #item.name="{ item }">
                    <div>
                        <div class="font-weight-medium">{{ row(item).name }}</div>
                        <div
                            v-if="row(item).trade_name"
                            class="text-caption text-medium-emphasis"
                        >
                            {{ row(item).trade_name }}
                        </div>
                    </div>
                </template>
                <template #item.email="{ item }">
                    {{ row(item).email || '—' }}
                </template>
                <template #item.rfc="{ item }">
                    {{ row(item).rfc || '—' }}
                </template>
                <template #item.status="{ item }">
                    <StatusChip :status="row(item).status" :map="customerStatusMap" />
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <Link :href="route('customers.show', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">
                                Ver
                            </v-btn>
                        </Link>
                        <Link
                            v-if="canCreateCustomers"
                            :href="route('customers.edit', row(item).id)"
                        >
                            <v-btn variant="text" size="small" color="primary">
                                Editar
                            </v-btn>
                        </Link>
                        <v-btn
                            v-if="row(item).status === 'active' && canUpdate"
                            variant="text"
                            size="small"
                            color="error"
                            @click="askDeactivate(row(item))"
                        >
                            Desactivar
                        </v-btn>
                    </div>
                </template>
                <template v-if="can?.create" #empty-action>
                    <Link :href="route('customers.create')">
                        <v-btn color="primary" variant="flat">Nuevo cliente</v-btn>
                    </Link>
                </template>
            </ServerDataTable>
        </PanelCard>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Desactivar cliente"
            :message="pendingCustomer
                ? `¿Desactivar a ${pendingCustomer.name}? El historial permanecerá consultable.`
                : undefined"
            confirm-text="Desactivar"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
