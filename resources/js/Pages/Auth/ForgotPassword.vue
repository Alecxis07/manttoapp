<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

function submit(): void {
    form.post(route('password.email'));
}
</script>

<template>
    <GuestLayout
        title="Recuperar contraseña"
        subtitle="Te enviaremos un enlace para restablecerla"
    >
        <v-alert
            type="info"
            variant="tonal"
            density="comfortable"
            class="mb-4"
        >
            Indica tu correo y te enviaremos un enlace para elegir una nueva contraseña.
        </v-alert>

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
                class="mb-4"
                :error-messages="form.errors.email"
            />

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                block
                size="large"
                class="mb-3"
                :loading="form.processing"
            >
                Enviar enlace
            </v-btn>

            <div class="text-center">
                <Link :href="route('login')" class="text-body-2 text-decoration-none">
                    Volver a iniciar sesión
                </Link>
            </div>
        </v-form>
    </GuestLayout>
</template>
