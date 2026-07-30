<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

function submit(): void {
    form.post(route('verification.send'));
}
</script>

<template>
    <GuestLayout
        title="Verifica tu correo"
        subtitle="Revisa tu bandeja para continuar"
    >
        <v-alert type="info" variant="tonal" density="comfortable" class="mb-4">
            Antes de continuar, verifica tu correo con el enlace que te enviamos.
            Si no lo recibiste, podemos enviarte otro.
        </v-alert>

        <v-alert
            v-if="verificationLinkSent"
            type="success"
            variant="tonal"
            density="comfortable"
            class="mb-4"
        >
            Se envió un nuevo enlace de verificación a tu correo.
        </v-alert>

        <v-form @submit.prevent="submit">
            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                block
                size="large"
                class="mb-4"
                :loading="form.processing"
            >
                Reenviar correo de verificación
            </v-btn>

            <div class="d-flex justify-center flex-wrap ga-3">
                <Link :href="route('profile.show')" class="text-body-2 text-decoration-none">
                    Editar perfil
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-body-2 text-decoration-none bg-transparent border-0 cursor-pointer"
                >
                    Cerrar sesión
                </Link>
            </div>
        </v-form>
    </GuestLayout>
</template>
