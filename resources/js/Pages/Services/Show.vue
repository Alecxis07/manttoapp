<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    service: Object,
});

const deactivate = () => {
    if (confirm(`¿Desactivar el servicio ${props.service.code}?`)) {
        router.post(route('service-catalog.deactivate', props.service.id));
    }
};
</script>

<template>
    <AppLayout title="Servicio">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ service.code }}
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('service-catalog.edit', service.id)">
                        <PrimaryButton>Editar</PrimaryButton>
                    </Link>
                    <SecondaryButton v-if="service.is_active" @click="deactivate">
                        Desactivar
                    </SecondaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded text-sm">
                    {{ service.base_price_note }}
                </div>
                <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Descripción</dt>
                            <dd class="text-sm text-gray-900">{{ service.description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Categoría</dt>
                            <dd class="text-sm text-gray-900">{{ service.category?.name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Tipo</dt>
                            <dd class="text-sm text-gray-900">{{ service.type_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Precio base</dt>
                            <dd class="text-sm text-gray-900">${{ service.base_price }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Unidad</dt>
                            <dd class="text-sm text-gray-900">{{ service.unit_of_measure }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Tiempo estimado</dt>
                            <dd class="text-sm text-gray-900">{{ service.estimated_minutes != null ? `${service.estimated_minutes} min` : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estatus</dt>
                            <dd class="text-sm text-gray-900">{{ service.is_active ? 'Activo' : 'Inactivo' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">En uso (RN-CAT-001)</dt>
                            <dd class="text-sm text-gray-900">{{ service.in_use ? 'Sí — solo desactivable' : 'No' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
