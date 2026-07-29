<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    billingRequest: Object,
    can: Object,
});

const submit = () => router.post(route('billing-requests.submit', props.billingRequest.id));

const transition = (status) => {
    const notes = prompt('Observaciones (opcional):') ?? '';
    router.post(route('billing-requests.transition', props.billingRequest.id), {
        status,
        notes,
    });
};

const process = () => {
    const invoiceReference = prompt('Referencia de factura emitida:');
    if (!invoiceReference) {
        return;
    }
    router.post(route('billing-requests.process', props.billingRequest.id), {
        invoice_reference: invoiceReference,
    });
};
</script>

<template>
    <AppLayout :title="billingRequest.folio">
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ billingRequest.folio }}
                    </h2>
                    <p class="text-sm text-gray-500">{{ billingRequest.status_label }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-if="can?.update && billingRequest.is_editable"
                        :href="route('billing-requests.edit', billingRequest.id)"
                    >
                        <SecondaryButton>Editar</SecondaryButton>
                    </Link>
                    <PrimaryButton
                        v-if="can?.submit && (billingRequest.status === 'draft' || billingRequest.status === 'incomplete')"
                        type="button"
                        @click="submit"
                    >
                        Enviar a revisión
                    </PrimaryButton>
                    <SecondaryButton
                        v-for="transitionOption in billingRequest.allowed_transitions"
                        :key="transitionOption.value"
                        type="button"
                        @click="transition(transitionOption.value)"
                    >
                        {{ transitionOption.label }}
                    </SecondaryButton>
                    <PrimaryButton
                        v-if="can?.process && billingRequest.status === 'approved'"
                        type="button"
                        @click="process"
                    >
                        Marcar procesada
                    </PrimaryButton>
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
                    <div><span class="text-gray-500">Cliente:</span> {{ billingRequest.customer?.name }}</div>
                    <div><span class="text-gray-500">Unidad:</span> {{ billingRequest.vehicle?.license_plate || '—' }}</div>
                    <div>
                        <span class="text-gray-500">Origen:</span>
                        <span v-if="billingRequest.maintenance_order">Orden {{ billingRequest.maintenance_order.folio }}</span>
                        <span v-else-if="billingRequest.quotation">Cotización {{ billingRequest.quotation.folio }} v{{ billingRequest.quotation.version }}</span>
                        <span v-else>—</span>
                    </div>
                    <div><span class="text-gray-500">Referencia factura:</span> {{ billingRequest.invoice_reference || '—' }}</div>
                    <div><span class="text-gray-500">Método pago:</span> {{ billingRequest.payment_method_code || '—' }}</div>
                    <div><span class="text-gray-500">Forma pago:</span> {{ billingRequest.payment_form_code || '—' }}</div>
                    <div class="md:col-span-2"><span class="text-gray-500">Observaciones:</span> {{ billingRequest.notes || '—' }}</div>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">Snapshot fiscal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div><span class="text-gray-500">Razón social:</span> {{ billingRequest.fiscal_profile_snapshot?.legal_name || '—' }}</div>
                        <div><span class="text-gray-500">RFC:</span> {{ billingRequest.fiscal_profile_snapshot?.rfc || '—' }}</div>
                        <div><span class="text-gray-500">Régimen:</span> {{ billingRequest.fiscal_profile_snapshot?.tax_regime_code || '—' }}</div>
                        <div><span class="text-gray-500">Uso CFDI:</span> {{ billingRequest.fiscal_profile_snapshot?.cfdi_use_code || '—' }}</div>
                        <div><span class="text-gray-500">C.P.:</span> {{ billingRequest.fiscal_profile_snapshot?.postal_code || '—' }}</div>
                        <div><span class="text-gray-500">Email:</span> {{ billingRequest.fiscal_profile_snapshot?.email || '—' }}</div>
                    </div>
                    <p v-if="!billingRequest.has_complete_fiscal_data" class="mt-3 text-sm text-amber-700">
                        Datos fiscales incompletos — no se puede enviar a revisión.
                    </p>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6 overflow-x-auto">
                    <h3 class="font-medium text-gray-900 mb-4">Conceptos</h3>
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
                            <tr v-for="item in billingRequest.items" :key="item.id">
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
                        <div>Subtotal: ${{ billingRequest.subtotal }}</div>
                        <div>Descuento: ${{ billingRequest.discount_total }}</div>
                        <div>IVA ({{ billingRequest.tax_rate }}%): ${{ billingRequest.tax_total }}</div>
                        <div class="font-semibold text-base">Total: ${{ billingRequest.total }}</div>
                    </div>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">Historial de estados</h3>
                    <ul class="space-y-2 text-sm">
                        <li v-for="(row, index) in billingRequest.status_history" :key="index">
                            <span class="text-gray-500">{{ row.created_at }}</span>
                            — {{ row.to_status_label }}
                            <span v-if="row.user"> ({{ row.user.name }})</span>
                            <span v-if="row.notes" class="text-gray-500"> — {{ row.notes }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
