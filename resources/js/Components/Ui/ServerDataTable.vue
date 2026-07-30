<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import EmptyState from '@/Components/Ui/EmptyState.vue';

export interface DataTableHeader {
    title: string;
    key: string;
    sortable?: boolean;
    align?: 'start' | 'end' | 'center';
    width?: string | number;
}

export interface LaravelPaginator {
    data: unknown[];
    current_page: number;
    per_page: number;
    total: number;
    last_page?: number;
    from?: number | null;
    to?: number | null;
    path?: string;
    links?: unknown[];
}

const props = withDefaults(
    defineProps<{
        headers: DataTableHeader[];
        items: LaravelPaginator | unknown[];
        routeName: string;
        routeParams?: Record<string, unknown>;
        filters?: Record<string, unknown>;
        loading?: boolean;
        itemValue?: string;
        emptyTitle?: string;
        emptyMessage?: string;
    }>(),
    {
        routeParams: () => ({}),
        filters: () => ({}),
        loading: false,
        itemValue: 'id',
        emptyTitle: 'Sin resultados',
        emptyMessage: 'No hay registros que coincidan con los filtros actuales.',
    },
);

const rows = computed(() => {
    if (Array.isArray(props.items)) {
        return props.items;
    }

    return props.items.data ?? [];
});

const totalItems = computed(() => {
    if (Array.isArray(props.items)) {
        return props.items.length;
    }

    return props.items.total ?? rows.value.length;
});

const page = computed(() => {
    if (Array.isArray(props.items)) {
        return 1;
    }

    return props.items.current_page ?? 1;
});

const itemsPerPage = computed(() => {
    if (Array.isArray(props.items)) {
        return rows.value.length || 15;
    }

    return props.items.per_page ?? 15;
});

function onUpdateOptions(options: {
    page: number;
    itemsPerPage: number;
    sortBy: Array<{ key: string; order: 'asc' | 'desc' }>;
}): void {
    const sort = options.sortBy[0];

    router.get(
        route(props.routeName, props.routeParams),
        {
            ...props.filters,
            page: options.page,
            per_page: options.itemsPerPage === -1 ? undefined : options.itemsPerPage,
            sort: sort?.key,
            direction: sort?.order,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>

<template>
    <v-data-table-server
        :headers="headers"
        :items="rows"
        :items-length="totalItems"
        :loading="loading"
        :item-value="itemValue"
        :page="page"
        :items-per-page="itemsPerPage"
        class="bg-transparent"
        @update:options="onUpdateOptions"
    >
        <template
            v-for="(_, name) in $slots"
            #[name]="slotData"
        >
            <slot :name="name" v-bind="slotData ?? {}" />
        </template>

        <template #no-data>
            <EmptyState :title="emptyTitle" :message="emptyMessage">
                <template v-if="$slots['empty-action']" #action>
                    <slot name="empty-action" />
                </template>
            </EmptyState>
        </template>
    </v-data-table-server>
</template>
