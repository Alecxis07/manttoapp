<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    billingRequests: Object,
    filters: Object,
    statuses: Array,
    can: Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

watch([search, status], () => {
    router.get(route('billing-requests.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <AppLayout title="Facturación">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Solicitudes de facturación
                </h2>
                <Link v-if="can?.create" :href="route('billing-requests.create')">
                    <PrimaryButton>
                        Nueva solicitud
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
                            placeholder="Buscar folio, cliente o referencia"
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
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="row in billingRequests.data" :key="row.id">
                                    <td class="px-3 py-3 text-sm text-gray-900">{{ row.folio }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ row.customer?.name }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ row.status_label }}</td>
                                    <td class="px-3 py-3 text-sm text-right text-gray-900">${{ row.total }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ row.invoice_reference || '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-right">
                                        <Link :href="route('billing-requests.show', row.id)" class="text-indigo-600 hover:text-indigo-900">
                                            Ver
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!billingRequests.data.length">
                                    <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">
                                        No hay solicitudes de facturación.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
