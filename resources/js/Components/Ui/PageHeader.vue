<script setup lang="ts">
export interface BreadcrumbItem {
    title: string;
    href?: string;
    disabled?: boolean;
}

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        subtitle: undefined,
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
        <div class="min-w-0">
            <v-breadcrumbs
                v-if="breadcrumbs.length"
                :items="breadcrumbs"
                class="pa-0 mb-1"
                density="compact"
            >
                <template #divider>
                    <v-icon icon="mdi-chevron-right" size="small" />
                </template>
            </v-breadcrumbs>
            <h1 class="text-h5 font-weight-bold text-high-emphasis">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-body-2 text-medium-emphasis mt-1 mb-0">
                {{ subtitle }}
            </p>
        </div>
        <div v-if="$slots.actions" class="d-flex flex-wrap align-center ga-2">
            <slot name="actions" />
        </div>
    </div>
</template>
