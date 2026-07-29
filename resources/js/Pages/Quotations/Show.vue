<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    quotation: Object,
    versions: Array,
    can: Object,
});

const send = () => router.post(route('quotations.send', props.quotation.id));
const version = () => {
    if (confirm('¿Crear una nueva versión a partir de esta cotización?')) {
        router.post(route('quotations.version', props.quotation.id));
    }
};
const accept = () => router.post(route('quotations.accept', props.quotation.id));
const reject = () => {
    const reason = prompt('Motivo del rechazo (opcional):') ?? '';
    router.post(route('quotations.reject', props.quotation.id), { reason });
};
const convert = () => {
    if (confirm('¿Convertir esta cotización aceptada en una orden de mantenimiento?')) {
        router.post(route('quotations.convert', props.quotation.id));
    }
};
const cancel = () => {
    const reason = prompt('Motivo de cancelación (opcional):') ?? '';
    router.post(route('quotations.cancel', props.quotation.id), { reason });
};
</script>

<template>
    <AppLayout :title="quotation.folio">
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ quotation.folio }}
                        <span class="text-gray-500 font-normal">v{{ quotation.version }}</span>
                    </h2>
                    <p class="text-sm text-gray-500">{{ quotation.status_label }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a
                        v-if="can?.exportPdf"
                        :href="route('quotations.pdf', quotation.id)"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                    >
                        PDF
                    </a>
                    <Link
                        v-if="can?.update && quotation.is_editable"
                        :href="route('quotations.edit', quotation.id)"
                    >
                        <SecondaryButton>Editar</SecondaryButton>
                    </Link>
                    <PrimaryButton
                        v-if="can?.send && quotation.status === 'draft'"
                        type="button"
                        @click="send"
                    >
                        Enviar
                    </PrimaryButton>
                    <SecondaryButton
                        v-if="can?.version && quotation.status !== 'draft' && quotation.status !== 'accepted' && quotation.status !== 'cancelled'"
                        type="button"
                        @click="version"
                    >
                        Nueva versión
                    </SecondaryButton>
                    <PrimaryButton
                        v-if="can?.accept && quotation.can_be_accepted"
                        type="button"
                        @click="accept"
                    >
                        Aceptar
                    </PrimaryButton>
                    <SecondaryButton
                        v-if="can?.reject && quotation.status === 'sent'"
                        type="button"
                        @click="reject"
                    >
                        Rechazar
                    </SecondaryButton>
                    <PrimaryButton
                        v-if="can?.convert && quotation.status === 'accepted' && !quotation.maintenance_order_id"
                        type="button"
                        @click="convert"
                    >
                        Convertir a orden
                    </PrimaryButton>
                    <SecondaryButton
                        v-if="can?.cancel && quotation.status !== 'cancelled' && quotation.status !== 'accepted'"
                        type="button"
                        @click="cancel"
                    >
                        Cancelar
                    </SecondaryButton>
                </div>
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

                <div class="bg-white shadow-xl sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Cliente:</span> {{ quotation.customer?.name }}</div>
                    <div><span class="text-gray-500">Unidad:</span> {{ quotation.vehicle?.license_plate }}</div>
                    <div><span class="text-gray-500">Vigencia:</span> {{ quotation.valid_until || '—' }}</div>
                    <div><span class="text-gray-500">Emitida:</span> {{ quotation.issued_at || '—' }}</div>
                    <div class="md:col-span-2"><span class="text-gray-500">Condiciones:</span> {{ quotation.commercial_terms || '—' }}</div>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 overflow-x-auto">
                    <h3 class="font-medium text-gray-900 mb-4">Partidas</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Tipo</th>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Descripción</th>
                                <th class="px-3 py-2 text-right text-xs uppercase text-gray-500">Cant.</th>
                                <th class="px-3 py-2 text-right text-xs uppercase text-gray-500">P. unit.</th>
                                <th class="px-3 py-2 text-right text-xs uppercase text-gray-500">Desc.</th>
                                <th class="px-3 py-2 text-right text-xs uppercase text-gray-500">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in quotation.items" :key="item.id">
                                <td class="px-3 py-2 text-sm">{{ item.item_type_label }}</td>
                                <td class="px-3 py-2 text-sm">{{ item.code }} — {{ item.description }}</td>
                                <td class="px-3 py-2 text-sm text-right">{{ item.quantity }}</td>
                                <td class="px-3 py-2 text-sm text-right">${{ item.unit_price }}</td>
                                <td class="px-3 py-2 text-sm text-right">${{ item.discount }}</td>
                                <td class="px-3 py-2 text-sm text-right">${{ item.line_total }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4 text-sm text-right space-y-1">
                        <div>Subtotal: ${{ quotation.subtotal }}</div>
                        <div>Descuento: ${{ quotation.discount_total }}</div>
                        <div>IVA ({{ quotation.tax_rate }}%): ${{ quotation.tax_total }}</div>
                        <div class="font-semibold text-base">Total: ${{ quotation.total }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-xl sm:rounded-lg p-6">
                        <h3 class="font-medium text-gray-900 mb-4">Versiones</h3>
                        <ul class="space-y-2 text-sm">
                            <li v-for="versionRow in versions" :key="versionRow.id">
                                <Link
                                    :href="route('quotations.show', versionRow.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                    :class="{ 'font-semibold': versionRow.is_current }"
                                >
                                    {{ versionRow.folio }} v{{ versionRow.version }} — {{ versionRow.status_label }} (${{ versionRow.total }})
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white shadow-xl sm:rounded-lg p-6">
                        <h3 class="font-medium text-gray-900 mb-4">Historial de estados</h3>
                        <ul class="space-y-2 text-sm">
                            <li v-for="(row, index) in quotation.status_history" :key="index">
                                <span class="text-gray-500">{{ row.created_at }}</span>
                                — {{ row.to_status_label }}
                                <span v-if="row.user"> ({{ row.user.name }})</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
