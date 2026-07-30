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
    part: {
        id: number;
        code: string;
        description: string;
        type_label: string;
        base_price: number | string;
        base_price_note?: string;
        unit_of_measure: string;
        is_active: boolean;
        in_use?: boolean;
        category?: { name?: string } | null;
    };
}>();

const confirmOpen = ref(false);
const processing = ref(false);

const detailItems = computed(() => [
    { label: 'Descripción', value: props.part.description, key: 'description' },
    { label: 'Tipo', value: props.part.type_label, key: 'type' },
    { label: 'Categoría', value: props.part.category?.name ?? '—', key: 'category' },
    { label: 'Precio base', value: props.part.base_price, key: 'price' },
    { label: 'Unidad', value: props.part.unit_of_measure, key: 'unit' },
    { label: 'Estatus', value: props.part.is_active ? 'Activo' : 'Inactivo', key: 'status' },
    {
        label: 'En uso (RN-CAT-001)',
        value: props.part.in_use ? 'Sí — solo desactivable' : 'No',
        key: 'in_use',
    },
]);

function askDeactivate(): void {
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    processing.value = true;
    router.post(route('part-catalog.deactivate', props.part.id), {}, {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
        },
    });
}
</script>

<template>
    <AppLayout title="Concepto">
        <PageHeader
            :title="part.code"
            :breadcrumbs="[
                { title: 'Refacciones', href: route('part-catalog.index') },
                { title: part.code, disabled: true },
            ]"
        >
            <template #actions>
                <Link :href="route('part-catalog.edit', part.id)">
                    <v-btn color="primary" variant="flat">Editar</v-btn>
                </Link>
                <v-btn
                    v-if="part.is_active"
                    variant="tonal"
                    color="warning"
                    @click="askDeactivate"
                >
                    Desactivar
                </v-btn>
            </template>
        </PageHeader>

        <v-alert
            v-if="part.base_price_note"
            type="warning"
            variant="tonal"
            class="mb-4"
        >
            {{ part.base_price_note }}
        </v-alert>

        <PanelCard>
            <DescriptionList :items="detailItems">
                <template #price>
                    <MoneyText :amount="part.base_price" />
                </template>
                <template #status>
                    <StatusChip
                        :status="part.is_active ? 'active' : 'inactive'"
                        :map="activeFlagMap"
                    />
                </template>
            </DescriptionList>
        </PanelCard>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Desactivar concepto"
            :message="`¿Desactivar el concepto ${part.code}?`"
            confirm-text="Desactivar"
            confirm-color="warning"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
