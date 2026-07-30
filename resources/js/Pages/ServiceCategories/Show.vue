<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { activeFlagFeminineMap } from '@/Components/Ui/statusMaps';

const props = defineProps<{
    category: {
        id: number;
        code: string;
        name: string;
        description?: string | null;
        is_active: boolean;
        services_count: number;
        parts_count: number;
        in_use?: boolean;
    };
}>();

const confirmOpen = ref(false);
const processing = ref(false);

const detailItems = computed(() => [
    { label: 'Código', value: props.category.code, key: 'code' },
    { label: 'Estatus', value: props.category.is_active ? 'Activa' : 'Inactiva', key: 'status' },
    { label: 'Descripción', value: props.category.description || '—', key: 'description' },
    { label: 'Servicios asociados', value: props.category.services_count, key: 'services' },
    { label: 'Refacciones asociadas', value: props.category.parts_count, key: 'parts' },
    {
        label: 'En uso (RN-CAT-001)',
        value: props.category.in_use ? 'Sí — solo desactivable' : 'No',
        key: 'in_use',
    },
]);

function askDeactivate(): void {
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    processing.value = true;
    router.post(route('service-categories.deactivate', props.category.id), {}, {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
        },
    });
}
</script>

<template>
    <AppLayout title="Categoría">
        <PageHeader
            :title="category.name"
            :breadcrumbs="[
                { title: 'Categorías', href: route('service-categories.index') },
                { title: category.name, disabled: true },
            ]"
        >
            <template #actions>
                <Link :href="route('service-categories.edit', category.id)">
                    <v-btn color="primary" variant="flat">Editar</v-btn>
                </Link>
                <v-btn
                    v-if="category.is_active"
                    variant="tonal"
                    color="warning"
                    @click="askDeactivate"
                >
                    Desactivar
                </v-btn>
            </template>
        </PageHeader>

        <PanelCard>
            <DescriptionList :items="detailItems" :columns="2">
                <template #status>
                    <StatusChip
                        :status="category.is_active ? 'active' : 'inactive'"
                        :map="activeFlagFeminineMap"
                    />
                </template>
            </DescriptionList>
        </PanelCard>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Desactivar categoría"
            :message="`¿Desactivar la categoría ${category.name}?`"
            confirm-text="Desactivar"
            confirm-color="warning"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
