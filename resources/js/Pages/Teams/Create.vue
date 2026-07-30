<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import CreateTeamForm from '@/Pages/Teams/Partials/CreateTeamForm.vue';
</script>

<template>
    <AppLayout title="Create Team">
        <PageHeader
            title="Create Team"
            subtitle="Start a new team to collaborate"
        />
        <CreateTeamForm />
    </AppLayout>
</template>
