<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';
import KpiCard from '@/Components/Ui/KpiCard.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';

interface FrequentVehicle {
    id: number;
    customer_id: number;
    license_plate: string;
    brand?: string;
    model?: string;
    orders_count: number;
}

interface DashboardKpis {
    period: { label: string };
    active_orders: number;
    completed_this_month: number;
    pending_quotations: number;
    accepted_quotations: number;
    full_access?: boolean;
    pending_billing?: number;
    period_revenue?: number | string;
    units_served?: number;
    frequent_vehicles?: FrequentVehicle[];
}

const props = defineProps<{
    kpis?: DashboardKpis | null;
    can?: { viewReports?: boolean };
}>();

const revenueDisplay = computed(() => {
    if (!props.kpis?.full_access) {
        return '—';
    }

    const value = Number(props.kpis.period_revenue ?? 0);

    return value.toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    });
});
</script>

<template>
    <AppLayout title="Dashboard">
        <PageHeader
            title="Dashboard"
            :subtitle="kpis ? `Periodo: ${kpis.period.label}` : undefined"
        >
            <template v-if="can?.viewReports" #actions>
                <Link :href="route('reports.index')" class="text-decoration-none">
                    <v-btn
                        variant="tonal"
                        color="primary"
                        prepend-icon="mdi-chart-box-outline"
                    >
                        Ver reportes
                    </v-btn>
                </Link>
            </template>
        </PageHeader>

        <EmptyState
            v-if="!kpis"
            title="Sin indicadores"
            message="No hay indicadores disponibles para tu rol."
            icon="mdi-chart-timeline-variant"
        />

        <template v-else>
            <v-row class="mb-2">
                <v-col cols="12" sm="6" lg="3">
                    <div data-testid="kpi-active-orders">
                        <KpiCard
                            title="Órdenes activas"
                            :value="kpis.active_orders"
                            icon="mdi-wrench-outline"
                        />
                    </div>
                </v-col>
                <v-col cols="12" sm="6" lg="3">
                    <div data-testid="kpi-completed-month">
                        <KpiCard
                            title="Terminadas del mes"
                            :value="kpis.completed_this_month"
                            icon="mdi-check-circle-outline"
                        />
                    </div>
                </v-col>
                <v-col cols="12" sm="6" lg="3">
                    <div data-testid="kpi-pending-quotations">
                        <KpiCard
                            title="Cotizaciones pendientes"
                            :value="kpis.pending_quotations"
                            icon="mdi-file-document-outline"
                        />
                    </div>
                </v-col>
                <v-col cols="12" sm="6" lg="3">
                    <div data-testid="kpi-accepted-quotations">
                        <KpiCard
                            title="Cotizaciones aceptadas (mes)"
                            :value="kpis.accepted_quotations"
                            icon="mdi-file-check-outline"
                        />
                    </div>
                </v-col>
                <template v-if="kpis.full_access">
                    <v-col cols="12" sm="6" lg="3">
                        <div data-testid="kpi-pending-billing">
                            <KpiCard
                                title="Facturación pendiente"
                                :value="kpis.pending_billing ?? 0"
                                icon="mdi-receipt-text-outline"
                            />
                        </div>
                    </v-col>
                    <v-col cols="12" sm="6" lg="3">
                        <div data-testid="kpi-period-revenue">
                            <KpiCard
                                title="Ingresos del periodo"
                                :value="revenueDisplay"
                                icon="mdi-cash-multiple"
                            />
                        </div>
                    </v-col>
                    <v-col cols="12" sm="6" lg="3">
                        <div data-testid="kpi-units-served">
                            <KpiCard
                                title="Unidades atendidas (mes)"
                                :value="kpis.units_served ?? 0"
                                icon="mdi-truck-outline"
                            />
                        </div>
                    </v-col>
                </template>
            </v-row>

            <PanelCard
                v-if="kpis.full_access && kpis.frequent_vehicles?.length"
                title="Unidades con mayor frecuencia"
                class="mt-4"
            >
                <v-list lines="two" class="bg-transparent pa-0">
                    <v-list-item
                        v-for="vehicle in kpis.frequent_vehicles"
                        :key="vehicle.id"
                    >
                        <v-list-item-title>
                            <Link
                                :href="route('customers.vehicles.show', [vehicle.customer_id, vehicle.id])"
                                class="text-primary text-decoration-none font-weight-medium"
                            >
                                {{ vehicle.license_plate }}
                            </Link>
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            {{ vehicle.brand }} {{ vehicle.model }}
                        </v-list-item-subtitle>
                        <template #append>
                            <span class="text-body-2 text-medium-emphasis">
                                {{ vehicle.orders_count }} órdenes
                            </span>
                        </template>
                    </v-list-item>
                </v-list>
            </PanelCard>
        </template>
    </AppLayout>
</template>
