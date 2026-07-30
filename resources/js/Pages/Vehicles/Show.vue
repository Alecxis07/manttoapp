<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import DateText from '@/Components/Ui/DateText.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import TabsCard from '@/Components/Ui/TabsCard.vue';
import type { TabItem } from '@/Components/Ui/TabsCard.vue';
import { vehicleStatusMap } from '@/Components/Ui/statusMaps';

interface SelectOption {
    value: string;
    label: string;
}

interface Attachment {
    id: number;
    original_name: string;
    uploaded_by?: { name?: string } | null;
}

interface TimelineEntry {
    id: string | number;
    type_label: string;
    status_label?: string | null;
    title: string;
    subtitle?: string | null;
    url?: string | null;
    occurred_at?: string | null;
    amount?: number | string | null;
}

interface AuditEntry {
    id: number;
    action: string;
    created_at: string;
    user?: { name?: string } | null;
    properties?: unknown;
}

const props = defineProps<{
    customer: {
        id: number;
        name: string;
        trade_name?: string | null;
        status: string;
    };
    vehicle: {
        id: number;
        license_plate: string;
        license_plate_normalized?: string;
        vin?: string | null;
        economic_number?: string | null;
        brand: string;
        model: string;
        year: number;
        engine_type?: string | null;
        current_mileage?: number | null;
        status: string;
        status_label: string;
        status_notes?: string | null;
        is_in_use?: boolean;
        vehicle_type?: { name?: string } | null;
        created_by?: { name?: string } | null;
    };
    attachments: Attachment[];
    auditHistory: AuditEntry[];
    timeline: TimelineEntry[];
    historyFilters: {
        from?: string;
        to?: string;
        type?: string;
    };
    eventTypes: SelectOption[];
    can: {
        update?: boolean;
        delete?: boolean;
        exportHistory?: boolean;
    };
}>();

const activeTab = ref('identification');
const confirmOpen = ref(false);
const processing = ref(false);
const inUseAlert = ref(false);

const tabs: TabItem[] = [
    { key: 'identification', title: 'Identificación', icon: 'mdi-card-account-details-outline' },
    { key: 'owner', title: 'Propietario', icon: 'mdi-account-outline' },
    { key: 'history', title: 'Historial', icon: 'mdi-timeline-outline' },
];

const filters = reactive({
    from: props.historyFilters?.from || '',
    to: props.historyFilters?.to || '',
    type: props.historyFilters?.type || '',
});

function askDestroy(): void {
    if (props.vehicle.is_in_use) {
        inUseAlert.value = true;
        return;
    }

    confirmOpen.value = true;
}

function confirmDestroy(): void {
    processing.value = true;
    router.delete(route('customers.vehicles.destroy', [props.customer.id, props.vehicle.id]), {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
        },
    });
}

function applyHistoryFilters(): void {
    const query: Record<string, string> = {};

    if (filters.from) {
        query.from = filters.from;
    }

    if (filters.to) {
        query.to = filters.to;
    }

    if (filters.type) {
        query.type = filters.type;
    }

    router.get(
        route('customers.vehicles.show', [props.customer.id, props.vehicle.id]),
        query,
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                activeTab.value = 'history';
            },
        },
    );
}

function clearHistoryFilters(): void {
    filters.from = '';
    filters.to = '';
    filters.type = '';
    applyHistoryFilters();
}

function pdfUrl(): string {
    const query: Record<string, string> = {};

    if (filters.from) {
        query.from = filters.from;
    }

    if (filters.to) {
        query.to = filters.to;
    }

    if (filters.type) {
        query.type = filters.type;
    }

    return route('customers.vehicles.history.pdf', {
        customer: props.customer.id,
        vehicle: props.vehicle.id,
        ...query,
    });
}
</script>

