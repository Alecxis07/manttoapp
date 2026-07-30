<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/Ui/FormSection.vue';
import FormActions from '@/Components/Ui/FormActions.vue';

const passwordInput = ref<{ focus: () => void } | null>(null);
const currentPasswordInput = ref<{ focus: () => void } | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updatePassword(): void {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
}
</script>

<template>
    <FormSection
        title="Update Password"
        description="Ensure your account is using a long, random password to stay secure."
        @submitted="updatePassword"
    >
        <template #form>
            <v-text-field
                ref="currentPasswordInput"
                v-model="form.current_password"
                label="Current Password"
                type="password"
                autocomplete="current-password"
                class="mb-3"
                :error-messages="form.errors.current_password"
            />

            <v-text-field
                ref="passwordInput"
                v-model="form.password"
                label="New Password"
                type="password"
                autocomplete="new-password"
                class="mb-3"
                :error-messages="form.errors.password"
            />

            <v-text-field
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                autocomplete="new-password"
                :error-messages="form.errors.password_confirmation"
            />
        </template>

        <template #actions>
            <FormActions
                :processing="form.processing"
                :show-cancel="false"
                save-text="Save"
            >
                <template #prepend>
                    <v-fade-transition>
                        <span
                            v-if="form.recentlySuccessful"
                            class="text-success text-body-2 me-2"
                        >
                            Saved.
                        </span>
                    </v-fade-transition>
                </template>
            </FormActions>
        </template>
    </FormSection>
</template>
