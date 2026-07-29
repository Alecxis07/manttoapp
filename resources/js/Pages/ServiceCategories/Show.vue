<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    category: Object,
});

const deactivate = () => {
    if (confirm(`¿Desactivar la categoría ${props.category.name}?`)) {
        router.post(route('service-categories.deactivate', props.category.id));
    }
};
</script>

<template>
    <AppLayout title="Categoría">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ category.name }}
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('service-categories.edit', category.id)">
                        <PrimaryButton>Editar</PrimaryButton>
                    </Link>
                    <SecondaryButton v-if="category.is_active" @click="deactivate">
                        Desactivar
                    </SecondaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Código</dt>
                            <dd class="text-sm text-gray-900">{{ category.code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estatus</dt>
                            <dd class="text-sm text-gray-900">{{ category.is_active ? 'Activa' : 'Inactiva' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">Descripción</dt>
                            <dd class="text-sm text-gray-900">{{ category.description || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Servicios asociados</dt>
                            <dd class="text-sm text-gray-900">{{ category.services_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Refacciones asociadas</dt>
                            <dd class="text-sm text-gray-900">{{ category.parts_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">En uso (RN-CAT-001)</dt>
                            <dd class="text-sm text-gray-900">{{ category.in_use ? 'Sí — solo desactivable' : 'No' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
