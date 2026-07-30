<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const page = usePage();
const hasTerms = computed(
    () => !!(page.props.jetstream as { hasTermsAndPrivacyPolicyFeature?: boolean })
        ?.hasTermsAndPrivacyPolicyFeature,
);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

function submit(): void {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout title="Crear cuenta" subtitle="Regístrate en Mantto">
        <v-form @submit.prevent="submit">
            <v-text-field
                v-model="form.name"
                label="Nombre"
                type="text"
                autocomplete="name"
                autofocus
                required
                class="mb-3"
                :error-messages="form.errors.name"
            />

            <v-text-field
                v-model="form.email"
                label="Correo electrónico"
                type="email"
                autocomplete="username"
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
                class="mb-3"
                :error-messages="form.errors.password_confirmation"
            />

            <div v-if="hasTerms" class="mb-4">
                <v-checkbox
                    v-model="form.terms"
                    density="compact"
                    color="primary"
                    :error-messages="form.errors.terms"
                    required
                >
                    <template #label>
                        <span class="text-body-2">
                            Acepto los
                            <a
                                :href="route('terms.show')"
                                target="_blank"
                                class="text-decoration-none"
                                @click.stop
                            >Términos de servicio</a>
                            y la
                            <a
                                :href="route('policy.show')"
                                target="_blank"
                                class="text-decoration-none"
                                @click.stop
                            >Política de privacidad</a>
                        </span>
                    </template>
                </v-checkbox>
            </div>

            <v-btn
                type="submit"
                color="primary"
                variant="flat"
                block
                size="large"
                class="mb-3"
                :loading="form.processing"
            >
                Registrarse
            </v-btn>

            <div class="text-center">
                <Link :href="route('login')" class="text-body-2 text-decoration-none">
                    ¿Ya tienes cuenta? Inicia sesión
                </Link>
            </div>
        </v-form>
    </GuestLayout>
</template>
