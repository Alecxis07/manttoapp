<script setup lang="ts">
export interface DescriptionItem {
    label: string;
    value?: string | number | null;
    key?: string;
}

withDefaults(
    defineProps<{
        items: DescriptionItem[];
        columns?: number;
    }>(),
    {
        columns: 2,
    },
);
</script>

<template>
    <v-row density="compact">
        <v-col
            v-for="(item, index) in items"
            :key="item.key ?? `${item.label}-${index}`"
            cols="12"
            :md="12 / columns"
        >
            <div class="text-caption text-medium-emphasis text-uppercase mb-1">
                {{ item.label }}
            </div>
            <div class="text-body-1">
                <slot :name="item.key ?? item.label" :item="item">
                    {{ item.value ?? '—' }}
                </slot>
            </div>
        </v-col>
    </v-row>
</template>
