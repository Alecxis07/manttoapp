<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    users: Object,
    filters: Object,
    statuses: Array,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

watch([search, status], () => {
    router.get(route('users.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const deactivate = (user) => {
    if (confirm(`¿Desactivar a ${user.name}?`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <AppLayout title="Usuarios">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Usuarios
                </h2>
                <Link :href="route('users.create')">
                    <PrimaryButton>
                        Nuevo usuario
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
                            placeholder="Buscar por nombre o email"
                        />
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
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ user.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ user.email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ user.role }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ user.status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right space-x-2">
                                        <Link :href="route('users.show', user.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Ver
                                        </Link>
                                        <Link :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Editar
                                        </Link>
                                        <button
                                            v-if="user.status === 'active' && user.id !== $page.props.auth.user.id"
                                            type="button"
                                            class="text-red-600 hover:text-red-800"
                                            @click="deactivate(user)"
                                        >
                                            Desactivar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay usuarios para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="users.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
                        <template v-for="(link, index) in users.links" :key="index">
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
