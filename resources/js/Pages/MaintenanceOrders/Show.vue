<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    order: Object,
    items: Array,
    parts: Array,
    statusHistory: Array,
    attachments: Array,
    services: Array,
    partsCatalog: Array,
    technicians: Array,
    statuses: Array,
    allowedTransitions: Array,
    can: Object,
});

const transitionForm = useForm({
    status: '',
    notes: '',
    cancellation_reason: '',
});

const diagnosisForm = useForm({
    diagnosis: props.order.diagnosis ?? '',
    technical_notes: props.order.technical_notes ?? '',
    assigned_user_id: props.order.assigned_user_id ? String(props.order.assigned_user_id) : '',
});

const itemForm = useForm({
    service_catalog_id: '',
    description: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    notes: '',
});

const partForm = useForm({
    part_catalog_id: '',
    description: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    notes: '',
});

const reopenForm = useForm({
    reason: '',
});

const attachmentForm = useForm({
    file: null,
});

const showCancelReason = computed(() => transitionForm.status === 'cancelled');

const onServiceSelect = () => {
    const selected = props.services.find((s) => String(s.id) === String(itemForm.service_catalog_id));
    if (selected) {
        itemForm.description = selected.description;
        itemForm.unit_price = selected.base_price;
    }
};

const onPartSelect = () => {
    const selected = props.partsCatalog.find((p) => String(p.id) === String(partForm.part_catalog_id));
    if (selected) {
        partForm.description = selected.description;
        partForm.unit_price = selected.base_price;
    }
};

const submitTransition = () => {
    transitionForm.post(route('maintenance-orders.transition', props.order.id), {
        preserveScroll: true,
        onSuccess: () => transitionForm.reset('notes', 'cancellation_reason'),
    });
};

const submitDiagnosis = () => {
    diagnosisForm.transform((data) => ({
        ...data,
        assigned_user_id: data.assigned_user_id ? Number(data.assigned_user_id) : null,
    })).post(route('maintenance-orders.diagnose', props.order.id), { preserveScroll: true });
};

const submitItem = () => {
    itemForm.transform((data) => ({
        ...data,
        service_catalog_id: data.service_catalog_id ? Number(data.service_catalog_id) : null,
        quantity: Number(data.quantity),
        unit_price: Number(data.unit_price),
        discount: Number(data.discount || 0),
    })).post(route('maintenance-orders.items.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => itemForm.reset('service_catalog_id', 'description', 'quantity', 'unit_price', 'discount', 'notes'),
    });
};

const submitPart = () => {
    partForm.transform((data) => ({
        ...data,
        part_catalog_id: data.part_catalog_id ? Number(data.part_catalog_id) : null,
        quantity: Number(data.quantity),
        unit_price: Number(data.unit_price),
        discount: Number(data.discount || 0),
    })).post(route('maintenance-orders.parts.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => partForm.reset('part_catalog_id', 'description', 'quantity', 'unit_price', 'discount', 'notes'),
    });
};

const submitReopen = () => {
    reopenForm.post(route('maintenance-orders.reopen', props.order.id), { preserveScroll: true });
};

const submitAttachment = () => {
    attachmentForm.post(route('maintenance-orders.attachments.store', props.order.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            attachmentForm.reset('file');
            fileInputKey.value += 1;
        },
    });
};

const fileInputKey = ref(0);
const onFileChange = (event) => {
    attachmentForm.file = event.target.files[0] ?? null;
};
</script>

