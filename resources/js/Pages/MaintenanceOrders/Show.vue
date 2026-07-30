<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import DateText from '@/Components/Ui/DateText.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import TabsCard from '@/Components/Ui/TabsCard.vue';
import { orderStatusMap } from '@/Components/Ui/statusMaps';
import type { TabItem } from '@/Components/Ui/TabsCard.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface CatalogOption {
    id: number;
    code: string;
    description: string;
    base_price: number | string;
}

interface LineItem {
    id: number;
    description: string;
    quantity: number | string;
    unit_price: number | string;
    discount: number | string;
    line_total: number | string;
}

interface StatusHistoryEntry {
    id: number;
    from_status_label?: string | null;
    to_status_label: string;
    notes?: string | null;
    created_at: string;
    user?: { name?: string } | null;
}

interface AttachmentRow {
    id: number;
    original_name: string;
    mime_type?: string;
    uploaded_by?: { name?: string } | null;
}

interface OrderDetail {
    id: number;
    folio: string;
    status: string;
    status_label: string;
    type_label: string;
    reason?: string;
    mileage?: number;
    diagnosis?: string | null;
    technical_notes?: string | null;
    assigned_user_id?: number | null;
    subtotal: number | string;
    discount_total: number | string;
    tax_total: number | string;
    total: number | string;
    tax_rate: number | string;
    customer?: { name?: string } | null;
    vehicle?: {
        license_plate?: string;
        brand?: string;
        model?: string;
    } | null;
    assignee?: { name?: string } | null;
}

const props = defineProps<{
    order: OrderDetail;
    items: LineItem[];
    parts: LineItem[];
    statusHistory: StatusHistoryEntry[];
    attachments: AttachmentRow[];
    services: CatalogOption[];
    partsCatalog: CatalogOption[];
    technicians: Array<{ id: number; name: string }>;
    statuses: SelectOption[];
    allowedTransitions: SelectOption[];
    can: {
        changeStatus?: boolean;
        reopen?: boolean;
        diagnose?: boolean;
        addItems?: boolean;
        attach?: boolean;
    };
}>();

const transitionForm = useForm({
    status: '',
    notes: '',
    cancellation_reason: '',
});

const diagnosisForm = useForm({
    diagnosis: props.order.diagnosis ?? '',
    technical_notes: props.order.technical_notes ?? '',
    assigned_user_id: props.order.assigned_user_id ? String(props.order.assigned_user_id) : '',
});

const itemForm = useForm({
    service_catalog_id: '',
    description: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    notes: '',
});

const partForm = useForm({
    part_catalog_id: '',
    description: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    notes: '',
});

const reopenForm = useForm({
    reason: '',
});

const attachmentForm = useForm({
    file: null as File | null,
});

const fileInputKey = ref(0);
const confirmReopen = ref(false);
const activeTab = ref('overview');

const showCancelReason = computed(() => transitionForm.status === 'cancelled');

const tabs: TabItem[] = [
    { key: 'overview', title: 'Resumen', icon: 'mdi-information-outline' },
    { key: 'history', title: 'Historial', icon: 'mdi-history' },
    { key: 'diagnosis', title: 'Diagnóstico', icon: 'mdi-stethoscope' },
    { key: 'lines', title: 'Partidas', icon: 'mdi-format-list-bulleted' },
    { key: 'attachments', title: 'Evidencias', icon: 'mdi-paperclip' },
];

const summaryItems = computed(() => [
    { key: 'customer', label: 'Cliente', value: props.order.customer?.name },
    {
        key: 'vehicle',
        label: 'Unidad',
        value: [
            props.order.vehicle?.license_plate,
            props.order.vehicle?.brand,
            props.order.vehicle?.model,
        ]
            .filter(Boolean)
            .join(' — '),
    },
    { key: 'reason', label: 'Motivo', value: props.order.reason },
    { key: 'mileage', label: 'Kilometraje', value: props.order.mileage },
    { key: 'assignee', label: 'Responsable', value: props.order.assignee?.name || 'Sin asignar' },
]);

const transitionItems = [
    { title: 'Seleccione…', value: '' },
    ...props.allowedTransitions.map((option) => ({
        title: option.label,
        value: option.value,
    })),
];

const technicianItems = [
    { title: 'Sin asignar', value: '' },
    ...props.technicians.map((user) => ({
        title: user.name,
        value: String(user.id),
    })),
];

const serviceItems = [
    { title: 'Manual…', value: '' },
    ...props.services.map((service) => ({
        title: `${service.code} — ${service.description}`,
        value: String(service.id),
    })),
];