<template>
    <AppLayout :title="`Unidad ${vehicle.license_plate}`">
        <PageHeader
            :title="vehicle.license_plate"
            :subtitle="`${vehicle.brand} ${vehicle.model} · ${vehicle.status_label}`"
            :breadcrumbs="[
                { title: 'Unidades', href: route('vehicles.index') },
                { title: vehicle.license_plate, disabled: true },
            ]"
        >
            <template #actions>
                <Link :href="route('vehicles.index')">
                    <v-btn variant="text">Volver</v-btn>
                </Link>
                <Link
                    v-if="can?.update"
                    :href="route('customers.vehicles.edit', [customer.id, vehicle.id])"
                >
                    <v-btn color="primary" variant="flat">Editar</v-btn>
                </Link>
                <v-btn
                    v-if="can?.delete && !vehicle.is_in_use"
                    variant="tonal"
                    color="error"
                    @click="askDestroy"
                >
                    Eliminar
                </v-btn>
            </template>
        </PageHeader>

        <v-alert
            v-model="inUseAlert"
            type="warning"
            variant="tonal"
            closable
            class="mb-4"
        >
            Esta unidad tiene órdenes asociadas y no puede eliminarse.
        </v-alert>

        <TabsCard v-model="activeTab" :tabs="tabs">
            <template #identification>
                <DescriptionList
                    :items="[
                        { label: 'Placas', value: vehicle.license_plate, key: 'plates' },
                        { label: 'VIN', value: vehicle.vin || '—', key: 'vin' },
                        { label: 'Número económico', value: vehicle.economic_number || '—', key: 'economic' },
                        { label: 'Tipo', value: vehicle.vehicle_type?.name || '—', key: 'type' },
                        {
                            label: 'Marca / Modelo / Año',
                            value: `${vehicle.brand} ${vehicle.model} (${vehicle.year})`,
                            key: 'brand',
                        },
                        { label: 'Motor', value: vehicle.engine_type || '—', key: 'engine' },
                        {
                            label: 'Kilometraje',
                            value: `${vehicle.current_mileage?.toLocaleString() ?? '—'} km`,
                            key: 'mileage',
                        },
                        { label: 'Estatus', value: vehicle.status_label, key: 'status' },
                    ]"
                >
                    <template #plates>
                        <div class="font-weight-medium">{{ vehicle.license_plate }}</div>
                        <div class="text-caption text-medium-emphasis">
                            Norm: {{ vehicle.license_plate_normalized }}
                        </div>
                    </template>
                    <template #status>
                        <StatusChip :status="vehicle.status" :map="vehicleStatusMap" />
                        <div v-if="vehicle.status_notes" class="text-body-2 text-medium-emphasis mt-1">
                            {{ vehicle.status_notes }}
                        </div>
                    </template>
                </DescriptionList>

                <v-divider class="my-4" />
                <h3 class="text-subtitle-1 font-weight-medium mb-2">Adjuntos</h3>
                <v-list v-if="attachments?.length" density="compact">
                    <v-list-item
                        v-for="file in attachments"
                        :key="file.id"
                        :title="file.original_name"
                        :subtitle="file.uploaded_by?.name || 'sistema'"
                        prepend-icon="mdi-paperclip"
                    />
                </v-list>
                <p v-else class="text-body-2 text-medium-emphasis mb-0">Sin adjuntos.</p>
            </template>

            <template #owner>
                <DescriptionList
                    :items="[
                        { label: 'Cliente', value: customer.name, key: 'customer' },
                        { label: 'Nombre comercial', value: customer.trade_name || '—', key: 'trade' },
                        { label: 'Estatus del cliente', value: customer.status, key: 'customer_status' },
                        { label: 'Registrado por', value: vehicle.created_by?.name || '—', key: 'created_by' },
                    ]"
                >
                    <template #customer>
                        <Link
                            :href="route('customers.show', customer.id)"
                            class="text-primary text-decoration-none font-weight-medium"
                        >
                            {{ customer.name }}
                        </Link>
                    </template>
                </DescriptionList>
            </template>

            <template #history>
                <div class="d-flex flex-wrap align-end justify-space-between ga-3 mb-4">
                    <div>
                        <h3 class="text-subtitle-1 font-weight-medium mb-1">Expediente consolidado</h3>
                        <p class="text-body-2 text-medium-emphasis mb-0">
                            Órdenes, cotizaciones, evidencias y facturación, orden descendente.
                        </p>
                    </div>
                    <v-btn
                        v-if="can?.exportHistory"
                        :href="pdfUrl()"
                        variant="tonal"
                        prepend-icon="mdi-file-pdf-box"
                    >
                        Exportar PDF
                    </v-btn>
                </div>

                <v-row class="mb-4" dense>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.from" type="date" label="Desde" hide-details />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.to" type="date" label="Hasta" hide-details />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select
                            v-model="filters.type"
                            :items="[
                                { value: '', title: 'Todos' },
                                ...eventTypes.map((t) => ({ value: t.value, title: t.label })),
                            ]"
                            label="Tipo"
                            hide-details
                        />
                    </v-col>
                    <v-col cols="12" md="3" class="d-flex ga-2 align-center">
                        <v-btn color="primary" variant="flat" @click="applyHistoryFilters">
                            Filtrar
                        </v-btn>
                        <v-btn variant="text" @click="clearHistoryFilters">
                            Limpiar
                        </v-btn>
                    </v-col>
                </v-row>

                <div v-if="timeline?.length" class="d-flex flex-column ga-3 mb-6">
                    <v-card
                        v-for="entry in timeline"
                        :key="entry.id"
                        variant="outlined"
                        class="pa-4"
                    >
                        <div class="d-flex flex-wrap justify-space-between ga-2">
                            <div>
                                <div class="d-flex flex-wrap align-center ga-2 mb-1">
                                    <v-chip size="small" color="primary" variant="tonal" label>
                                        {{ entry.type_label }}
                                    </v-chip>
                                    <span
                                        v-if="entry.status_label"
                                        class="text-caption text-medium-emphasis"
                                    >
                                        {{ entry.status_label }}
                                    </span>
                                </div>
                                <a
                                    v-if="entry.url"
                                    :href="entry.url"
                                    class="text-primary text-decoration-none font-weight-medium"
                                >
                                    {{ entry.title }}
                                </a>
                                <div v-else class="font-weight-medium">{{ entry.title }}</div>
                                <p v-if="entry.subtitle" class="text-body-2 text-medium-emphasis mb-0 mt-1">
                                    {{ entry.subtitle }}
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="text-body-2 text-medium-emphasis">
                                    <DateText :value="entry.occurred_at" time-style="short" />
                                </div>
                                <div v-if="entry.amount !== null && entry.amount !== undefined">
                                    <MoneyText :amount="entry.amount" />
                                </div>
                            </div>
                        </div>
                    </v-card>
                </div>
                <EmptyState
                    v-else
                    title="Sin eventos"
                    message="No hay eventos en el expediente para los filtros seleccionados."
                    icon="mdi-timeline-outline"
                />

                <h3 class="text-subtitle-1 font-weight-medium mb-2">Auditoría de cambios</h3>
                <div
                    v-if="auditHistory?.length"
                    class="d-flex flex-column ga-2"
                    style="max-height: 24rem; overflow-y: auto"
                >
                    <v-card
                        v-for="entry in auditHistory"
                        :key="entry.id"
                        variant="outlined"
                        class="pa-3"
                    >
                        <div class="d-flex justify-space-between ga-2">
                            <span class="font-weight-medium">{{ entry.action }}</span>
                            <span class="text-caption text-medium-emphasis">{{ entry.created_at }}</span>
                        </div>
                        <div class="text-body-2 text-medium-emphasis">
                            {{ entry.user?.name || 'Sistema' }}
                        </div>
                        <pre
                            v-if="entry.properties"
                            class="text-caption text-medium-emphasis mt-2 mb-0"
                            style="white-space: pre-wrap"
                        >{{ entry.properties }}</pre>
                    </v-card>
                </div>
                <p v-else class="text-body-2 text-medium-emphasis mb-0">Sin registros de auditoría.</p>
            </template>
        </TabsCard>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Eliminar unidad"
            :message="`¿Eliminar la unidad ${vehicle.license_plate}?`"
            confirm-text="Eliminar"
            :loading="processing"
            @confirm="confirmDestroy"
        />
    </AppLayout>
</template>
