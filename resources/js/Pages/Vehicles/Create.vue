<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    customer: Object,
    customers: Array,
    vehicleTypes: Array,
    statuses: Array,
    yearRange: Object,
});

const form = useForm({
    customer_id: props.customer?.id ?? '',
    vehicle_type_id: props.vehicleTypes?.[0]?.id ?? '',
    license_plate: '',
    vin: '',
    economic_number: '',
    brand: '',
    model: '',
    year: props.yearRange?.max ? props.yearRange.max - 1 : new Date().getFullYear(),
    engine_type: '',
    current_mileage: 0,
    status: 'active',
    status_notes: '',
    attachments: [],
});

const onFilesChange = (event) => {
    form.attachments = Array.from(event.target.files || []);
};

const submit = () => {
    if (props.customer?.id) {
        form.post(route('customers.vehicles.store', props.customer.id), {
            forceFormData: true,
        });
        return;
    }

    form.post(route('vehicles.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Nueva unidad">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva unidad
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
                <FormSection @submitted="submit">
                    <template #title>
                        Identificación
                    </template>
                    <template #description>
                        Registra una unidad asociada a un cliente activo. Las placas se normalizan para unicidad.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="customer_id" value="Cliente" />
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                :disabled="!!customer"
                            >
                                <option value="" disabled>Seleccione un cliente</option>
                                <option
                                    v-for="option in customers"
                                    :key="option.id"
                                    :value="option.id"
                                >
                                    {{ option.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.customer_id" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="vehicle_type_id" value="Tipo de unidad" />
                            <select
                                id="vehicle_type_id"
                                v-model="form.vehicle_type_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option
                                    v-for="type in vehicleTypes"
                                    :key="type.id"
                                    :value="type.id"
                                >
                                    {{ type.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.vehicle_type_id" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="license_plate" value="Placas" />
                            <TextInput
                                id="license_plate"
                                v-model="form.license_plate"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.license_plate" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="vin" value="VIN (opcional)" />
                            <TextInput
                                id="vin"
                                v-model="form.vin"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.vin" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="economic_number" value="Número económico" />
                            <TextInput
                                id="economic_number"
                                v-model="form.economic_number"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.economic_number" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="brand" value="Marca" />
                            <TextInput id="brand" v-model="form.brand" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.brand" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="model" value="Modelo" />
                            <TextInput id="model" v-model="form.model" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.model" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <InputLabel for="year" value="Año" />
                            <TextInput
                                id="year"
                                v-model="form.year"
                                type="number"
                                class="mt-1 block w-full"
                                :min="yearRange?.min"
                                :max="yearRange?.max"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.year" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="engine_type" value="Tipo de motor" />
                            <TextInput id="engine_type" v-model="form.engine_type" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.engine_type" />
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <InputLabel for="current_mileage" value="Kilometraje" />
                            <TextInput
                                id="current_mileage"
                                v-model="form.current_mileage"
                                type="number"
                                class="mt-1 block w-full"
                                min="0"
                            />
                            <InputError class="mt-2" :message="form.errors.current_mileage" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="status" value="Estatus" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option
                                    v-for="option in statuses"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="attachments" value="Adjuntos" />
                            <input
                                id="attachments"
                                type="file"
                                multiple
                                class="mt-1 block w-full text-sm"
                                accept=".jpg,.jpeg,.png,.pdf,.webp"
                                @change="onFilesChange"
                            >
                            <InputError class="mt-2" :message="form.errors.attachments" />
                        </div>
                    </template>

                    <template #actions>
                        <Link :href="route('vehicles.index')">
                            <SecondaryButton type="button">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton class="ms-3" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
