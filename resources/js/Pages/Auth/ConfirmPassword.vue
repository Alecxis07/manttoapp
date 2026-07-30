<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    password: '',
});

const passwordInput = ref<{ focus: () => void } | null>(null);

function submit(): void {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
            passwordInput.value?.focus();
        },
    });
}
</script>

<template>
    <GuestLayout
        title="Área segura"
        subtitle="Confirma tu contraseña para continuar"
    >
        <v-alert type="info" variant="tonal" density="comfortable" class="mb-4">
            Esta es una zona segura de la aplicación. Confirma tu contraseña antes de continuar.
        </v-alert>

        <v-form @submit.prevent="submit">
            <v-text-field
                ref="passwordInput"
                v-model="form.password"
                label="Contraseña"
                type="password"
                autocomplete="current-password"
                autofocus
                required
                class="mb-4"
                :error-messages="form.errors.password"
            />

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                block
                size="large"
                :loading="form.processing"
            >
                Confirmar
            </v-btn>
        </v-form>
    </GuestLayout>
</template>
