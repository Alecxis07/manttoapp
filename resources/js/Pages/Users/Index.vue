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
import { userStatusMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string;
    label: string;
}

interface UserRow {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    status_label: string;
}

const props = defineProps<{
    users: LaravelPaginator;
    filters: {
        search?: string;
        status?: string;
    };
    statuses: SelectOption[];
}>();

const page = usePage();
const authUserId = computed(
    () => (page.props.auth as { user?: { id?: number } })?.user?.id,
);

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const confirmOpen = ref(false);
const processing = ref(false);
const pending = ref<UserRow | null>(null);

const headers: DataTableHeader[] = [
    { title: 'Nombre', key: 'name' },
    { title: 'Email', key: 'email' },
    { title: 'Rol', key: 'role' },
    { title: 'Estatus', key: 'status', sortable: false },
    { title: 'Acciones', key: 'actions', align: 'end', sortable: false },
];

const statusItems = computed(() => [
    { value: '', title: 'Todos los estatus' },
    ...props.statuses.map((option) => ({ value: option.value, title: option.label })),
]);

const filterParams = computed(() => ({
    search: search.value || undefined,
    status: status.value || undefined,
}));

watch([search, status], () => {
    router.get(route('users.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function askDeactivate(user: UserRow): void {
    pending.value = user;
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    if (!pending.value) {
        return;
    }

    processing.value = true;
    router.delete(route('users.destroy', pending.value.id), {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
            pending.value = null;
        },
    });
}

function row(item: unknown): UserRow {
    return item as UserRow;
}
</script>

<template>
    <AppLayout title="Usuarios">
        <PageHeader title="Usuarios" subtitle="Accesos y roles del sistema.">
            <template #actions>
                <Link :href="route('users.create')">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">
                        Nuevo usuario
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <FilterBar>
            <v-text-field
                v-model="search"
                type="search"
                label="Buscar"
                placeholder="Nombre o email"
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
                style="max-width: 12rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="users"
                route-name="users.index"
                :filters="filterParams"
                empty-title="Sin usuarios"
                empty-message="No hay usuarios para mostrar."
            >
                <template #item.status="{ item }">
                    <StatusChip :status="row(item).status" :map="userStatusMap" />
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <Link :href="route('users.show', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">Ver</v-btn>
                        </Link>
                        <Link :href="route('users.edit', row(item).id)">
                            <v-btn variant="text" size="small" color="primary">Editar</v-btn>
                        </Link>
                        <v-btn
                            v-if="row(item).status === 'active' && row(item).id !== authUserId"
                            variant="text"
                            size="small"
                            color="error"
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
            title="Desactivar usuario"
            :message="pending ? `¿Desactivar a ${pending.name}?` : undefined"
            confirm-text="Desactivar"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
