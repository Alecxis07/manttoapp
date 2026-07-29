<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    query: String,
    customers: Array,
    vehicles: Array,
    orders: Array,
});
</script>

<template>
    <AppLayout title="Búsqueda">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Búsqueda global
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <p class="text-sm text-gray-600">
                    Resultados para: <span class="font-medium text-gray-900">{{ query || '—' }}</span>
                </p>

                <div v-if="!query" class="bg-white shadow-xl sm:rounded-lg p-6 text-gray-600">
                    Escribe un término en la barra de búsqueda.
                </div>

                <template v-else>
                    <section class="bg-white shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Clientes</h3>
                        <ul v-if="customers.length" class="divide-y divide-gray-100">
                            <li v-for="customer in customers" :key="customer.id" class="py-2">
                                <Link :href="customer.url" class="text-indigo-600 hover:text-indigo-900">
                                    {{ customer.name }}
                                </Link>
                                <span v-if="customer.email" class="ms-2 text-sm text-gray-500">{{ customer.email }}</span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">Sin resultados</p>
                    </section>

                    <section class="bg-white shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Unidades</h3>
                        <ul v-if="vehicles.length" class="divide-y divide-gray-100">
                            <li v-for="vehicle in vehicles" :key="vehicle.id" class="py-2">
                                <Link :href="vehicle.url" class="text-indigo-600 hover:text-indigo-900">
                                    {{ vehicle.license_plate }}
                                </Link>
                                <span class="ms-2 text-sm text-gray-500">
                                    {{ vehicle.brand }} {{ vehicle.model }} — {{ vehicle.customer_name }}
                                </span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">Sin resultados</p>
                    </section>

                    <section class="bg-white shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Órdenes</h3>
                        <ul v-if="orders.length" class="divide-y divide-gray-100">
                            <li v-for="order in orders" :key="order.id" class="py-2">
                                <Link :href="order.url" class="text-indigo-600 hover:text-indigo-900">
                                    {{ order.folio }}
                                </Link>
                                <span class="ms-2 text-sm text-gray-500">
                                    {{ order.status_label }} — {{ order.customer_name }} — {{ order.license_plate }}
                                </span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">Sin resultados</p>
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
