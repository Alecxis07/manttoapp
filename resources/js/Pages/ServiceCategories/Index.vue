<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    categories: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const active = ref(props.filters.active ?? '');

watch([search, active], () => {
    router.get(route('service-categories.index'), {
        search: search.value || undefined,
        active: active.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const deactivate = (category) => {
    if (confirm(`¿Desactivar la categoría ${category.name}?`)) {
        router.post(route('service-categories.deactivate', category.id));
    }
};

const remove = (category) => {
    if (category.in_use) {
        alert('La categoría está en uso. Solo puede desactivarse.');
        return;
    }
    if (confirm(`¿Eliminar la categoría ${category.name}?`)) {
        router.delete(route('service-categories.destroy', category.id));
    }
};
</script>

<template>
    <AppLayout title="Categorías de servicio">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Categorías de servicio
                </h2>
                <Link :href="route('service-categories.create')">
                    <PrimaryButton>
                        Nueva categoría
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
                            placeholder="Buscar por código o nombre"
                        />
                        <select
                            v-model="active"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activas</option>
                            <option value="0">Inactivas</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicios</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="category in categories.data" :key="category.id">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ category.code }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ category.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ category.services_count }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ category.is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right space-x-2">
                                        <Link :href="route('service-categories.show', category.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Ver
                                        </Link>
                                        <Link :href="route('service-categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Editar
                                        </Link>
                                        <button
                                            v-if="category.is_active"
                                            type="button"
                                            class="text-amber-600 hover:text-amber-800"
                                            @click="deactivate(category)"
                                        >
                                            Desactivar
                                        </button>
                                        <button
                                            v-if="!category.in_use"
                                            type="button"
                                            class="text-red-600 hover:text-red-800"
                                            @click="remove(category)"
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="categories.data.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay categorías para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="categories.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
                        <template v-for="(link, index) in categories.links" :key="index">
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
