<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    services: Object,
    filters: Object,
    categories: Array,
});

const search = ref(props.filters.search ?? '');
const active = ref(props.filters.active ?? '');
const serviceCategoryId = ref(props.filters.service_category_id ?? '');

watch([search, active, serviceCategoryId], () => {
    router.get(route('service-catalog.index'), {
        search: search.value || undefined,
        active: active.value || undefined,
        service_category_id: serviceCategoryId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const deactivate = (service) => {
    if (confirm(`¿Desactivar el servicio ${service.code}?`)) {
        router.post(route('service-catalog.deactivate', service.id));
    }
};
</script>

<template>
    <AppLayout title="Catálogo de servicios">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Catálogo de servicios
                </h2>
                <Link :href="route('service-catalog.create')">
                    <PrimaryButton>
                        Nuevo servicio
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
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <TextInput
                            v-model="search"
                            type="search"
                            class="w-full sm:w-72"
                            placeholder="Buscar por código o descripción"
                        />
                        <select
                            v-model="active"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <select
                            v-model="serviceCategoryId"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todas las categorías</option>
                            <option
                                v-for="category in categories"
                                :key="category.value"
                                :value="category.value"
                            >
                                {{ category.label }}
                            </option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio base</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="service in services.data" :key="service.id">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ service.code }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ service.description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ service.category?.name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">${{ service.base_price }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="service.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ service.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right space-x-2">
                                        <Link :href="route('service-catalog.show', service.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Ver
                                        </Link>
                                        <Link :href="route('service-catalog.edit', service.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Editar
                                        </Link>
                                        <button
                                            v-if="service.is_active"
                                            type="button"
                                            class="text-amber-600 hover:text-amber-800"
                                            @click="deactivate(service)"
                                        >
                                            Desactivar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="services.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay servicios para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="services.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
                        <template v-for="(link, index) in services.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 text-sm border rounded"
                                :class="link.active ? 'bg-gray-800 text-white' : 'bg-white text-gray-700'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1 text-sm border rounded text-gray-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
