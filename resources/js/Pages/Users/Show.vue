<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { userStatusMap } from '@/Components/Ui/statusMaps';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        status: string;
        status_label: string;
        created_at: string;
    };
}>();

const detailItems = computed(() => [
    { label: 'Email', value: props.user.email, key: 'email' },
    { label: 'Rol', value: props.user.role, key: 'role' },
    { label: 'Estatus', value: props.user.status_label, key: 'status' },
    { label: 'Creado', value: props.user.created_at, key: 'created' },
]);
</script>

<template>
    <AppLayout title="Detalle de usuario">
        <PageHeader
            :title="user.name"
            :breadcrumbs="[
                { title: 'Usuarios', href: route('users.index') },
                { title: user.name, disabled: true },
            ]"
        >
            <template #actions>
                <Link :href="route('users.index')">
                    <v-btn variant="text">Volver</v-btn>
                </Link>
                <Link :href="route('users.edit', user.id)">
                    <v-btn color="primary" variant="flat">Editar</v-btn>
                </Link>
            </template>
        </PageHeader>

        <PanelCard>
            <DescriptionList :items="detailItems">
                <template #status>
                    <StatusChip :status="user.status" :map="userStatusMap" />
                </template>
            </DescriptionList>
        </PanelCard>
    </AppLayout>
</template>
