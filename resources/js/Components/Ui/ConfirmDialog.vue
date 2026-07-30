<script setup lang="ts">
const model = defineModel<boolean>({ default: false });

withDefaults(
    defineProps<{
        title?: string;
        message?: string;
        confirmText?: string;
        cancelText?: string;
        confirmColor?: string;
        loading?: boolean;
        maxWidth?: string | number;
    }>(),
    {
        title: 'Confirmar acción',
        message: '¿Deseas continuar?',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        confirmColor: 'error',
        loading: false,
        maxWidth: 480,
    },
);

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

function onCancel(): void {
    model.value = false;
    emit('cancel');
}

function onConfirm(): void {
    emit('confirm');
}
</script>

<template>
    <v-dialog v-model="model" :max-width="maxWidth" persistent>
        <v-card>
            <v-card-title class="text-h6">
                {{ title }}
            </v-card-title>
            <v-card-text>
                <slot>
                    {{ message }}
                </slot>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" :disabled="loading" @click="onCancel">
                    {{ cancelText }}
                </v-btn>
                <v-btn
                    :color="confirmColor"
                    variant="flat"
                    :loading="loading"
                    @click="onConfirm"
                >
                    {{ confirmText }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
