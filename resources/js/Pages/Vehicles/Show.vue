<script setup>
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    customer: Object,
    vehicle: Object,
    attachments: Array,
    auditHistory: Array,
    timeline: Array,
    historyFilters: Object,
    eventTypes: Array,
    can: Object,
});

const activeTab = ref('identification');

const filters = reactive({
    from: props.historyFilters?.from || '',
    to: props.historyFilters?.to || '',
    type: props.historyFilters?.type || '',
});

const tabs = [
    { id: 'identification', label: 'Identificación' },
    { id: 'owner', label: 'Propietario' },
    { id: 'history', label: 'Historial' },
];

const destroy = () => {
    if (props.vehicle.is_in_use) {
        alert('Esta unidad tiene órdenes asociadas y no puede eliminarse.');
        return;
    }

    if (confirm(`¿Eliminar la unidad ${props.vehicle.license_plate}?`)) {
        router.delete(route('customers.vehicles.destroy', [props.customer.id, props.vehicle.id]));
    }
};

const applyHistoryFilters = () => {
    const query = {};

    if (filters.from) {
        query.from = filters.from;
    }

    if (filters.to) {
        query.to = filters.to;
    }

    if (filters.type) {
        query.type = filters.type;
    }

    router.get(
        route('customers.vehicles.show', [props.customer.id, props.vehicle.id]),
        query,
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                activeTab.value = 'history';
            },
        },
    );
};

const clearHistoryFilters = () => {
    filters.from = '';
    filters.to = '';
    filters.type = '';
    applyHistoryFilters();
};

const pdfUrl = () => {
    const query = {};

    if (filters.from) {
        query.from = filters.from;
    }

    if (filters.to) {
        query.to = filters.to;
    }

    if (filters.type) {
        query.type = filters.type;
    }

    return route('customers.vehicles.history.pdf', {
        customer: props.customer.id,
        vehicle: props.vehicle.id,
        ...query,
    });
};

