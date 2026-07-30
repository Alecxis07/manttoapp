<script setup lang="ts">
withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
    }>(),
    {
        title: undefined,
        subtitle: undefined,
    },
);
</script>

<template>
    <v-card>
        <v-card-item v-if="title || $slots.actions">
            <v-card-title v-if="title">
                {{ title }}
            </v-card-title>
            <v-card-subtitle v-if="subtitle">
                {{ subtitle }}
            </v-card-subtitle>
            <template v-if="$slots.actions" #append>
                <slot name="actions" />
            </template>
        </v-card-item>
        <v-card-text>
            <slot />
        </v-card-text>
        <v-card-actions v-if="$slots.footer">
            <slot name="footer" />
        </v-card-actions>
    </v-card>
</template>
