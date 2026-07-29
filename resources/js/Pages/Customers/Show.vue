<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    customer: Object,
    vehicles: Array,
    documents: Object,
    can: Object,
});

const deactivate = () => {
    if (confirm(`¿Desactivar a ${props.customer.name}? El historial permanecerá consultable.`)) {
        router.delete(route('customers.destroy', props.customer.id));
    }
};
</script>

<template>
    <AppLayout title="Detalle de cliente">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ customer.name }}
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('customers.index')">
                        <SecondaryButton>Volver</SecondaryButton>
                    </Link>
                    <Link v-if="can?.update" :href="route('customers.edit', customer.id)">
                        <PrimaryButton>Editar</PrimaryButton>
                    </Link>
                    <SecondaryButton
                        v-if="can?.delete && customer.status === 'active'"
                        type="button"
                        @click="deactivate"
                    >
                        Desactivar
                    </SecondaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="$page.props.flash?.success"
                    class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded"
                >
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Tipo</div>
                        <div class="text-gray-900">{{ customer.type_label }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Estatus</div>
                        <div class="text-gray-900">{{ customer.status_label }}</div>
                    </div>
                    <div v-if="customer.trade_name">
                        <div class="text-sm text-gray-500">Nombre comercial</div>
                        <div class="text-gray-900">{{ customer.trade_name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="text-gray-900">{{ customer.email || '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Teléfono</div>
                        <div class="text-gray-900">{{ customer.phone || '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Creado por</div>
                        <div class="text-gray-900">{{ customer.created_by?.name || '—' }}</div>
                    </div>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Perfiles fiscales</h3>
                    <div v-if="customer.fiscal_profiles?.length" class="space-y-3">
                        <div
                            v-for="profile in customer.fiscal_profiles"
                            :key="profile.id"
                            class="border border-gray-100 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium text-gray-900">{{ profile.legal_name }}</div>
                                <span
                                    v-if="profile.is_default"
                                    class="text-xs font-semibold px-2 py-1 rounded-full bg-indigo-100 text-indigo-800"
                                >
                                    Predeterminado
                                </span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-600">
                                <div>RFC: {{ profile.rfc }}</div>
                                <div>Régimen: {{ profile.tax_regime_code }}</div>
                                <div>Uso CFDI: {{ profile.cfdi_use_code }}</div>
                                <div>CP: {{ profile.postal_code }}</div>
                                <div>Email: {{ profile.email || '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">Sin perfiles fiscales registrados.</p>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Unidades asociadas</h3>
                        <Link
                            v-if="$page.props.can?.createVehicles"
                            :href="route('customers.vehicles.create', customer.id)"
                            class="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            Agregar unidad
                        </Link>
                    </div>
                    <div v-if="vehicles?.length" class="space-y-2">
                        <Link
                            v-for="vehicle in vehicles"
                            :key="vehicle.id"
                            :href="route('customers.vehicles.show', [customer.id, vehicle.id])"
                            class="flex justify-between text-sm text-gray-700 hover:text-indigo-700 border border-gray-100 rounded p-3"
                        >
                            <span>
                                {{ vehicle.license_plate }}
                                <span v-if="vehicle.brand" class="text-gray-500">
                                    — {{ vehicle.brand }} {{ vehicle.model }}
                                </span>
                            </span>
                            <span>{{ vehicle.status_label || vehicle.status }}</span>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500">
                        Aún no hay unidades asociadas a este cliente.
                    </p>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Documentos relacionados</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div>
                            Órdenes: {{ documents?.maintenance_orders?.length ?? 0 }}
                        </div>
                        <div>
                            Cotizaciones: {{ documents?.quotations?.length ?? 0 }}
                        </div>
                        <div>
                            Facturación: {{ documents?.billing_requests?.length ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
