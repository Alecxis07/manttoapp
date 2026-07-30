<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout title="Nueva contraseña" subtitle="Elige una contraseña segura">
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
                autocomplete="new-password"
                required
                class="mb-3"
                :error-messages="form.errors.password"
            />

            <v-text-field
                v-model="form.password_confirmation"
                label="Confirmar contraseña"
                type="password"
                autocomplete="new-password"
                required
                class="mb-4"
                :error-messages="form.errors.password_confirmation"
            />

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                block
                size="large"
                :loading="form.processing"
            >
                Restablecer contraseña
            </v-btn>
        </v-form>
    </GuestLayout>
</template>
