<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DateText from '@/Components/Ui/DateText.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import FormDialog from '@/Components/Ui/FormDialog.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import TabsCard from '@/Components/Ui/TabsCard.vue';
import { billingStatusMap } from '@/Components/Ui/statusMaps';
import type { TabItem } from '@/Components/Ui/TabsCard.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface BillingItem {
    id: number;
    item_type_label: string;
    code?: string;
    description: string;
    quantity: number | string;
    unit_price: number | string;
    discount: number | string;
    line_total: number | string;
}

interface StatusHistoryRow {
    created_at: string;
    to_status_label: string;
    notes?: string | null;
    user?: { name?: string } | null;
}

interface FiscalSnapshot {
    legal_name?: string | null;
    rfc?: string | null;
    tax_regime_code?: string | null;
    cfdi_use_code?: string | null;
    postal_code?: string | null;
    email?: string | null;
}

interface BillingDetail {
    id: number;
    folio: string;
    status: string;
    status_label: string;
    is_editable?: boolean;
    has_complete_fiscal_data?: boolean;
    invoice_reference?: string | null;
    payment_method_code?: string | null;
    payment_form_code?: string | null;
    notes?: string | null;
    subtotal: number | string;
    discount_total: number | string;
    tax_total: number | string;
    total: number | string;
    tax_rate: number | string;
    customer?: { name?: string } | null;
    vehicle?: { license_plate?: string } | null;
    maintenance_order?: { folio?: string } | null;
    quotation?: { folio?: string; version?: number } | null;
    fiscal_profile_snapshot?: FiscalSnapshot | null;
    items: BillingItem[];
    status_history: StatusHistoryRow[];
    allowed_transitions?: SelectOption[];
}

const props = defineProps<{
    billingRequest: BillingDetail;
    can?: {
        update?: boolean;
        submit?: boolean;
        process?: boolean;
    };
}>();

const activeTab = ref('items');
const transitionDialog = ref(false);
const processDialog = ref(false);
const pendingStatus = ref('');
const actionNotes = ref('');
const invoiceReference = ref('');
const actionLoading = ref(false);

const tabs: TabItem[] = [
    { key: 'items', title: 'Conceptos', icon: 'mdi-format-list-bulleted' },
    { key: 'fiscal', title: 'Fiscal', icon: 'mdi-domain' },
    { key: 'history', title: 'Historial', icon: 'mdi-history' },
];

const originLabel = computed(() => {
    if (props.billingRequest.maintenance_order) {
        return `Orden ${props.billingRequest.maintenance_order.folio}`;
    }

    if (props.billingRequest.quotation) {
        return `Cotización ${props.billingRequest.quotation.folio} v${props.billingRequest.quotation.version}`;
    }

    return '—';
});

const summaryItems = computed(() => [
    { key: 'customer', label: 'Cliente', value: props.billingRequest.customer?.name },
    {
        key: 'vehicle',
        label: 'Unidad',
        value: props.billingRequest.vehicle?.license_plate || '—',
    },
    { key: 'origin', label: 'Origen', value: originLabel.value },
    {
        key: 'invoice',
        label: 'Referencia factura',
        value: props.billingRequest.invoice_reference || '—',
    },
    {
        key: 'payment_method',
        label: 'Método pago',
        value: props.billingRequest.payment_method_code || '—',
    },
    {
        key: 'payment_form',
        label: 'Forma pago',
        value: props.billingRequest.payment_form_code || '—',
    },
    { key: 'notes', label: 'Observaciones', value: props.billingRequest.notes || '—' },
]);

const fiscalItems = computed(() => [
    {
        key: 'legal_name',
        label: 'Razón social',
        value: props.billingRequest.fiscal_profile_snapshot?.legal_name || '—',
    },
    {
        key: 'rfc',
        label: 'RFC',
        value: props.billingRequest.fiscal_profile_snapshot?.rfc || '—',
    },
    {
        key: 'regime',
        label: 'Régimen',
        value: props.billingRequest.fiscal_profile_snapshot?.tax_regime_code || '—',
    },
    {
        key: 'cfdi',
        label: 'Uso CFDI',
        value: props.billingRequest.fiscal_profile_snapshot?.cfdi_use_code || '—',
    },
    {
        key: 'postal',
        label: 'C.P.',
        value: props.billingRequest.fiscal_profile_snapshot?.postal_code || '—',
    },
    {
        key: 'email',
        label: 'Email',
        value: props.billingRequest.fiscal_profile_snapshot?.email || '—',
    },
]);

function submitForReview(): void {
    actionLoading.value = true;
    router.post(
        route('billing-requests.submit', props.billingRequest.id),
        {},
        { onFinish: () => { actionLoading.value = false; } },
    );
}

function openTransition(status: string): void {
    pendingStatus.value = status;
    actionNotes.value = '';
    transitionDialog.value = true;
}

function confirmTransition(): void {
    actionLoading.value = true;
    router.post(
        route('billing-requests.transition', props.billingRequest.id),
        {
            status: pendingStatus.value,
            notes: actionNotes.value,
        },
        {
            onFinish: () => {
                actionLoading.value = false;
                transitionDialog.value = false;
                pendingStatus.value = '';
                actionNotes.value = '';
            },
        },
    );
}

