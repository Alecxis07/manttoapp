<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    customers: Array,
    types: Array,
    technicians: Array,
    prefill: Object,
    vehicles: Array,
});

const form = useForm({
    customer_id: props.prefill?.customer_id ? String(props.prefill.customer_id) : '',
    vehicle_id: props.prefill?.vehicle_id ? String(props.prefill.vehicle_id) : '',
    type: 'corrective',
    reason: '',
    mileage: 0,
    received_at: new Date().toISOString().slice(0, 16),
    assigned_user_id: '',
    technical_notes: '',
});

const vehicleOptions = props.vehicles ?? [];

watch(() => form.customer_id, (customerId) => {
    if (!customerId) {
        form.vehicle_id = '';
        return;
    }

    window.location = route('maintenance-orders.create', { customer_id: customerId });
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        customer_id: Number(data.customer_id),
        vehicle_id: Number(data.vehicle_id),
        mileage: Number(data.mileage),
        assigned_user_id: data.assigned_user_id ? Number(data.assigned_user_id) : null,
    })).post(route('maintenance-orders.store'));
};
</script>

<template>
    <AppLayout title="Nueva orden">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva orden de mantenimiento
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>
                        Recepción
                    </template>
                    <template #description>
                        Asocia cliente, unidad y motivo. El folio se genera automáticamente.
                    </template>
                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="customer_id" value="Cliente" />
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Seleccione…</option>
                                <option v-for="customer in customers" :key="customer.id" :value="String(customer.id)">
                                    {{ customer.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.customer_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="vehicle_id" value="Unidad" />
                            <select
                                id="vehicle_id"
                                v-model="form.vehicle_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Seleccione…</option>
                                <option v-for="vehicle in vehicleOptions" :key="vehicle.id" :value="String(vehicle.id)">
                                    {{ vehicle.license_plate }} — {{ vehicle.brand }} {{ vehicle.model }}
                                </option>
                            </select>
                            <InputError :message="form.errors.vehicle_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Tipo" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option v-for="option in types" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="mileage" value="Kilometraje" />
                            <TextInput id="mileage" v-model="form.mileage" type="number" class="mt-1 block w-full" min="0" />
                            <InputError :message="form.errors.mileage" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="received_at" value="Fecha de recepción" />
                            <TextInput id="received_at" v-model="form.received_at" type="datetime-local" class="mt-1 block w-full" />
                            <InputError :message="form.errors.received_at" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="assigned_user_id" value="Responsable" />
                            <select
                                id="assigned_user_id"
                                v-model="form.assigned_user_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Sin asignar</option>
                                <option v-for="user in technicians" :key="user.id" :value="String(user.id)">
                                    {{ user.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.assigned_user_id" class="mt-2" />
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="reason" value="Motivo" />
                            <textarea
                                id="reason"
                                v-model="form.reason"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                            <InputError :message="form.errors.reason" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <Link :href="route('maintenance-orders.index')">
                            <SecondaryButton type="button">
                                Cancelar
                            </SecondaryButton>
                        </Link>
                        <PrimaryButton class="ms-3" :disabled="form.processing">
                            Crear orden
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
