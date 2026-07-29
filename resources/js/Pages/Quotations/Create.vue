<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    customers: Array,
    itemTypes: Array,
    services: Array,
    parts: Array,
    prefill: Object,
});

const vehicles = ref([]);

const form = useForm({
    customer_id: props.prefill?.customer_id ?? '',
    vehicle_id: props.prefill?.vehicle_id ?? '',
    valid_until: '',
    commercial_terms: '',
    items: [
        {
            item_type: 'service',
            service_catalog_id: '',
            part_catalog_id: '',
            code: '',
            description: '',
            quantity: 1,
            unit_price: 0,
            discount: 0,
            notes: '',
        },
    ],
});

const loadVehicles = async (customerId) => {
    if (!customerId) {
        vehicles.value = [];
        return;
    }

    const response = await fetch(route('quotations.customer-vehicles', customerId), {
        headers: { Accept: 'application/json' },
    });
    const payload = await response.json();
    vehicles.value = payload.data ?? [];
};

watch(() => form.customer_id, (value) => {
    form.vehicle_id = '';
    loadVehicles(value);
}, { immediate: true });

const addItem = () => {
    form.items.push({
        item_type: 'service',
        service_catalog_id: '',
        part_catalog_id: '',
        code: '',
        description: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        notes: '',
    });
};

const removeItem = (index) => {
    if (form.items.length === 1) {
        return;
    }
    form.items.splice(index, 1);
};

const onCatalogChange = (index) => {
    const item = form.items[index];
    if (item.item_type === 'service') {
        const service = props.services.find((row) => String(row.value) === String(item.service_catalog_id));
        if (service) {
            item.code = service.code;
            item.description = service.description;
            item.unit_price = Number(service.base_price);
            item.part_catalog_id = '';
        }
    } else {
        const part = props.parts.find((row) => String(row.value) === String(item.part_catalog_id));
        if (part) {
            item.code = part.code;
            item.description = part.description;
            item.unit_price = Number(part.base_price);
            item.service_catalog_id = '';
        }
    }
};

const estimated = computed(() => {
    let subtotal = 0;
    let discount = 0;
    form.items.forEach((item) => {
        subtotal += Number(item.quantity || 0) * Number(item.unit_price || 0);
        discount += Number(item.discount || 0);
    });
    const taxable = Math.max(subtotal - discount, 0);
    const tax = taxable * 0.16;
    return {
        subtotal: subtotal.toFixed(2),
        discount: discount.toFixed(2),
        tax: tax.toFixed(2),
        total: (taxable + tax).toFixed(2),
    };
});

const submit = () => {
    form.post(route('quotations.store'));
};
</script>

<template>
    <AppLayout title="Nueva cotización">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva cotización
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos de la cotización
                    </template>
                    <template #description>
                        Los totales se recalculan en el servidor (RN-GEN-002). Los precios de catálogo son referenciales.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="customer_id" value="Cliente" />
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Seleccione…</option>
                                <option v-for="customer in customers" :key="customer.value" :value="customer.value">
                                    {{ customer.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.customer_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="vehicle_id" value="Unidad" />
                            <select
                                id="vehicle_id"
                                v-model="form.vehicle_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Seleccione…</option>
                                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                                    {{ vehicle.license_plate }} — {{ vehicle.brand }} {{ vehicle.model }}
                                </option>
                            </select>
                            <InputError :message="form.errors.vehicle_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="valid_until" value="Vigencia" />
                            <TextInput id="valid_until" v-model="form.valid_until" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.valid_until" class="mt-2" />
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="commercial_terms" value="Condiciones comerciales" />
                            <textarea
                                id="commercial_terms"
                                v-model="form.commercial_terms"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                            <InputError :message="form.errors.commercial_terms" class="mt-2" />
                        </div>

                        <div class="col-span-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900">Partidas</h3>
                                <SecondaryButton type="button" @click="addItem">Agregar partida</SecondaryButton>
                            </div>

                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="border border-gray-200 rounded-md p-4 space-y-3"
                            >
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div>
                                        <InputLabel :value="'Tipo'" />
                                        <select
                                            v-model="item.item_type"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            @change="onCatalogChange(index)"
                                        >
                                            <option v-for="type in itemTypes" :key="type.value" :value="type.value">
                                                {{ type.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <InputLabel :value="item.item_type === 'service' ? 'Servicio' : 'Refacción'" />
                                        <select
                                            v-if="item.item_type === 'service'"
                                            v-model="item.service_catalog_id"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            @change="onCatalogChange(index)"
                                        >
                                            <option value="">Seleccione…</option>
                                            <option v-for="service in services" :key="service.value" :value="service.value">
                                                {{ service.label }}
                                            </option>
                                        </select>
                                        <select
                                            v-else
                                            v-model="item.part_catalog_id"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            @change="onCatalogChange(index)"
                                        >
                                            <option value="">Seleccione…</option>
                                            <option v-for="part in parts" :key="part.value" :value="part.value">
                                                {{ part.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="flex items-end">
                                        <SecondaryButton type="button" @click="removeItem(index)">Quitar</SecondaryButton>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div>
                                        <InputLabel value="Cantidad" />
                                        <TextInput v-model="item.quantity" type="number" step="0.01" min="0.01" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <InputLabel value="Precio unitario" />
                                        <TextInput v-model="item.unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <InputLabel value="Descuento" />
                                        <TextInput v-model="item.discount" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <InputLabel value="Descripción" />
                                        <TextInput v-model="item.description" type="text" class="mt-1 block w-full" />
                                    </div>
                                </div>
                                <InputError :message="form.errors[`items.${index}.description`]" />
                            </div>
                            <InputError :message="form.errors.items" />
                        </div>

                        <div class="col-span-6 text-sm text-gray-700 space-y-1">
                            <div>Estimado cliente — Subtotal: ${{ estimated.subtotal }}</div>
                            <div>Descuento: ${{ estimated.discount }} · IVA: ${{ estimated.tax }} · Total: ${{ estimated.total }}</div>
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :disabled="form.processing">
                            Guardar borrador
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
