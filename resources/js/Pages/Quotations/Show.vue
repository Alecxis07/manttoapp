<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import DateText from '@/Components/Ui/DateText.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import FormDialog from '@/Components/Ui/FormDialog.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import TabsCard from '@/Components/Ui/TabsCard.vue';
import { quotationStatusMap } from '@/Components/Ui/statusMaps';
import type { TabItem } from '@/Components/Ui/TabsCard.vue';

interface QuotationItem {
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
    user?: { name?: string } | null;
}

interface VersionRow {
    id: number;
    folio: string;
    version: number;
    status: string;
    status_label: string;
    total: number | string;
    is_current?: boolean;
}

interface QuotationDetail {
    id: number;
    folio: string;
    version: number;
    status: string;
    status_label: string;
    is_editable?: boolean;
    can_be_accepted?: boolean;
    maintenance_order_id?: number | null;
    valid_until?: string | null;
    issued_at?: string | null;
    commercial_terms?: string | null;
    subtotal: number | string;
    discount_total: number | string;
    tax_total: number | string;
    total: number | string;
    tax_rate: number | string;
    customer?: { name?: string } | null;
    vehicle?: { license_plate?: string } | null;
    items: QuotationItem[];
    status_history: StatusHistoryRow[];
}

const props = defineProps<{
    quotation: QuotationDetail;
    versions: VersionRow[];
    can?: {
        exportPdf?: boolean;
        update?: boolean;
        send?: boolean;
        version?: boolean;
        accept?: boolean;
        reject?: boolean;
        convert?: boolean;
        cancel?: boolean;
    };
}>();

const activeTab = ref('items');
const confirmVersion = ref(false);
const confirmConvert = ref(false);
const rejectDialog = ref(false);
const cancelDialog = ref(false);
const actionReason = ref('');
const actionLoading = ref(false);

const tabs: TabItem[] = [
    { key: 'items', title: 'Partidas', icon: 'mdi-format-list-bulleted' },
    { key: 'versions', title: 'Versiones', icon: 'mdi-source-branch' },
    { key: 'history', title: 'Historial', icon: 'mdi-history' },
];

const summaryItems = computed(() => [
    { key: 'customer', label: 'Cliente', value: props.quotation.customer?.name },
    { key: 'vehicle', label: 'Unidad', value: props.quotation.vehicle?.license_plate },
    { key: 'valid_until', label: 'Vigencia', value: props.quotation.valid_until || '—' },
    { key: 'issued_at', label: 'Emitida', value: props.quotation.issued_at || '—' },
    {
        key: 'commercial_terms',
        label: 'Condiciones',
        value: props.quotation.commercial_terms || '—',
    },
]);

function send(): void {
    actionLoading.value = true;
    router.post(
        route('quotations.send', props.quotation.id),
        {},
        { onFinish: () => { actionLoading.value = false; } },
    );
}

function accept(): void {
    actionLoading.value = true;
    router.post(
        route('quotations.accept', props.quotation.id),
        {},
        { onFinish: () => { actionLoading.value = false; } },
    );
}

function createVersion(): void {
    actionLoading.value = true;
    router.post(
        route('quotations.version', props.quotation.id),
        {},
        {
            onFinish: () => {
                actionLoading.value = false;
                confirmVersion.value = false;
            },
        },
    );
}

function convert(): void {
    actionLoading.value = true;
    router.post(
        route('quotations.convert', props.quotation.id),
        {},
        {
            onFinish: () => {
                actionLoading.value = false;
                confirmConvert.value = false;
            },
        },
    );
}

function reject(): void {
    actionLoading.value = true;
    router.post(
        route('quotations.reject', props.quotation.id),
        { reason: actionReason.value },
        {
            onFinish: () => {
                actionLoading.value = false;
                rejectDialog.value = false;
                actionReason.value = '';
            },
        },
    );
}

function cancel(): void {
    actionLoading.value = true;
    router.post(
        route('quotations.cancel', props.quotation.id),
        { reason: actionReason.value },
        {
            onFinish: () => {
                actionLoading.value = false;
                cancelDialog.value = false;
                actionReason.value = '';
            },
        },
    );
}

const canShowVersion = computed(
    () =>
        !!props.can?.version &&
        props.quotation.status !== 'draft' &&
        props.quotation.status !== 'accepted' &&
        props.quotation.status !== 'cancelled',
);

const canCancel = computed(
    () =>
        !!props.can?.cancel &&
        props.quotation.status !== 'cancelled' &&
        props.quotation.status !== 'accepted',
);
</script>