<template>
    <AppLayout :title="order.folio">
        <template #header>
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ order.folio }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ order.status_label }} · {{ order.type_label }}
                    </p>
                </div>
                <Link :href="route('maintenance-orders.index')">
                    <SecondaryButton type="button">Volver al listado</SecondaryButton>
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

                <div class="bg-white shadow sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs uppercase text-gray-500">Cliente</p>
                        <p class="font-medium">{{ order.customer?.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500">Unidad</p>
                        <p class="font-medium">
                            {{ order.vehicle?.license_plate }} —
                            {{ order.vehicle?.brand }} {{ order.vehicle?.model }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500">Motivo</p>
                        <p>{{ order.reason }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500">Kilometraje</p>
                        <p>{{ order.mileage }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500">Responsable</p>
                        <p>{{ order.assignee?.name || 'Sin asignar' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500">Totales</p>
                        <p class="text-sm">
                            Subtotal ${{ Number(order.subtotal).toFixed(2) }} ·
                            Desc. ${{ Number(order.discount_total).toFixed(2) }} ·
                            IVA ({{ order.tax_rate }}%) ${{ Number(order.tax_total).toFixed(2) }} ·
                            <strong>Total ${{ Number(order.total).toFixed(2) }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Historial de estados</h3>
                    <ol class="space-y-3">
                        <li
                            v-for="entry in statusHistory"
                            :key="entry.id"
                            class="border-l-2 border-indigo-200 pl-4"
                        >
                            <p class="text-sm font-medium">
                                <span v-if="entry.from_status_label">{{ entry.from_status_label }} → </span>
                                {{ entry.to_status_label }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ entry.user?.name || 'Sistema' }} · {{ entry.created_at }}
                            </p>
                            <p v-if="entry.notes" class="text-sm text-gray-600 mt-1">{{ entry.notes }}</p>
                        </li>
                    </ol>
                </div>

                <!-- Transition -->
                <div v-if="can.changeStatus && allowedTransitions.length" class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Cambiar estado</h3>
                    <form class="space-y-3" @submit.prevent="submitTransition">
                        <div>
                            <InputLabel value="Nuevo estado" />
                            <select
                                v-model="transitionForm.status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="">Seleccione…</option>
                                <option
                                    v-for="option in allowedTransitions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="transitionForm.errors.status" class="mt-2" />
                        </div>
                        <div v-if="showCancelReason">
                            <InputLabel value="Motivo de cancelación" />
                            <textarea
                                v-model="transitionForm.cancellation_reason"
                                rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            />
                            <InputError :message="transitionForm.errors.cancellation_reason" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Notas" />
                            <TextInput v-model="transitionForm.notes" class="mt-1 block w-full" />
                        </div>
                        <PrimaryButton :disabled="transitionForm.processing || !transitionForm.status">
                            Aplicar transición
                        </PrimaryButton>
                    </form>
                </div>

                <!-- Reopen -->
                <div v-if="can.reopen && order.status === 'delivered'" class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Reabrir orden</h3>
                    <form class="space-y-3" @submit.prevent="submitReopen">
                        <div>
                            <InputLabel value="Justificación" />
                            <textarea
                                v-model="reopenForm.reason"
                                rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            />
                            <InputError :message="reopenForm.errors.reason" class="mt-2" />
                        </div>
                        <PrimaryButton :disabled="reopenForm.processing">Reabrir a En progreso</PrimaryButton>
                    </form>
                </div>

                <!-- Diagnosis -->
                <div v-if="can.diagnose" class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Diagnóstico</h3>
                    <form class="space-y-3" @submit.prevent="submitDiagnosis">
                        <div>
                            <InputLabel value="Diagnóstico" />
                            <textarea
                                v-model="diagnosisForm.diagnosis"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            />
                            <InputError :message="diagnosisForm.errors.diagnosis" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Notas técnicas" />
                            <textarea
                                v-model="diagnosisForm.technical_notes"
                                rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Responsable" />
                            <select
                                v-model="diagnosisForm.assigned_user_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="">Sin asignar</option>
                                <option v-for="user in technicians" :key="user.id" :value="String(user.id)">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <PrimaryButton :disabled="diagnosisForm.processing">Guardar diagnóstico</PrimaryButton>
                    </form>
                </div>
                <div v-else-if="order.diagnosis" class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">Diagnóstico</h3>
                    <p class="whitespace-pre-wrap">{{ order.diagnosis }}</p>
                </div>

                <!-- Items -->
                <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-lg">Servicios</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase">
                                <th class="py-2">Descripción</th>
                                <th class="py-2">Cant.</th>
                                <th class="py-2">P. unit.</th>
                                <th class="py-2">Desc.</th>
                                <th class="py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id" class="border-t">
                                <td class="py-2">{{ item.description }}</td>
                                <td class="py-2">{{ item.quantity }}</td>
                                <td class="py-2">${{ Number(item.unit_price).toFixed(2) }}</td>
                                <td class="py-2">${{ Number(item.discount).toFixed(2) }}</td>
                                <td class="py-2 text-right">${{ Number(item.line_total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <form v-if="can.addItems" class="grid grid-cols-1 md:grid-cols-6 gap-3 border-t pt-4" @submit.prevent="submitItem">
                        <div class="md:col-span-2">
                            <InputLabel value="Catálogo" />
                            <select
                                v-model="itemForm.service_catalog_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                @change="onServiceSelect"
                            >
                                <option value="">Manual…</option>
                                <option v-for="service in services" :key="service.id" :value="String(service.id)">
                                    {{ service.code }} — {{ service.description }}
                                </option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel value="Descripción" />
                            <TextInput v-model="itemForm.description" class="mt-1 block w-full" />
                            <InputError :message="itemForm.errors.description" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Cant." />
                            <TextInput v-model="itemForm.quantity" type="number" step="0.01" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="P. unit." />
                            <TextInput v-model="itemForm.unit_price" type="number" step="0.01" class="mt-1 block w-full" />
                        </div>
                        <div class="md:col-span-6">
                            <PrimaryButton :disabled="itemForm.processing">Agregar servicio</PrimaryButton>
                            <InputError :message="itemForm.errors.order" class="mt-2" />
                        </div>
                    </form>
                </div>

                <!-- Parts -->
                <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-lg">Refacciones</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase">
                                <th class="py-2">Descripción</th>
                                <th class="py-2">Cant.</th>
                                <th class="py-2">P. unit.</th>
                                <th class="py-2">Desc.</th>
                                <th class="py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="part in parts" :key="part.id" class="border-t">
                                <td class="py-2">{{ part.description }}</td>
                                <td class="py-2">{{ part.quantity }}</td>
                                <td class="py-2">${{ Number(part.unit_price).toFixed(2) }}</td>
                                <td class="py-2">${{ Number(part.discount).toFixed(2) }}</td>
                                <td class="py-2 text-right">${{ Number(part.line_total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <form v-if="can.addItems" class="grid grid-cols-1 md:grid-cols-6 gap-3 border-t pt-4" @submit.prevent="submitPart">
                        <div class="md:col-span-2">
                            <InputLabel value="Catálogo" />
                            <select
                                v-model="partForm.part_catalog_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                @change="onPartSelect"
                            >
                                <option value="">Manual…</option>
                                <option v-for="part in partsCatalog" :key="part.id" :value="String(part.id)">
                                    {{ part.code }} — {{ part.description }}
                                </option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel value="Descripción" />
                            <TextInput v-model="partForm.description" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Cant." />
                            <TextInput v-model="partForm.quantity" type="number" step="0.01" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="P. unit." />
                            <TextInput v-model="partForm.unit_price" type="number" step="0.01" class="mt-1 block w-full" />
                        </div>
                        <div class="md:col-span-6">
                            <PrimaryButton :disabled="partForm.processing">Agregar refacción</PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Attachments -->
                <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-lg">Evidencias</h3>
                    <ul class="space-y-2 text-sm">
                        <li v-for="file in attachments" :key="file.id" class="flex justify-between border-b pb-2">
                            <span>{{ file.original_name }} <span class="text-gray-400">({{ file.mime_type }})</span></span>
                            <span class="text-gray-500">{{ file.uploaded_by?.name }}</span>
                        </li>
                        <li v-if="!attachments.length" class="text-gray-500">Sin evidencias.</li>
                    </ul>
                    <form v-if="can.attach" class="space-y-3" @submit.prevent="submitAttachment">
                        <input :key="fileInputKey" type="file" @change="onFileChange">
                        <InputError :message="attachmentForm.errors.file" />
                        <PrimaryButton :disabled="attachmentForm.processing || !attachmentForm.file">
                            Subir evidencia
                        </PrimaryButton>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
