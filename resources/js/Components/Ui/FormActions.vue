<script setup lang="ts">
withDefaults(
    defineProps<{
        processing?: boolean;
        saveText?: string;
        cancelText?: string;
        showCancel?: boolean;
    }>(),
    {
        processing: false,
        saveText: 'Guardar',
        cancelText: 'Cancelar',
        showCancel: true,
    },
);

const emit = defineEmits<{
    cancel: [];
}>();
</script>

<template>
    <div class="d-flex flex-wrap align-center justify-end ga-2 w-100">
        <slot name="prepend" />
        <v-btn
            v-if="showCancel"
            variant="text"
            :disabled="processing"
            @click="emit('cancel')"
        >
            {{ cancelText }}
        </v-btn>
        <slot />
        <v-btn
            type="submit"
            color="primary"
            variant="flat"
            :loading="processing"
        >
            {{ saveText }}
        </v-btn>
    </div>
</template>
