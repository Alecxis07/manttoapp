<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    filters: Object,
    vehicle: Object,
    orders: Object,
    vehicles: Array,
    can: Object,
});

const vehicleId = ref(props.filters.vehicle_id ? String(props.filters.vehicle_id) : '');
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');

const apply = () => {
    router.get(route('reports.vehicle-history'), {
        vehicle_id: vehicleId.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([vehicleId, from, to], apply);
</script>

<template>
    <AppLayout title="Historial por unidad">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Historial por unidad
                </h2>
                <div class="flex items-center gap-3">
                    <a
                        v-if="can?.exportPdf"
                        :href="route('reports.vehicle-history.pdf', {
                            vehicle_id: vehicleId,
                            from: from || undefined,
                            to: to || undefined,
                        })"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        Exportar PDF
                    </a>
                    <Link :href="route('reports.index')" class="text-sm text-gray-600 hover:text-gray-900">
                        Volver
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <select
                            v-model="vehicleId"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full lg:w-96"
                        >
                            <option value="">Selecciona unidad</option>
                            <option v-for="option in vehicles" :key="option.id" :value="String(option.id)">
                                {{ option.label }}
                            </option>
                        </select>
                        <input v-model="from" type="date" class="border-gray-300 rounded-md shadow-sm">
                        <input v-model="to" type="date" class="border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <div v-if="vehicle" class="bg-white shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ vehicle.license_plate }} — {{ vehicle.customer?.name }}
                    </h3>
                    <p class="text-sm text-gray-500">{{ vehicle.brand }} {{ vehicle.model }}</p>
                </div>

                <div v-if="orders" class="bg-white shadow-xl sm:rounded-lg p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Servicios</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Refacciones</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="order in orders.data" :key="order.id">
                                <td class="px-3 py-3 text-sm">
                                    <Link :href="route('maintenance-orders.show', order.id)" class="text-indigo-600">
                                        {{ order.folio }}
                                    </Link>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ order.status_label }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ order.services?.join(', ') || '—' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">
                                    {{ order.parts?.map(p => p.description).join(', ') || '—' }}
                                </td>
                                <td class="px-3 py-3 text-sm text-right text-gray-900">
                                    {{ order.total != null ? `$${order.total}` : '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="orders.links?.length > 3" class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in orders.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm rounded border"
                            :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <div v-else class="bg-white shadow-xl sm:rounded-lg p-6 text-gray-500">
                    Selecciona una unidad para ver su historial.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