const formatAmount = (amount) => {
    if (amount === null || amount === undefined || amount === '') {
        return '—';
    }

    return Number(amount).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (iso) => {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleString('es-MX');
};
</script>

<template>
    <AppLayout :title="`Unidad ${vehicle.license_plate}`">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ vehicle.license_plate }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ vehicle.brand }} {{ vehicle.model }} · {{ vehicle.status_label }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('vehicles.index')">
                        <SecondaryButton>Volver</SecondaryButton>
                    </Link>
                    <Link
                        v-if="can?.update"
                        :href="route('customers.vehicles.edit', [customer.id, vehicle.id])"
                    >
                        <PrimaryButton>Editar</PrimaryButton>
                    </Link>
                    <SecondaryButton
                        v-if="can?.delete && !vehicle.is_in_use"
                        type="button"
                        @click="destroy"
                    >
                        Eliminar
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

                <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200 px-4 flex gap-1 overflow-x-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            type="button"
                            class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition"
                            :class="activeTab === tab.id
                                ? 'border-indigo-600 text-indigo-700'
                                : 'border-transparent text-gray-500 hover:text-gray-700'"
                            @click="activeTab = tab.id"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <div v-show="activeTab === 'identification'" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Placas</div>
                                <div class="text-gray-900 font-medium">{{ vehicle.license_plate }}</div>
                                <div class="text-xs text-gray-400">Norm: {{ vehicle.license_plate_normalized }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">VIN</div>
                                <div class="text-gray-900">{{ vehicle.vin || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Número económico</div>
                                <div class="text-gray-900">{{ vehicle.economic_number || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Tipo</div>
                                <div class="text-gray-900">{{ vehicle.vehicle_type?.name || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Marca / Modelo / Año</div>
                                <div class="text-gray-900">{{ vehicle.brand }} {{ vehicle.model }} ({{ vehicle.year }})</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Motor</div>
                                <div class="text-gray-900">{{ vehicle.engine_type || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Kilometraje</div>
                                <div class="text-gray-900">{{ vehicle.current_mileage?.toLocaleString() }} km</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Estatus</div>
                                <div class="text-gray-900">{{ vehicle.status_label }}</div>
                                <div v-if="vehicle.status_notes" class="text-sm text-gray-500 mt-1">
                                    {{ vehicle.status_notes }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Adjuntos</h3>
                            <ul v-if="attachments?.length" class="text-sm text-gray-600 space-y-1">
                                <li v-for="file in attachments" :key="file.id">
                                    {{ file.original_name }}
                                    <span class="text-gray-400">
                                        ({{ file.uploaded_by?.name || 'sistema' }})
                                    </span>
                                </li>
                            </ul>
                            <p v-else class="text-sm text-gray-500">Sin adjuntos.</p>
                        </div>
                    </div>

                    <div v-show="activeTab === 'owner'" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Cliente</div>
                                <Link
                                    :href="route('customers.show', customer.id)"
                                    class="text-indigo-600 hover:text-indigo-800 font-medium"
                                >
                                    {{ customer.name }}
                                </Link>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Nombre comercial</div>
                                <div class="text-gray-900">{{ customer.trade_name || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Estatus del cliente</div>
                                <div class="text-gray-900">{{ customer.status }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Registrado por</div>
                                <div class="text-gray-900">{{ vehicle.created_by?.name || '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-show="activeTab === 'history'" class="p-6 space-y-6">
                        <div class="flex flex-col lg:flex-row lg:items-end gap-3 justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Expediente consolidado</h3>
                                <p class="text-sm text-gray-500">
                                    Órdenes, cotizaciones, evidencias y facturación (si aplica), orden descendente.
                                </p>
                            </div>
                            <a
                                v-if="can?.exportHistory"
                                :href="pdfUrl()"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                            >
                                Exportar PDF
                            </a>
                        </div>

                        <form
                            class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end"
                            @submit.prevent="applyHistoryFilters"
                        >
                            <div>
                                <label class="block text-sm text-gray-600 mb-1" for="history-from">Desde</label>
                                <input
                                    id="history-from"
                                    v-model="filters.from"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1" for="history-to">Hasta</label>
                                <input
                                    id="history-to"
                                    v-model="filters.to"
                                    type="date"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1" for="history-type">Tipo</label>
                                <select
                                    id="history-type"
                                    v-model="filters.type"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                >
                                    <option value="">Todos</option>
                                    <option
                                        v-for="eventType in eventTypes"
                                        :key="eventType.value"
                                        :value="eventType.value"
                                    >
                                        {{ eventType.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <PrimaryButton type="submit">Filtrar</PrimaryButton>
                                <SecondaryButton type="button" @click="clearHistoryFilters">
                                    Limpiar
                                </SecondaryButton>
                            </div>
                        </form>

                        <div v-if="timeline?.length" class="space-y-3">
                            <div
                                v-for="entry in timeline"
                                :key="entry.id"
                                class="border border-gray-100 rounded-lg p-4"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs font-medium uppercase tracking-wide text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                                {{ entry.type_label }}
                                            </span>
                                            <span
                                                v-if="entry.status_label"
                                                class="text-xs text-gray-500"
                                            >
                                                {{ entry.status_label }}
                                            </span>
                                        </div>
                                        <div class="mt-1 font-medium text-gray-900">
                                            <a
                                                v-if="entry.url"
                                                :href="entry.url"
                                                class="text-indigo-600 hover:text-indigo-800"
                                            >
                                                {{ entry.title }}
                                            </a>
                                            <span v-else>{{ entry.title }}</span>
                                        </div>
                                        <p v-if="entry.subtitle" class="text-sm text-gray-500 mt-1">
                                            {{ entry.subtitle }}
                                        </p>
                                    </div>
                                    <div class="text-sm text-right text-gray-500 shrink-0">
                                        <div>{{ formatDate(entry.occurred_at) }}</div>
                                        <div v-if="entry.amount !== null" class="font-medium text-gray-800">
                                            ${{ formatAmount(entry.amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500">
                            No hay eventos en el expediente para los filtros seleccionados.
                        </p>

                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Auditoría de cambios</h3>
                            <div v-if="auditHistory?.length" class="space-y-2 max-h-96 overflow-y-auto">
                                <div
                                    v-for="entry in auditHistory"
                                    :key="entry.id"
                                    class="text-sm border border-gray-100 rounded p-3"
                                >
                                    <div class="flex justify-between gap-2">
                                        <span class="font-medium text-gray-800">{{ entry.action }}</span>
                                        <span class="text-gray-400 text-xs">{{ entry.created_at }}</span>
                                    </div>
                                    <div class="text-gray-500">
                                        {{ entry.user?.name || 'Sistema' }}
                                    </div>
                                    <pre
                                        v-if="entry.properties"
                                        class="mt-2 text-xs text-gray-500 whitespace-pre-wrap"
                                    >{{ entry.properties }}</pre>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-500">Sin registros de auditoría.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
