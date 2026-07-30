<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import { useAppPage } from '@/Composables/useAppPage';
import { computed } from 'vue';

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
    confirmsTwoFactorAuthentication?: boolean;
    sessions: BrowserSession[];
}>();

const page = useAppPage();
const jetstream = computed(() => page.props.jetstream);
const authUser = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout title="Perfil">
        <PageHeader
            title="Perfil"
            subtitle="Administra tu cuenta, seguridad y sesiones"
        />

        <div class="d-flex flex-column ga-8">
            <UpdateProfileInformationForm
                v-if="jetstream.canUpdateProfileInformation && authUser"
                :user="authUser"
            />

            <UpdatePasswordForm v-if="jetstream.canUpdatePassword" />

            <TwoFactorAuthenticationForm
                v-if="jetstream.canManageTwoFactorAuthentication"
                :requires-confirmation="confirmsTwoFactorAuthentication"
            />

            <LogoutOtherBrowserSessionsForm :sessions="sessions" />

            <DeleteUserForm v-if="jetstream.hasAccountDeletionFeatures" />
        </div>
    </AppLayout>
</template>
