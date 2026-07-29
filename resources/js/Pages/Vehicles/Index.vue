<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    vehicles: Object,
    filters: Object,
    statuses: Array,
    customers: Array,
    can: Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const brand = ref(props.filters.brand ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');
const plateSearch = ref('');

watch([search, status, brand, customerId], () => {
    router.get(route('vehicles.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        brand: brand.value || undefined,
        customer_id: customerId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const quickPlateSearch = () => {
    if (!plateSearch.value.trim()) {
        return;
    }

    router.get(route('vehicles.search'), {
        plate: plateSearch.value,
    });
};
</script>

<template>
    <AppLayout title="Unidades">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Unidades
                </h2>
                <Link v-if="can?.create" :href="route('vehicles.create')">
                    <PrimaryButton>
                        Nueva unidad
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
                    <form class="flex flex-col sm:flex-row gap-3 mb-6" @submit.prevent="quickPlateSearch">
                        <TextInput
                            v-model="plateSearch"
                            type="search"
                            class="w-full sm:w-72"
                            placeholder="Búsqueda rápida por placas"
                        />
                        <PrimaryButton type="submit">
                            Buscar placas
                        </PrimaryButton>
                    </form>

                    <div class="flex flex-col lg:flex-row gap-4 mb-6">
                        <TextInput
                            v-model="search"
                            type="search"
                            class="w-full lg:w-64"
                            placeholder="Buscar placas, VIN, marca…"
                        />
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
                        <select
                            v-model="status"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos los estatus</option>
                            <option
                                v-for="option in statuses"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <TextInput
                            v-model="brand"
                            type="search"
                            class="w-full lg:w-40"
                            placeholder="Marca"
                        />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Placas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca / Modelo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Año</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Km</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estatus</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="vehicle in vehicles.data" :key="vehicle.id">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ vehicle.license_plate }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ vehicle.customer_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ vehicle.vehicle_type }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ vehicle.brand }} {{ vehicle.model }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ vehicle.year }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ vehicle.current_mileage?.toLocaleString() }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ vehicle.status_label }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right space-x-3">
                                        <Link
                                            :href="route('customers.vehicles.show', [vehicle.customer_id, vehicle.id])"
                                            class="text-indigo-600 hover:text-indigo-800"
                                        >
                                            Ver
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!vehicles.data?.length">
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No se encontraron unidades.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="vehicles.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
                        <Link
                            v-for="(link, index) in vehicles.links"
                            :key="index"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm rounded border"
                            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300'"
                            v-html="link.label"
                            :preserve-scroll="true"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
