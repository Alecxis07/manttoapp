<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/Ui/ActionSection.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<{ focus: () => void } | null>(null);

const form = useForm({
    password: '',
});

async function confirmUserDeletion(): Promise<void> {
    confirmingUserDeletion.value = true;
    await nextTick();
    setTimeout(() => passwordInput.value?.focus(), 250);
}

function deleteUser(): void {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
}

function closeModal(): void {
    confirmingUserDeletion.value = false;
    form.reset();
}
</script>

<template>
    <ActionSection
        title="Delete Account"
        description="Permanently delete your account."
    >
        <template #content>
            <p class="text-body-2 text-medium-emphasis mb-4">
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
            </p>

            <v-btn color="error" variant="flat" @click="confirmUserDeletion">
                Delete Account
            </v-btn>

            <ConfirmDialog
                v-model="confirmingUserDeletion"
                title="Delete Account"
                confirm-text="Delete Account"
                confirm-color="error"
                :loading="form.processing"
                @confirm="deleteUser"
                @cancel="closeModal"
            >
                <p class="text-body-2 text-medium-emphasis mb-4">
                    Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                </p>
                <v-text-field
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    autocomplete="current-password"
                    :error-messages="form.errors.password"
                    @keyup.enter="deleteUser"
                />
            </ConfirmDialog>
        </template>
    </ActionSection>
</template>
