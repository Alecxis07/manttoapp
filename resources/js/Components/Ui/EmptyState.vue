<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        message?: string;
        icon?: string;
    }>(),
    {
        message: undefined,
        icon: 'mdi-inbox-outline',
    },
);
</script>

<template>
    <div class="d-flex flex-column align-center justify-center text-center py-12 px-4">
        <v-avatar color="primary" variant="tonal" size="64" class="mb-4">
            <v-icon :icon="icon" size="32" />
        </v-avatar>
        <h3 class="text-h6 font-weight-bold mb-1">
            {{ title }}
        </h3>
        <p v-if="message" class="text-body-2 text-medium-emphasis mb-4" style="max-width: 28rem">
            {{ message }}
        </p>
        <div v-if="$slots.action">
            <slot name="action" />
        </div>
    </div>
</template>
