<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout title="Iniciar sesión" subtitle="Accede a tu taller Mantto">
        <v-alert
            v-if="status"
            type="success"
            variant="tonal"
            density="comfortable"
            class="mb-4"
        >
            {{ status }}
        </v-alert>

        <v-form @submit.prevent="submit">
            <v-text-field
                v-model="form.email"
                label="Correo electrónico"
                type="email"
                autocomplete="username"
                autofocus
                required
                class="mb-3"
                :error-messages="form.errors.email"
            />

            <v-text-field
                v-model="form.password"
                label="Contraseña"
                type="password"
                autocomplete="current-password"
                required
                class="mb-2"
                :error-messages="form.errors.password"
            />

            <div class="d-flex align-center justify-space-between flex-wrap ga-2 mb-4">
                <v-checkbox
                    v-model="form.remember"
                    label="Recordarme"
                    density="compact"
                    hide-details
                    color="primary"
                />
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-body-2 text-decoration-none"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                block
                size="large"
                :loading="form.processing"
            >
                Entrar
            </v-btn>
        </v-form>
    </GuestLayout>
</template>
