<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref<{ focus: () => void } | null>(null);
const codeInput = ref<{ focus: () => void } | null>(null);

async function toggleRecovery(): Promise<void> {
    recovery.value = !recovery.value;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value?.focus();
        form.code = '';
    } else {
        codeInput.value?.focus();
        form.recovery_code = '';
    }
}

function submit(): void {
    form.post(route('two-factor.login'));
}
</script>

<template>
    <GuestLayout
        title="Verificación en dos pasos"
        :subtitle="recovery
            ? 'Ingresa un código de recuperación'
            : 'Ingresa el código de tu app autenticadora'"
    >
        <v-alert type="info" variant="tonal" density="comfortable" class="mb-4">
            <template v-if="!recovery">
                Confirma el acceso con el código de tu aplicación autenticadora.
            </template>
            <template v-else>
                Confirma el acceso con uno de tus códigos de recuperación de emergencia.
            </template>
        </v-alert>

        <v-form @submit.prevent="submit">
            <v-text-field
                v-if="!recovery"
                ref="codeInput"
                v-model="form.code"
                label="Código"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                autofocus
                class="mb-4"
                :error-messages="form.errors.code"
            />

            <v-text-field
                v-else
                ref="recoveryCodeInput"
                v-model="form.recovery_code"
                label="Código de recuperación"
                type="text"
                autocomplete="one-time-code"
                class="mb-4"
                :error-messages="form.errors.recovery_code"
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
                Entrar
            </v-btn>

            <div class="text-center">
                <v-btn variant="text" size="small" @click.prevent="toggleRecovery">
                    {{ recovery ? 'Usar código de autenticación' : 'Usar código de recuperación' }}
                </v-btn>
            </div>
        </v-form>
    </GuestLayout>
</template>
