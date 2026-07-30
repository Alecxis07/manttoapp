<script setup lang="ts">
const model = defineModel<boolean>({ default: false });

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        maxWidth?: string | number;
        persistent?: boolean;
        loading?: boolean;
        saveText?: string;
        cancelText?: string;
        hideActions?: boolean;
    }>(),
    {
        subtitle: undefined,
        maxWidth: 720,
        persistent: true,
        loading: false,
        saveText: 'Guardar',
        cancelText: 'Cancelar',
        hideActions: false,
    },
);

const emit = defineEmits<{
    save: [];
    cancel: [];
}>();

function onCancel(): void {
    model.value = false;
    emit('cancel');
}
</script>

<template>
    <v-dialog v-model="model" :max-width="maxWidth" :persistent="persistent">
        <v-card>
            <v-card-item>
                <v-card-title>{{ title }}</v-card-title>
                <v-card-subtitle v-if="subtitle">
                    {{ subtitle }}
                </v-card-subtitle>
            </v-card-item>
            <v-card-text>
                <slot />
            </v-card-text>
            <v-card-actions v-if="!hideActions">
                <slot name="actions">
                    <v-spacer />
                    <v-btn variant="text" :disabled="loading" @click="onCancel">
                        {{ cancelText }}
                    </v-btn>
                    <v-btn color="primary" variant="flat" :loading="loading" @click="emit('save')">
                        {{ saveText }}
                    </v-btn>
                </slot>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
