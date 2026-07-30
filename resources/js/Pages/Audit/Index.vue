<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DateText from '@/Components/Ui/DateText.vue';
import FilterBar from '@/Components/Ui/FilterBar.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import ServerDataTable from '@/Components/Ui/ServerDataTable.vue';
import type { DataTableHeader, LaravelPaginator } from '@/Components/Ui/ServerDataTable.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface UserOption {
    id: number;
    name: string;
}

interface AuditLog {
    id: number;
    created_at?: string | null;
    action: string;
    subject_type_label: string;
    subject_id?: number | string | null;
    ip_address?: string | null;
    user?: { name?: string } | null;
    properties?: unknown;
}

const props = defineProps<{
    logs: LaravelPaginator;
    filters: {
        entity?: string;
        user_id?: string | number;
        action?: string;
        from?: string;
        to?: string;
    };
    actions: string[];
    entities: SelectOption[];
    users: UserOption[];
}>();

const entity = ref(props.filters.entity ?? '');
const userId = ref(props.filters.user_id ? String(props.filters.user_id) : '');
const action = ref(props.filters.action ?? '');
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');
const selectedLogId = ref<number | null>(null);

const headers: DataTableHeader[] = [
    { title: 'Fecha', key: 'created_at' },
    { title: 'Usuario', key: 'user' },
    { title: 'Acción', key: 'action' },
    { title: 'Entidad', key: 'subject_type_label' },
    { title: 'IP', key: 'ip_address' },
    { title: 'Detalle', key: 'actions', align: 'end', sortable: false },
];

const entityItems = computed(() => [
    { value: '', title: 'Todas' },
    ...props.entities.map((option) => ({ value: option.value, title: option.label })),
]);

const userItems = computed(() => [
    { value: '', title: 'Todos' },
    ...props.users.map((user) => ({ value: String(user.id), title: user.name })),
]);

const actionItems = computed(() => [
    { value: '', title: 'Todas' },
    ...props.actions.map((option) => ({ value: option, title: option })),
]);

const filterParams = computed(() => ({
    entity: entity.value || undefined,
    user_id: userId.value || undefined,
    action: action.value || undefined,
    from: from.value || undefined,
    to: to.value || undefined,
}));

const selectedLog = computed(() => {
    const rows = (props.logs.data ?? []) as AuditLog[];
    return rows.find((log) => log.id === selectedLogId.value) ?? null;
});

watch([entity, userId, action, from, to], () => {
    router.get(route('audit.index'), filterParams.value, {
        preserveState: true,
        replace: true,
    });
});

function formatJson(value: unknown): string {
    if (value === null || value === undefined) {
        return '—';
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return String(value);
    }
}

function toggleDetail(log: AuditLog): void {
    selectedLogId.value = selectedLogId.value === log.id ? null : log.id;
}

function row(item: unknown): AuditLog {
    return item as AuditLog;
}
</script>

<template>
    <AppLayout title="Auditoría">
        <PageHeader
            title="Visor de auditoría"
            subtitle="Consulta de cambios por entidad, usuario y periodo."
        />

        <FilterBar>
            <v-select
                v-model="entity"
                :items="entityItems"
                label="Entidad"
                hide-details
                style="max-width: 12rem"
            />
            <v-select
                v-model="userId"
                :items="userItems"
                label="Usuario"
                hide-details
                style="max-width: 14rem"
            />
            <v-select
                v-model="action"
                :items="actionItems"
                label="Acción"
                hide-details
                style="max-width: 12rem"
            />
            <v-text-field
                v-model="from"
                type="date"
                label="Desde"
                hide-details
                style="max-width: 11rem"
            />
            <v-text-field
                v-model="to"
                type="date"
                label="Hasta"
                hide-details
                style="max-width: 11rem"
            />
        </FilterBar>

        <PanelCard>
            <ServerDataTable
                :headers="headers"
                :items="logs"
                route-name="audit.index"
                :filters="filterParams"
                empty-title="Sin registros"
                empty-message="No hay registros con los filtros seleccionados."
            >
                <template #item.created_at="{ item }">
                    <DateText :value="row(item).created_at" time-style="short" />
                </template>
                <template #item.user="{ item }">
                    {{ row(item).user?.name ?? 'Sistema' }}
                </template>
                <template #item.subject_type_label="{ item }">
                    {{ row(item).subject_type_label }}
                    <span v-if="row(item).subject_id" class="text-medium-emphasis">
                        #{{ row(item).subject_id }}
                    </span>
                </template>
                <template #item.ip_address="{ item }">
                    <span class="font-mono text-body-2">{{ row(item).ip_address ?? '—' }}</span>
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex justify-end">
                        <v-btn
                            variant="text"
                            size="small"
                            color="primary"
                            @click="toggleDetail(row(item))"
                        >
                            {{ selectedLogId === row(item).id ? 'Ocultar' : 'Ver' }}
                        </v-btn>
                    </div>
                </template>
            </ServerDataTable>

            <v-card
                v-if="selectedLog"
                variant="tonal"
                class="mt-4 pa-4"
            >
                <h4 class="text-subtitle-2 font-weight-medium mb-2">
                    Cambios (before / after)
                </h4>
                <pre
                    class="text-caption mb-0 pa-3 rounded bg-surface"
                    style="overflow-x: auto; white-space: pre-wrap"
                >{{ formatJson(selectedLog.properties) }}</pre>
            </v-card>
        </PanelCard>
    </AppLayout>
</template>
