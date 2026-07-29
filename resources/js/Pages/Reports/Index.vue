<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    can: Object,
});
</script>

<template>
    <AppLayout title="Reportes">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reportes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link
                        :href="route('reports.vehicle-history')"
                        class="bg-white shadow-xl sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-200 transition"
                    >
                        <h3 class="text-lg font-medium text-gray-900">Historial por unidad</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Órdenes, servicios y refacciones de una unidad en un periodo.
                        </p>
                    </Link>
                    <Link
                        :href="route('reports.orders')"
                        class="bg-white shadow-xl sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-200 transition"
                    >
                        <h3 class="text-lg font-medium text-gray-900">Órdenes por periodo</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Filtra por fechas de recepción, entrega o terminación.
                        </p>
                    </Link>
                    <Link
                        :href="route('reports.quotations')"
                        class="bg-white shadow-xl sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-200 transition"
                    >
                        <h3 class="text-lg font-medium text-gray-900">Cotizaciones por estado</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Emisiones, estados y tasa de conversión a orden.
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
