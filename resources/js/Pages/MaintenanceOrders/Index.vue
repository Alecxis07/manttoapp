<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    orders: Object,
    filters: Object,
    statuses: Array,
    types: Array,
    customers: Array,
    can: Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const type = ref(props.filters.type ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');

watch([search, status, type, customerId], () => {
    router.get(route('maintenance-orders.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        type: type.value || undefined,
        customer_id: customerId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <AppLayout title="Órdenes">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Órdenes de mantenimiento
                </h2>
                <Link v-if="can?.create" :href="route('maintenance-orders.create')">
                    <PrimaryButton>
                        Nueva orden
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="$page.props.flash?.success"
                    class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded"
                >
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <div class="flex flex-col lg:flex-row gap-4 mb-6">
                        <TextInput
                            v-model="search"
                            type="search"
                            class="w-full lg:w-64"
                            placeholder="Buscar folio, cliente, placas…"
                        />
                        <select
                            v-model="status"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos los estados</option>
                            <option v-for="option in statuses" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <select
                            v-model="type"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos los tipos</option>
                            <option v-for="option in types" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <select
                            v-model="customerId"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos los clientes</option>
                            <option
                                v-for="customer in customers"
                                :key="customer.id"
                                :value="String(customer.id)"
                            >
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unidad</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                    <td class="px-3 py-3 text-sm">
                                        <Link
                                            :href="route('maintenance-orders.show', order.id)"
                                            class="text-indigo-600 hover:underline font-medium"
                                        >
                                            {{ order.folio }}
                                        </Link>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ order.customer_name }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ order.vehicle_plate }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ order.type_label }}</td>
                                    <td class="px-3 py-3 text-sm">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-800">
                                            {{ order.status_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-right text-gray-700">
                                        ${{ Number(order.total).toFixed(2) }}
                                    </td>
                                </tr>
                                <tr v-if="!orders.data?.length">
                                    <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">
                                        No hay órdenes registradas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="orders.links?.length > 3" class="mt-4 flex gap-2 flex-wrap">
                        <Link
                            v-for="(link, index) in orders.links"
                            :key="index"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm rounded border"
                            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
