<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    parts: Object,
    filters: Object,
    types: Array,
});

const search = ref(props.filters.search ?? '');
const active = ref(props.filters.active ?? '');
const type = ref(props.filters.type ?? '');

watch([search, active, type], () => {
    router.get(route('part-catalog.index'), {
        search: search.value || undefined,
        active: active.value || undefined,
        type: type.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const deactivate = (part) => {
    if (confirm(`¿Desactivar el concepto ${part.code}?`)) {
        router.post(route('part-catalog.deactivate', part.id));
    }
};
</script>

<template>
    <AppLayout title="Catálogo de refacciones">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Catálogo de refacciones y conceptos
                </h2>
                <Link :href="route('part-catalog.create')">
                    <PrimaryButton>
                        Nuevo concepto
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
                            v-model="type"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Todos los tipos</option>
                            <option
                                v-for="option in types"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio base</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="part in parts.data" :key="part.id">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ part.code }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ part.description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ part.type_label }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">${{ part.base_price }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="part.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ part.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right space-x-2">
                                        <Link :href="route('part-catalog.show', part.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Ver
                                        </Link>
                                        <Link :href="route('part-catalog.edit', part.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Editar
                                        </Link>
                                        <button
                                            v-if="part.is_active"
                                            type="button"
                                            class="text-amber-600 hover:text-amber-800"
                                            @click="deactivate(part)"
                                        >
                                            Desactivar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="parts.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay conceptos para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="parts.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
                        <template v-for="(link, index) in parts.links" :key="index">
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
