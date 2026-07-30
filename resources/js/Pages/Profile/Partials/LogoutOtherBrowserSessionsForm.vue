<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/Ui/ActionSection.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

interface BrowserSession {
    agent: {
        is_desktop: boolean;
        platform: string | null;
        browser: string | null;
    };
    ip_address: string;
    is_current_device: boolean;
    last_active: string;
}

defineProps<{
    sessions: BrowserSession[];
}>();

const confirmingLogout = ref(false);
const passwordInput = ref<{ focus: () => void } | null>(null);

const form = useForm({
    password: '',
});

async function confirmLogout(): Promise<void> {
    confirmingLogout.value = true;
    await nextTick();
    setTimeout(() => passwordInput.value?.focus(), 250);
}

function logoutOtherBrowserSessions(): void {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
}

function closeModal(): void {
    confirmingLogout.value = false;
    form.reset();
}
</script>

<template>
    <ActionSection
        title="Browser Sessions"
        description="Manage and log out your active sessions on other browsers and devices."
    >
        <template #content>
            <p class="text-body-2 text-medium-emphasis mb-4">
                If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.
            </p>

            <v-list v-if="sessions.length > 0" lines="two" class="mb-4 bg-transparent">
                <v-list-item
                    v-for="(session, i) in sessions"
                    :key="i"
                    :prepend-icon="session.agent.is_desktop ? 'mdi-monitor' : 'mdi-cellphone'"
                >
                    <v-list-item-title>
                        {{ session.agent.platform || 'Unknown' }} — {{ session.agent.browser || 'Unknown' }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                        {{ session.ip_address }},
                        <span v-if="session.is_current_device" class="text-success font-weight-medium">
                            This device
                        </span>
                        <span v-else>
                            Last active {{ session.last_active }}
                        </span>
                    </v-list-item-subtitle>
                </v-list-item>
            </v-list>

            <div class="d-flex align-center flex-wrap ga-3">
                <v-btn color="primary" variant="flat" @click="confirmLogout">
                    Log Out Other Browser Sessions
                </v-btn>
                <v-fade-transition>
                    <span v-if="form.recentlySuccessful" class="text-success text-body-2">
                        Done.
                    </span>
                </v-fade-transition>
            </div>

            <ConfirmDialog
                v-model="confirmingLogout"
                title="Log Out Other Browser Sessions"
                confirm-text="Log Out Other Browser Sessions"
                confirm-color="primary"
                :loading="form.processing"
                @confirm="logoutOtherBrowserSessions"
                @cancel="closeModal"
            >
                <p class="text-body-2 text-medium-emphasis mb-4">
                    Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.
                </p>
                <v-text-field
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    autocomplete="current-password"
                    :error-messages="form.errors.password"
                    @keyup.enter="logoutOtherBrowserSessions"
                />
            </ConfirmDialog>
        </template>
    </ActionSection>
</template>