const partCatalogItems = [
    { title: 'Manual…', value: '' },
    ...props.partsCatalog.map((part) => ({
        title: `${part.code} — ${part.description}`,
        value: String(part.id),
    })),
];

function onServiceSelect(): void {
    const selected = props.services.find(
        (service) => String(service.id) === String(itemForm.service_catalog_id),
    );

    if (selected) {
        itemForm.description = selected.description;
        itemForm.unit_price = Number(selected.base_price);
    }
}

function onPartSelect(): void {
    const selected = props.partsCatalog.find(
        (part) => String(part.id) === String(partForm.part_catalog_id),
    );

    if (selected) {
        partForm.description = selected.description;
        partForm.unit_price = Number(selected.base_price);
    }
}

function submitTransition(): void {
    transitionForm.post(route('maintenance-orders.transition', props.order.id), {
        preserveScroll: true,
        onSuccess: () => transitionForm.reset('notes', 'cancellation_reason'),
    });
}

function submitDiagnosis(): void {
    diagnosisForm
        .transform((data) => ({
            ...data,
            assigned_user_id: data.assigned_user_id ? Number(data.assigned_user_id) : null,
        }))
        .post(route('maintenance-orders.diagnose', props.order.id), { preserveScroll: true });
}

function submitItem(): void {
    itemForm
        .transform((data) => ({
            ...data,
            service_catalog_id: data.service_catalog_id ? Number(data.service_catalog_id) : null,
            quantity: Number(data.quantity),
            unit_price: Number(data.unit_price),
            discount: Number(data.discount || 0),
        }))
        .post(route('maintenance-orders.items.store', props.order.id), {
            preserveScroll: true,
            onSuccess: () =>
                itemForm.reset(
                    'service_catalog_id',
                    'description',
                    'quantity',
                    'unit_price',
                    'discount',
                    'notes',
                ),
        });
}

function submitPart(): void {
    partForm
        .transform((data) => ({
            ...data,
            part_catalog_id: data.part_catalog_id ? Number(data.part_catalog_id) : null,
            quantity: Number(data.quantity),
            unit_price: Number(data.unit_price),
            discount: Number(data.discount || 0),
        }))
        .post(route('maintenance-orders.parts.store', props.order.id), {
            preserveScroll: true,
            onSuccess: () =>
                partForm.reset(
                    'part_catalog_id',
                    'description',
                    'quantity',
                    'unit_price',
                    'discount',
                    'notes',
                ),
        });
}

function submitReopen(): void {
    reopenForm.post(route('maintenance-orders.reopen', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmReopen.value = false;
            reopenForm.reset();
        },
    });
}

function submitAttachment(): void {
    attachmentForm.post(route('maintenance-orders.attachments.store', props.order.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            attachmentForm.reset('file');
            fileInputKey.value += 1;
        },
    });
}

</script>

