<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    kpis: Object,
    can: Object,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
                <Link
                    v-if="can?.viewReports"
                    :href="route('reports.index')"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    Ver reportes
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div v-if="!kpis" class="bg-white shadow-xl sm:rounded-lg p-6 text-gray-600">
                    No hay indicadores disponibles para tu rol.
                </div>

                <template v-else>
                    <p class="text-sm text-gray-500">
                        Periodo: {{ kpis.period.label }}
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Órdenes activas</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-active-orders">
                                {{ kpis.active_orders }}
                            </p>
                        </div>
                        <div class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Terminadas del mes</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-completed-month">
                                {{ kpis.completed_this_month }}
                            </p>
                        </div>
                        <div class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Cotizaciones pendientes</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-pending-quotations">
                                {{ kpis.pending_quotations }}
                            </p>
                        </div>
                        <div class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Cotizaciones aceptadas (mes)</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-accepted-quotations">
                                {{ kpis.accepted_quotations }}
                            </p>
                        </div>
                        <div v-if="kpis.full_access" class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Facturación pendiente</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-pending-billing">
                                {{ kpis.pending_billing }}
                            </p>
                        </div>
                        <div v-if="kpis.full_access" class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Ingresos del periodo</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-period-revenue">
                                ${{ kpis.period_revenue }}
                            </p>
                        </div>
                        <div v-if="kpis.full_access" class="bg-white shadow-xl sm:rounded-lg p-5">
                            <p class="text-sm text-gray-500">Unidades atendidas (mes)</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900" data-testid="kpi-units-served">
                                {{ kpis.units_served }}
                            </p>
                        </div>
                    </div>

                    <div v-if="kpis.full_access && kpis.frequent_vehicles?.length" class="bg-white shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Unidades con mayor frecuencia</h3>
                        <ul class="divide-y divide-gray-100">
                            <li
                                v-for="vehicle in kpis.frequent_vehicles"
                                :key="vehicle.id"
                                class="py-3 flex items-center justify-between gap-4"
                            >
                                <div>
                                    <Link
                                        :href="route('customers.vehicles.show', [vehicle.customer_id, vehicle.id])"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium"
                                    >
                                        {{ vehicle.license_plate }}
                                    </Link>
                                    <p class="text-sm text-gray-500">
                                        {{ vehicle.brand }} {{ vehicle.model }}
                                    </p>
                                </div>
                                <span class="text-sm text-gray-700">{{ vehicle.orders_count }} órdenes</span>
                            </li>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
