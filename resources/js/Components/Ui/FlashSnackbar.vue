<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

type SnackbarColor = 'success' | 'error' | 'info' | 'warning';

const page = usePage();
const visible = ref(false);
const message = ref('');
const color = ref<SnackbarColor>('success');

const flashSuccess = computed(() => {
    const flash = page.props.flash as { success?: string | null } | undefined;

    return flash?.success ?? null;
});

const validationErrors = computed(() => {
    const errors = page.props.errors as Record<string, string> | undefined;

    if (!errors) {
        return [];
    }

    return Object.values(errors).filter(Boolean);
});

watch(
    () => [flashSuccess.value, page.url, validationErrors.value.join('|')] as const,
    () => {
        if (flashSuccess.value) {
            message.value = String(flashSuccess.value);
            color.value = 'success';
            visible.value = true;

            return;
        }

        if (validationErrors.value.length > 0) {
            message.value =
                validationErrors.value.length === 1
                    ? validationErrors.value[0]
                    : `Hay ${validationErrors.value.length} errores de validación.`;
            color.value = 'error';
            visible.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <v-snackbar v-model="visible" :color="color" location="top end" timeout="5000" rounded="lg">
        {{ message }}
        <template #actions>
            <v-btn variant="text" aria-label="Cerrar" @click="visible = false">
                Cerrar
            </v-btn>
        </template>
    </v-snackbar>
</template>
