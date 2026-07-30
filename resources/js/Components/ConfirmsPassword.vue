<script setup lang="ts">
import { nextTick, reactive, ref } from 'vue';
import axios from 'axios';

const emit = defineEmits<{
    confirmed: [];
}>();

withDefaults(
    defineProps<{
        title?: string;
        content?: string;
        button?: string;
    }>(),
    {
        title: 'Confirm Password',
        content: 'For your security, please confirm your password to continue.',
        button: 'Confirm',
    },
);

const confirmingPassword = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = reactive({
    password: '',
    error: '',
    processing: false,
});

function startConfirmingPassword(): void {
    axios.get(route('password.confirmation')).then((response) => {
        if (response.data.confirmed) {
            emit('confirmed');
        } else {
            confirmingPassword.value = true;
            setTimeout(() => passwordInput.value?.focus(), 250);
        }
    });
}

function confirmPassword(): void {
    form.processing = true;

    axios
        .post(route('password.confirm'), {
            password: form.password,
        })
        .then(() => {
            form.processing = false;
            closeModal();
            nextTick().then(() => emit('confirmed'));
        })
        .catch((error) => {
            form.processing = false;
            form.error = error.response?.data?.errors?.password?.[0] ?? 'Unable to confirm password.';
            passwordInput.value?.focus();
        });
}

function closeModal(): void {
    confirmingPassword.value = false;
    form.password = '';
    form.error = '';
}
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <v-dialog v-model="confirmingPassword" max-width="480" persistent>
            <v-card>
                <v-card-title class="text-h6">
                    {{ title }}
                </v-card-title>
                <v-card-text>
                    <p class="text-body-2 text-medium-emphasis mb-4">
                        {{ content }}
                    </p>
                    <v-text-field
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        label="Password"
                        autocomplete="current-password"
                        :error-messages="form.error ? [form.error] : []"
                        @keyup.enter="confirmPassword"
                    />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" :disabled="form.processing" @click="closeModal">
                        Cancel
                    </v-btn>
                    <v-btn
                        color="primary"
                        variant="flat"
                        :loading="form.processing"
                        @click="confirmPassword"
                    >
                        {{ button }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </span>
</template>
