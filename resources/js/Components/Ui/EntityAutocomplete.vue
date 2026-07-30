<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';

export type EntityKind = 'customers' | 'vehicles';

export interface EntityOption {
    id: number | string;
    title: string;
    subtitle?: string;
    raw?: Record<string, unknown>;
}

const model = defineModel<number | string | null>({ default: null });

const props = withDefaults(
    defineProps<{
        entity: EntityKind;
        label?: string;
        placeholder?: string;
        errorMessages?: string | string[];
        disabled?: boolean;
        clearable?: boolean;
        initialItems?: EntityOption[];
    }>(),
    {
        label: undefined,
        placeholder: 'Buscar…',
        errorMessages: () => [],
        disabled: false,
        clearable: true,
        initialItems: () => [],
    },
);

const items = ref<EntityOption[]>([...props.initialItems]);
const search = ref('');
const loading = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const fieldLabel = computed(() => {
    if (props.label) {
        return props.label;
    }

    return props.entity === 'customers' ? 'Cliente' : 'Unidad';
});

async function fetchOptions(query: string): Promise<void> {
    loading.value = true;

    try {
        if (props.entity === 'customers') {
            const { data } = await axios.get(route('customers.options'), {
                params: { search: query || undefined },
                headers: { Accept: 'application/json' },
            });

            items.value = (data.data ?? []).map(
                (row: { id: number; name: string; trade_name?: string | null; email?: string | null }) => ({
                    id: row.id,
                    title: row.name,
                    subtitle: row.trade_name || row.email || undefined,
                    raw: row as unknown as Record<string, unknown>,
                }),
            );
        } else {
            const { data } = await axios.get(route('vehicles.search'), {
                params: { q: query || undefined, json: 1 },
                headers: { Accept: 'application/json' },
            });

            items.value = (data.data ?? []).map(
                (row: {
                    id: number;
                    license_plate: string;
                    brand?: string;
                    model?: string;
                    customer_name?: string;
                }) => ({
                    id: row.id,
                    title: row.license_plate,
                    subtitle: [row.brand, row.model, row.customer_name].filter(Boolean).join(' · ') || undefined,
                    raw: row as unknown as Record<string, unknown>,
                }),
            );
        }
    } finally {
        loading.value = false;
    }
}

watch(search, (value) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        void fetchOptions(value.trim());
    }, 300);
});

watch(
    () => props.initialItems,
    (value) => {
        if (value.length) {
            items.value = [...value];
        }
    },
);
</script>

<template>
    <v-autocomplete
        v-model="model"
        v-model:search="search"
        :items="items"
        item-title="title"
        item-value="id"
        :label="fieldLabel"
        :placeholder="placeholder"
        :loading="loading"
        :disabled="disabled"
        :clearable="clearable"
        :error-messages="errorMessages"
        no-filter
        :no-data-text="loading ? 'Buscando…' : 'Sin coincidencias'"
    >
        <template #item="{ props: itemProps, item }">
            <v-list-item
                v-bind="itemProps"
                :subtitle="(item.raw as { subtitle?: string } | undefined)?.subtitle"
            />
        </template>
    </v-autocomplete>
</template>
