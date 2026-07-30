<script setup lang="ts">
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

defineProps<{
    tokens: Array<{
        id: number;
        name: string;
        abilities: string[];
        last_used_ago?: string | null;
    }>;
    availablePermissions: string[];
    defaultPermissions: string[];
}>();
</script>

<template>
    <AppLayout title="API Tokens">
        <PageHeader
            title="API Tokens"
            subtitle="Crea y administra tokens de acceso a la API"
        />

        <ApiTokenManager
            :tokens="tokens"
            :available-permissions="availablePermissions"
            :default-permissions="defaultPermissions"
        />
    </AppLayout>
</template>
