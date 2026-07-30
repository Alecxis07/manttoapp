<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import DescriptionList from '@/Components/Ui/DescriptionList.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';
import StatusChip from '@/Components/Ui/StatusChip.vue';
import { customerStatusMap, vehicleStatusMap } from '@/Components/Ui/statusMaps';

interface FiscalProfile {
    id: number;
    legal_name: string;
    rfc: string;
    tax_regime_code: string;
    cfdi_use_code: string;
    postal_code: string;
    email?: string | null;
    is_default: boolean;
}

interface VehicleSummary {
    id: number;
    license_plate: string;
    brand?: string | null;
    model?: string | null;
    status: string;
    status_label?: string;
}

interface CustomerProp {
    id: number;
    name: string;
    trade_name?: string | null;
    type_label: string;
    status: string;
    status_label: string;
    email?: string | null;
    phone?: string | null;
    created_by?: { name?: string } | null;
    fiscal_profiles?: FiscalProfile[];
}

const props = defineProps<{
    customer: CustomerProp;
    vehicles: VehicleSummary[];
    documents: {
        maintenance_orders?: unknown[];
        quotations?: unknown[];
        billing_requests?: unknown[];
    };
    can: {
        update?: boolean;
        delete?: boolean;
    };
}>();

const page = usePage();
const canCreateVehicles = computed(
    () => !!(page.props.can as Record<string, boolean> | undefined)?.createVehicles,
);

const confirmOpen = ref(false);
const processing = ref(false);

const detailItems = computed(() => [
    { label: 'Tipo', value: props.customer.type_label, key: 'type' },
    { label: 'Estatus', value: props.customer.status_label, key: 'status' },
    ...(props.customer.trade_name
        ? [{ label: 'Nombre comercial', value: props.customer.trade_name, key: 'trade_name' }]
        : []),
    { label: 'Email', value: props.customer.email || '—', key: 'email' },
    { label: 'Teléfono', value: props.customer.phone || '—', key: 'phone' },
    { label: 'Creado por', value: props.customer.created_by?.name || '—', key: 'created_by' },
]);

function askDeactivate(): void {
    confirmOpen.value = true;
}

function confirmDeactivate(): void {
    processing.value = true;
    router.delete(route('customers.destroy', props.customer.id), {
        onFinish: () => {
            processing.value = false;
            confirmOpen.value = false;
        },
    });
}
</script>

<template>
    <AppLayout title="Detalle de cliente">
        <PageHeader
            :title="customer.name"
            :breadcrumbs="[
                { title: 'Clientes', href: route('customers.index') },
                { title: customer.name, disabled: true },
            ]"
        >
            <template #actions>
                <Link :href="route('customers.index')">
                    <v-btn variant="text">Volver</v-btn>
                </Link>
                <Link v-if="can?.update" :href="route('customers.edit', customer.id)">
                    <v-btn color="primary" variant="flat">Editar</v-btn>
                </Link>
                <v-btn
                    v-if="can?.delete && customer.status === 'active'"
                    variant="tonal"
                    color="error"
                    @click="askDeactivate"
                >
                    Desactivar
                </v-btn>
            </template>
        </PageHeader>

        <div class="d-flex flex-column ga-6">
            <PanelCard title="Datos generales">
                <DescriptionList :items="detailItems">
                    <template #status>
                        <StatusChip :status="customer.status" :map="customerStatusMap" />
                    </template>
                </DescriptionList>
            </PanelCard>

            <PanelCard title="Perfiles fiscales">
                <div v-if="customer.fiscal_profiles?.length" class="d-flex flex-column ga-3">
                    <v-card
                        v-for="profile in customer.fiscal_profiles"
                        :key="profile.id"
                        variant="outlined"
                        class="pa-4"
                    >
                        <div class="d-flex align-center justify-space-between mb-2">
                            <div class="font-weight-medium">{{ profile.legal_name }}</div>
                            <v-chip
                                v-if="profile.is_default"
                                color="primary"
                                size="small"
                                variant="tonal"
                                label
                            >
                                Predeterminado
                            </v-chip>
                        </div>
                        <v-row dense>
                            <v-col cols="12" sm="4" class="text-body-2">RFC: {{ profile.rfc }}</v-col>
                            <v-col cols="12" sm="4" class="text-body-2">Régimen: {{ profile.tax_regime_code }}</v-col>
                            <v-col cols="12" sm="4" class="text-body-2">Uso CFDI: {{ profile.cfdi_use_code }}</v-col>
                            <v-col cols="12" sm="4" class="text-body-2">CP: {{ profile.postal_code }}</v-col>
                            <v-col cols="12" sm="4" class="text-body-2">Email: {{ profile.email || '—' }}</v-col>
                        </v-row>
                    </v-card>
                </div>
                <EmptyState
                    v-else
                    title="Sin perfiles fiscales"
                    message="No hay perfiles fiscales registrados."
                    icon="mdi-file-document-outline"
                />
            </PanelCard>

            <PanelCard title="Unidades asociadas">
                <template #actions>
                    <Link
                        v-if="canCreateVehicles"
                        :href="route('customers.vehicles.create', customer.id)"
                    >
                        <v-btn variant="tonal" size="small" prepend-icon="mdi-plus">
                            Agregar unidad
                        </v-btn>
                    </Link>
                </template>

                <v-list v-if="vehicles?.length" lines="two">
                    <Link
                        v-for="vehicle in vehicles"
                        :key="vehicle.id"
                        :href="route('customers.vehicles.show', [customer.id, vehicle.id])"
                        class="text-decoration-none"
                    >
                        <v-list-item
                            :title="vehicle.license_plate"
                            :subtitle="[vehicle.brand, vehicle.model].filter(Boolean).join(' ')"
                        >
                            <template #append>
                                <StatusChip
                                    :status="vehicle.status"
                                    :map="vehicleStatusMap"
                                />
                            </template>
                        </v-list-item>
                    </Link>
                </v-list>
                <EmptyState
                    v-else
                    title="Sin unidades"
                    message="Aún no hay unidades asociadas a este cliente."
                    icon="mdi-truck-outline"
                />
            </PanelCard>

            <PanelCard title="Documentos relacionados">
                <v-row>
                    <v-col cols="12" md="4">
                        <div class="text-caption text-medium-emphasis text-uppercase">Órdenes</div>
                        <div class="text-h6">{{ documents?.maintenance_orders?.length ?? 0 }}</div>
                    </v-col>
                    <v-col cols="12" md="4">
                        <div class="text-caption text-medium-emphasis text-uppercase">Cotizaciones</div>
                        <div class="text-h6">{{ documents?.quotations?.length ?? 0 }}</div>
                    </v-col>
                    <v-col cols="12" md="4">
                        <div class="text-caption text-medium-emphasis text-uppercase">Facturación</div>
                        <div class="text-h6">{{ documents?.billing_requests?.length ?? 0 }}</div>
                    </v-col>
                </v-row>
            </PanelCard>
        </div>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Desactivar cliente"
            :message="`¿Desactivar a ${customer.name}? El historial permanecerá consultable.`"
            confirm-text="Desactivar"
            :loading="processing"
            @confirm="confirmDeactivate"
        />
    </AppLayout>
</template>