<template>
    <AppLayout :title="order.folio">
        <PageHeader :title="order.folio" :subtitle="order.type_label">
            <template #actions>
                <StatusChip :status="order.status" :map="orderStatusMap" />
                <Link :href="route('maintenance-orders.index')" class="text-decoration-none">
                    <v-btn variant="text" prepend-icon="mdi-arrow-left">
                        Volver al listado
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <TabsCard v-model="activeTab" :tabs="tabs" class="mb-4">
            <template #overview>
                <DescriptionList :items="summaryItems" class="mb-6" />

                <v-alert type="info" variant="tonal" density="comfortable" class="mb-4">
                    Subtotal <MoneyText :amount="order.subtotal" />
                    · Desc. <MoneyText :amount="order.discount_total" />
                    · IVA ({{ order.tax_rate }}%) <MoneyText :amount="order.tax_total" />
                    · <strong>Total <MoneyText :amount="order.total" /></strong>
                </v-alert>

                <PanelCard
                    v-if="can.changeStatus && allowedTransitions.length"
                    title="Cambiar estado"
                    class="mb-4"
                >
                    <v-form @submit.prevent="submitTransition">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="transitionForm.status"
                                    :items="transitionItems"
                                    label="Nuevo estado"
                                    :error-messages="transitionForm.errors.status"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="transitionForm.notes"
                                    label="Notas"
                                    :error-messages="transitionForm.errors.notes"
                                />
                            </v-col>
                            <v-col v-if="showCancelReason" cols="12">
                                <v-textarea
                                    v-model="transitionForm.cancellation_reason"
                                    label="Motivo de cancelación"
                                    rows="2"
                                    :error-messages="transitionForm.errors.cancellation_reason"
                                />
                            </v-col>
                        </v-row>
                        <v-btn
                            type="submit"
                            color="primary"
                            variant="flat"
                            :loading="transitionForm.processing"
                            :disabled="!transitionForm.status"
                        >
                            Aplicar transición
                        </v-btn>
                    </v-form>
                </PanelCard>

                <PanelCard
                    v-if="can.reopen && order.status === 'delivered'"
                    title="Reabrir orden"
                >
                    <v-textarea
                        v-model="reopenForm.reason"
                        label="Justificación"
                        rows="2"
                        class="mb-3"
                        :error-messages="reopenForm.errors.reason"
                    />
                    <v-btn
                        color="warning"
                        variant="flat"
                        :loading="reopenForm.processing"
                        @click="confirmReopen = true"
                    >
                        Reabrir a En progreso
                    </v-btn>
                </PanelCard>
            </template>

            <template #history>
                <v-timeline v-if="statusHistory.length" density="compact" side="end">
                    <v-timeline-item
                        v-for="entry in statusHistory"
                        :key="entry.id"
                        dot-color="primary"
                        size="small"
                    >
                        <div class="text-subtitle-2 font-weight-medium">
                            <span v-if="entry.from_status_label">{{ entry.from_status_label }} → </span>
                            {{ entry.to_status_label }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                            {{ entry.user?.name || 'Sistema' }} ·
                            <DateText :value="entry.created_at" time-style="short" />
                        </div>
                        <p v-if="entry.notes" class="text-body-2 mt-1 mb-0">
                            {{ entry.notes }}
                        </p>
                    </v-timeline-item>
                </v-timeline>
                <EmptyState
                    v-else
                    title="Sin historial"
                    message="Aún no hay cambios de estado registrados."
                    icon="mdi-history"
                />
            </template>

            <template #diagnosis>
                <v-form v-if="can.diagnose" @submit.prevent="submitDiagnosis">
                    <v-textarea
                        v-model="diagnosisForm.diagnosis"
                        label="Diagnóstico"
                        rows="4"
                        class="mb-3"
                        :error-messages="diagnosisForm.errors.diagnosis"
                    />
                    <v-textarea
                        v-model="diagnosisForm.technical_notes"
                        label="Notas técnicas"
                        rows="2"
                        class="mb-3"
                    />
                    <v-select
                        v-model="diagnosisForm.assigned_user_id"
                        :items="technicianItems"
                        label="Responsable"
                        class="mb-3"
                    />
                    <v-btn
                        type="submit"
                        color="primary"
                        variant="flat"
                        :loading="diagnosisForm.processing"
                    >
                        Guardar diagnóstico
                    </v-btn>
                </v-form>
                <div v-else-if="order.diagnosis">
                    <p class="text-body-1 text-pre-wrap mb-0">{{ order.diagnosis }}</p>
                    <p v-if="order.technical_notes" class="text-body-2 text-medium-emphasis mt-3 mb-0">
                        {{ order.technical_notes }}
                    </p>
                </div>
                <EmptyState
                    v-else
                    title="Sin diagnóstico"
                    message="Todavía no se ha capturado un diagnóstico para esta orden."
                    icon="mdi-stethoscope"
                />
            </template>

            <template #lines>
                <div class="mb-8">
                    <div class="d-flex align-center justify-space-between mb-3">
                        <h3 class="text-subtitle-1 font-weight-bold mb-0">Servicios</h3>
                    </div>
                    <v-table density="comfortable" class="mb-4">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cant.</th>
                                <th>P. unit.</th>
                                <th>Desc.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id">
                                <td>{{ item.description }}</td>
                                <td>{{ item.quantity }}</td>
                                <td><MoneyText :amount="item.unit_price" /></td>
                                <td><MoneyText :amount="item.discount" /></td>
                                <td class="text-end"><MoneyText :amount="item.line_total" /></td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="5" class="text-medium-emphasis text-center py-6">
                                    Sin servicios.
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-form
                        v-if="can.addItems"
                        class="border-t pt-4"
                        @submit.prevent="submitItem"
                    >
                        <v-row dense>
                            <v-col cols="12" md="4">
                                <v-select
                                    v-model="itemForm.service_catalog_id"
                                    :items="serviceItems"
                                    label="Catálogo"
                                    @update:model-value="onServiceSelect"
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    v-model="itemForm.description"
                                    label="Descripción"
                                    :error-messages="itemForm.errors.description"
                                />
                            </v-col>
                            <v-col cols="6" md="2">
                                <v-text-field
                                    v-model.number="itemForm.quantity"
                                    type="number"
                                    step="0.01"
                                    label="Cant."
                                />
                            </v-col>
                            <v-col cols="6" md="2">
                                <v-text-field
                                    v-model.number="itemForm.unit_price"
                                    type="number"
                                    step="0.01"
                                    label="P. unit."
                                />
                            </v-col>
                            <v-col cols="12">
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    variant="flat"
                                    :loading="itemForm.processing"
                                >
                                    Agregar servicio
                                </v-btn>
                                <div
                                    v-if="(itemForm.errors as Record<string, string>).order"
                                    class="text-error text-caption mt-2"
                                >
                                    {{ (itemForm.errors as Record<string, string>).order }}
                                </div>
                            </v-col>
                        </v-row>
                    </v-form>
                </div>

                <div>
                    <h3 class="text-subtitle-1 font-weight-bold mb-3">Refacciones</h3>
                    <v-table density="comfortable" class="mb-4">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cant.</th>
                                <th>P. unit.</th>
                                <th>Desc.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="part in parts" :key="part.id">
                                <td>{{ part.description }}</td>
                                <td>{{ part.quantity }}</td>
                                <td><MoneyText :amount="part.unit_price" /></td>
                                <td><MoneyText :amount="part.discount" /></td>
                                <td class="text-end"><MoneyText :amount="part.line_total" /></td>
                            </tr>
                            <tr v-if="!parts.length">
                                <td colspan="5" class="text-medium-emphasis text-center py-6">
                                    Sin refacciones.
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-form
                        v-if="can.addItems"
                        class="border-t pt-4"
                        @submit.prevent="submitPart"
                    >
                        <v-row dense>
                            <v-col cols="12" md="4">
                                <v-select
                                    v-model="partForm.part_catalog_id"
                                    :items="partCatalogItems"
                                    label="Catálogo"
                                    @update:model-value="onPartSelect"
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    v-model="partForm.description"
                                    label="Descripción"
                                />
                            </v-col>
                            <v-col cols="6" md="2">
                                <v-text-field
                                    v-model.number="partForm.quantity"
                                    type="number"
                                    step="0.01"
                                    label="Cant."
                                />
                            </v-col>
                            <v-col cols="6" md="2">
                                <v-text-field
                                    v-model.number="partForm.unit_price"
                                    type="number"
                                    step="0.01"
                                    label="P. unit."
                                />
                            </v-col>
                            <v-col cols="12">
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    variant="flat"
                                    :loading="partForm.processing"
                                >
                                    Agregar refacción
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </div>
            </template>

            <template #attachments>
                <v-list v-if="attachments.length" lines="two" class="bg-transparent mb-4">
                    <v-list-item
                        v-for="file in attachments"
                        :key="file.id"
                        :title="file.original_name"
                        :subtitle="file.mime_type"
                    >
                        <template #append>
                            <span class="text-caption text-medium-emphasis">
                                {{ file.uploaded_by?.name }}
                            </span>
                        </template>
                    </v-list-item>
                </v-list>
                <EmptyState
                    v-else
                    title="Sin evidencias"
                    message="Aún no se han cargado archivos para esta orden."
                    icon="mdi-paperclip"
                    class="mb-4"
                />

                <v-form v-if="can.attach" @submit.prevent="submitAttachment">
                    <v-file-input
                        :key="fileInputKey"
                        label="Archivo de evidencia"
                        prepend-icon=""
                        prepend-inner-icon="mdi-paperclip"
                        show-size
                        class="mb-3"
                        :error-messages="attachmentForm.errors.file"
                        @update:model-value="(files) => {
                            const value = Array.isArray(files) ? files[0] : files;
                            attachmentForm.file = value ?? null;
                        }"
                    />
                    <v-btn
                        type="submit"
                        color="primary"
                        variant="flat"
                        :loading="attachmentForm.processing"
                        :disabled="!attachmentForm.file"
                    >
                        Subir evidencia
                    </v-btn>
                </v-form>
            </template>
        </TabsCard>

        <ConfirmDialog
            v-model="confirmReopen"
            title="Reabrir orden"
            message="La orden volverá a estado En progreso. ¿Continuar?"
            confirm-text="Reabrir"
            confirm-color="warning"
            :loading="reopenForm.processing"
            @confirm="submitReopen"
        />
    </AppLayout>
</template>
