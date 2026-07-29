<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    customers: Object,
    filters: Object,
    statuses: Array,
    types: Array,
    can: Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const type = ref(props.filters.type ?? '');

watch([search, status, type], () => {
    router.get(route('customers.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        type: type.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const deactivate = (customer) => {
    if (confirm(`¿Desactivar a ${customer.name}? El historial permanecerá consultable.`)) {
        router.delete(route('customers.destroy', customer.id));
    }
};
</script>

<template>
    <AppLayout title="Clientes">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Clientes
                </h2>
                <Link v-if="can?.create" :href="route('customers.create')">
                    <PrimaryButton>
                        Nuevo cliente
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
                    <div class="flex flex-col lg:flex-row gap-4 mb-6">
                        <TextInput
                            v-model="search"
                            type="search"
                            class="w-full lg:w-72"
                            placeholder="Buscar por nombre, email o teléfono"
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFC</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="customer in customers.data" :key="customer.id">
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div>{{ customer.name }}</div>
                                        <div v-if="customer.trade_name" class="text-xs text-gray-500">{{ customer.trade_name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ customer.type_label }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ customer.email || '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ customer.rfc || '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="customer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ customer.status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right space-x-2">
                                        <Link :href="route('customers.show', customer.id)" class="text-indigo-600 hover:text-indigo-800">
                                            Ver
                                        </Link>
                                        <Link
                                            v-if="$page.props.can?.createCustomers || $page.props.permissions?.includes('customers.update')"
                                            :href="route('customers.edit', customer.id)"
                                            class="text-indigo-600 hover:text-indigo-800"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="customer.status === 'active' && $page.props.permissions?.includes('customers.update')"
                                            type="button"
                                            class="text-red-600 hover:text-red-800"
                                            @click="deactivate(customer)"
                                        >
                                            Desactivar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="customers.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay clientes para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="customers.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
                        <template v-for="(link, index) in customers.links" :key="index">
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
