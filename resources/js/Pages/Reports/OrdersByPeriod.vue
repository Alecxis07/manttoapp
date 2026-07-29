<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    orders: Object,
    filters: Object,
    statuses: Array,
    customers: Array,
    summary: Object,
    can: Object,
});

const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');
const dateField = ref(props.filters.date_field ?? 'received_at');
const status = ref(props.filters.status ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');

watch([from, to, dateField, status, customerId], () => {
    router.get(route('reports.orders'), {
        from: from.value || undefined,
        to: to.value || undefined,
        date_field: dateField.value || undefined,
        status: status.value || undefined,
        customer_id: customerId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const exportUrl = () => route('reports.orders', {
    from: from.value || undefined,
    to: to.value || undefined,
    date_field: dateField.value || undefined,
    status: status.value || undefined,
    customer_id: customerId.value || undefined,
    export: 1,
});
</script>

<template>
    <AppLayout title="Órdenes por periodo">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Órdenes por periodo
                </h2>
                <div class="flex items-center gap-3">
                    <a v-if="can?.export" :href="exportUrl()" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Exportar CSV
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
                    <div class="flex flex-col lg:flex-row flex-wrap gap-4">
                        <input v-model="from" type="date" class="border-gray-300 rounded-md shadow-sm">
                        <input v-model="to" type="date" class="border-gray-300 rounded-md shadow-sm">
                        <select v-model="dateField" class="border-gray-300 rounded-md shadow-sm">
                            <option value="received_at">Recepción</option>
                            <option value="completed_at">Terminación</option>
                            <option value="delivered_at">Entrega</option>
                        </select>
                        <select v-model="status" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos los estatus</option>
                            <option v-for="option in statuses" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <select v-model="customerId" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos los clientes</option>
                            <option v-for="customer in customers" :key="customer.id" :value="String(customer.id)">
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>
                    <p class="mt-4 text-sm text-gray-600">
                        {{ summary.count }} órdenes
                        <span v-if="summary.total_amount != null"> · Total ${{ summary.total_amount }}</span>
                    </p>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unidad</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Servicios</th>
                                <th v-if="can?.viewFull" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="order in orders.data" :key="order.id">
                                <td class="px-3 py-3 text-sm">
                                    <Link :href="route('maintenance-orders.show', order.id)" class="text-indigo-600">
                                        {{ order.folio }}
                                    </Link>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ order.customer?.name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ order.vehicle?.license_plate }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ order.status_label }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ order.services?.join(', ') || '—' }}</td>
                                <td v-if="can?.viewFull" class="px-3 py-3 text-sm text-right">${{ order.total }}</td>
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
            </div>
        </div>
    </AppLayout>
</template>
