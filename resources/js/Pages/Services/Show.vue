<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { activeFlagMap } from '@/Components/Ui/statusMaps';

const props = defineProps<{
    service: {
        id: number;
        code: string;
        description: string;
        type_label: string;
        base_price: number | string;
        base_price_note?: string;
        unit_of_measure: string;
        estimated_minutes?: number | null;
        is_active: boolean;
        in_use?: boolean;
        category?: { name?: string } | null;
    };
}>();

const confirmOpen = ref(false);
const processing = ref(false);

const detailItems = computed(() => [
    { label: 'Descripción', value: props.service.description, key: 'description' },
    { label: 'Categoría', value: props.service.category?.name ?? '—', key: 'category' },
    { label: 'Tipo', value: props.service.type_label, key: 'type' },
    { label: 'Precio base', value: props.service.base_price, key: 'price' },
    { label: 'Unidad', value: props.service.unit_of_measure, key: 'unit' },
    {
        label: 'Tiempo estimado',
        value: props.service.estimated_minutes != null ? `${props.service.estimated_minutes} min` : '—',
        key: 'minutes',
    },
    { label: 'Estatus', value: props.service.is_active ? 'Activo' : 'Inactivo', key: 'status' },
    {
        label: 'En uso (RN-CAT-001)',
        value: props.service.in_use ? 'Sí — solo desactivable' : 'No',
        key: 'in_use',
    },
]);

function askDeactivate(): void {
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    processing.value = true;
    router.post(route('service-catalog.deactivate', props.service.id), {}, {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
        },
    });
}
</script>

<template>
    <AppLayout title="Servicio">
        <PageHeader
            :title="service.code"
            :breadcrumbs="[
                { title: 'Servicios', href: route('service-catalog.index') },
                { title: service.code, disabled: true },
            ]"
        >
            <template #actions>
                <Link :href="route('service-catalog.edit', service.id)">
                    <v-btn color="primary" variant="flat">Editar</v-btn>
                </Link>
                <v-btn
                    v-if="service.is_active"
                    variant="tonal"
                    color="warning"
                    @click="askDeactivate"
                >
                    Desactivar
                </v-btn>
            </template>
        </PageHeader>

        <v-alert
            v-if="service.base_price_note"
            type="warning"
            variant="tonal"
            class="mb-4"
        >
            {{ service.base_price_note }}
        </v-alert>

        <PanelCard>
            <DescriptionList :items="detailItems">
                <template #price>
                    <MoneyText :amount="service.base_price" />
                </template>
                <template #status>
                    <StatusChip
                        :status="service.is_active ? 'active' : 'inactive'"
                        :map="activeFlagMap"
                    />
                </template>
            </DescriptionList>
        </PanelCard>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Desactivar servicio"
            :message="`¿Desactivar el servicio ${service.code}?`"
            confirm-text="Desactivar"
            confirm-color="warning"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
