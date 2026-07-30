<script setup lang="ts">
withDefaults(
    defineProps<{
        dense?: boolean;
    }>(),
    {
        dense: false,
    },
);
</script>

<template>
    <v-card class="mb-4">
        <v-card-text :class="dense ? 'py-3' : undefined">
            <div class="d-flex flex-wrap align-center ga-3">
                <slot />
                <v-spacer />
                <div v-if="$slots.actions" class="d-flex flex-wrap ga-2">
                    <slot name="actions" />
                </div>
            </div>
        </v-card-text>
    </v-card>
</template>