function confirmProcess(): void {
    if (!invoiceReference.value.trim()) {
        return;
    }

    actionLoading.value = true;
    router.post(
        route('billing-requests.process', props.billingRequest.id),
        { invoice_reference: invoiceReference.value },
        {
            onFinish: () => {
                actionLoading.value = false;
                processDialog.value = false;
                invoiceReference.value = '';
            },
        },
    );
}

const canSubmit = computed(
    () =>
        !!props.can?.submit &&
        (props.billingRequest.status === 'draft' || props.billingRequest.status === 'incomplete'),
);
</script>

<template>
    <AppLayout :title="billingRequest.folio">
        <PageHeader :title="billingRequest.folio">
            <template #actions>
                <StatusChip :status="billingRequest.status" :map="billingStatusMap" />
                <Link
                    v-if="can?.update && billingRequest.is_editable"
                    :href="route('billing-requests.edit', billingRequest.id)"
                    class="text-decoration-none"
                >
                    <v-btn variant="tonal" prepend-icon="mdi-pencil">
                        Editar
                    </v-btn>
                </Link>
                <v-btn
                    v-if="canSubmit"
                    color="primary"
                    variant="flat"
                    :loading="actionLoading"
                    @click="submitForReview"
                >
                    Enviar a revisión
                </v-btn>
                <v-btn
                    v-for="transitionOption in billingRequest.allowed_transitions"
                    :key="transitionOption.value"
                    variant="tonal"
                    @click="openTransition(transitionOption.value)"
                >
                    {{ transitionOption.label }}
                </v-btn>
                <v-btn
                    v-if="can?.process && billingRequest.status === 'approved'"
                    color="success"
                    variant="flat"
                    @click="processDialog = true"
                >
                    Marcar procesada
                </v-btn>
            </template>
        </PageHeader>

        <PanelCard class="mb-4">
            <DescriptionList :items="summaryItems" />
        </PanelCard>

        <TabsCard v-model="activeTab" :tabs="tabs">
            <template #items>
                <v-table density="comfortable">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th class="text-end">Cant.</th>
                            <th class="text-end">P. unit.</th>
                            <th class="text-end">Desc.</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in billingRequest.items" :key="item.id">
                            <td>{{ item.item_type_label }}</td>
                            <td>{{ item.code }} — {{ item.description }}</td>
                            <td class="text-end">{{ item.quantity }}</td>
                            <td class="text-end"><MoneyText :amount="item.unit_price" /></td>
                            <td class="text-end"><MoneyText :amount="item.discount" /></td>
                            <td class="text-end"><MoneyText :amount="item.line_total" /></td>
                        </tr>
                    </tbody>
                </v-table>
                <div class="text-end text-body-2 mt-4">
                    <div>Subtotal: <MoneyText :amount="billingRequest.subtotal" /></div>
                    <div>Descuento: <MoneyText :amount="billingRequest.discount_total" /></div>
                    <div>
                        IVA ({{ billingRequest.tax_rate }}%):
                        <MoneyText :amount="billingRequest.tax_total" />
                    </div>
                    <div class="text-subtitle-1 font-weight-bold mt-1">
                        Total: <MoneyText :amount="billingRequest.total" />
                    </div>
                </div>
            </template>

            <template #fiscal>
                <DescriptionList :items="fiscalItems" />
                <v-alert
                    v-if="!billingRequest.has_complete_fiscal_data"
                    type="warning"
                    variant="tonal"
                    class="mt-4"
                    density="comfortable"
                >
                    Datos fiscales incompletos — no se puede enviar a revisión.
                </v-alert>
            </template>

            <template #history>
                <v-timeline density="compact" side="end">
                    <v-timeline-item
                        v-for="(row, index) in billingRequest.status_history"
                        :key="index"
                        dot-color="primary"
                        size="small"
                    >
                        <div class="text-subtitle-2">{{ row.to_status_label }}</div>
                        <div class="text-caption text-medium-emphasis">
                            <DateText :value="row.created_at" time-style="short" />
                            <span v-if="row.user"> · {{ row.user.name }}</span>
                        </div>
                        <p v-if="row.notes" class="text-body-2 mt-1 mb-0">
                            {{ row.notes }}
                        </p>
                    </v-timeline-item>
                </v-timeline>
            </template>
        </TabsCard>

        <FormDialog
            v-model="transitionDialog"
            title="Cambiar estado"
            save-text="Aplicar"
            :loading="actionLoading"
            @save="confirmTransition"
        >
            <v-textarea
                v-model="actionNotes"
                label="Observaciones (opcional)"
                rows="3"
            />
        </FormDialog>

        <FormDialog
            v-model="processDialog"
            title="Marcar procesada"
            save-text="Procesar"
            :loading="actionLoading"
            @save="confirmProcess"
        >
            <v-text-field
                v-model="invoiceReference"
                label="Referencia de factura emitida"
                required
            />
        </FormDialog>
    </AppLayout>
</template>