<template>
    <AppLayout :title="quotation.folio">
        <PageHeader :title="quotation.folio" :subtitle="`Versión ${quotation.version}`">
            <template #actions>
                <StatusChip :status="quotation.status" :map="quotationStatusMap" />
                <v-btn
                    v-if="can?.exportPdf"
                    :href="route('quotations.pdf', quotation.id)"
                    target="_blank"
                    rel="noopener"
                    variant="tonal"
                    prepend-icon="mdi-file-pdf-box"
                >
                    PDF
                </v-btn>
                <Link
                    v-if="can?.update && quotation.is_editable"
                    :href="route('quotations.edit', quotation.id)"
                    class="text-decoration-none"
                >
                    <v-btn variant="tonal" prepend-icon="mdi-pencil">
                        Editar
                    </v-btn>
                </Link>
                <v-btn
                    v-if="can?.send && quotation.status === 'draft'"
                    color="primary"
                    variant="flat"
                    :loading="actionLoading"
                    @click="send"
                >
                    Enviar
                </v-btn>
                <v-btn
                    v-if="canShowVersion"
                    variant="tonal"
                    @click="confirmVersion = true"
                >
                    Nueva versión
                </v-btn>
                <v-btn
                    v-if="can?.accept && quotation.can_be_accepted"
                    color="success"
                    variant="flat"
                    :loading="actionLoading"
                    @click="accept"
                >
                    Aceptar
                </v-btn>
                <v-btn
                    v-if="can?.reject && quotation.status === 'sent'"
                    color="error"
                    variant="tonal"
                    @click="rejectDialog = true"
                >
                    Rechazar
                </v-btn>
                <v-btn
                    v-if="can?.convert && quotation.status === 'accepted' && !quotation.maintenance_order_id"
                    color="primary"
                    variant="flat"
                    @click="confirmConvert = true"
                >
                    Convertir a orden
                </v-btn>
                <v-btn
                    v-if="canCancel"
                    color="error"
                    variant="text"
                    @click="cancelDialog = true"
                >
                    Cancelar
                </v-btn>
            </template>
        </PageHeader>

        <PanelCard class="mb-4">
            <DescriptionList :items="summaryItems">
                <template #valid_until="{ item }">
                    <DateText v-if="quotation.valid_until" :value="quotation.valid_until" />
                    <span v-else>{{ item.value }}</span>
                </template>
                <template #issued_at="{ item }">
                    <DateText
                        v-if="quotation.issued_at"
                        :value="quotation.issued_at"
                        time-style="short"
                    />
                    <span v-else>{{ item.value }}</span>
                </template>
            </DescriptionList>
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
                        <tr v-for="item in quotation.items" :key="item.id">
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
                    <div>Subtotal: <MoneyText :amount="quotation.subtotal" /></div>
                    <div>Descuento: <MoneyText :amount="quotation.discount_total" /></div>
                    <div>
                        IVA ({{ quotation.tax_rate }}%):
                        <MoneyText :amount="quotation.tax_total" />
                    </div>
                    <div class="text-subtitle-1 font-weight-bold mt-1">
                        Total: <MoneyText :amount="quotation.total" />
                    </div>
                </div>
            </template>

            <template #versions>
                <v-list class="bg-transparent pa-0">
                    <v-list-item
                        v-for="versionRow in versions"
                        :key="versionRow.id"
                        :title="`${versionRow.folio} v${versionRow.version}`"
                        :subtitle="versionRow.status_label"
                        :active="versionRow.is_current"
                    >
                        <template #prepend>
                            <StatusChip :status="versionRow.status" :map="quotationStatusMap" />
                        </template>
                        <template #append>
                            <div class="d-flex align-center ga-2">
                                <MoneyText :amount="versionRow.total" />
                                <Link
                                    :href="route('quotations.show', versionRow.id)"
                                    class="text-decoration-none"
                                >
                                    <v-btn size="small" variant="text" color="primary">
                                        Ver
                                    </v-btn>
                                </Link>
                            </div>
                        </template>
                    </v-list-item>
                </v-list>
            </template>

            <template #history>
                <v-timeline density="compact" side="end">
                    <v-timeline-item
                        v-for="(row, index) in quotation.status_history"
                        :key="index"
                        dot-color="primary"
                        size="small"
                    >
                        <div class="text-subtitle-2">{{ row.to_status_label }}</div>
                        <div class="text-caption text-medium-emphasis">
                            <DateText :value="row.created_at" time-style="short" />
                            <span v-if="row.user"> · {{ row.user.name }}</span>
                        </div>
                    </v-timeline-item>
                </v-timeline>
            </template>
        </TabsCard>

        <ConfirmDialog
            v-model="confirmVersion"
            title="Nueva versión"
            message="¿Crear una nueva versión a partir de esta cotización?"
            confirm-text="Crear versión"
            confirm-color="primary"
            :loading="actionLoading"
            @confirm="createVersion"
        />

        <ConfirmDialog
            v-model="confirmConvert"
            title="Convertir a orden"
            message="¿Convertir esta cotización aceptada en una orden de mantenimiento?"
            confirm-text="Convertir"
            confirm-color="primary"
            :loading="actionLoading"
            @confirm="convert"
        />

        <FormDialog
            v-model="rejectDialog"
            title="Rechazar cotización"
            save-text="Rechazar"
            :loading="actionLoading"
            @save="reject"
        >
            <v-textarea
                v-model="actionReason"
                label="Motivo del rechazo (opcional)"
                rows="3"
            />
        </FormDialog>

        <FormDialog
            v-model="cancelDialog"
            title="Cancelar cotización"
            save-text="Cancelar cotización"
            :loading="actionLoading"
            @save="cancel"
        >
            <v-textarea
                v-model="actionReason"
                label="Motivo de cancelación (opcional)"
                rows="3"
            />
        </FormDialog>
    </AppLayout>
</template>
