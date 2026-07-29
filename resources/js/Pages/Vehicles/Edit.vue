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
    vehicle: Object,
    customers: Array,
    vehicleTypes: Array,
    statuses: Array,
    yearRange: Object,
    attachments: Array,
});

const form = useForm({
    customer_id: props.vehicle.customer_id,
    vehicle_type_id: props.vehicle.vehicle_type_id,
    license_plate: props.vehicle.license_plate,
    vin: props.vehicle.vin ?? '',
    economic_number: props.vehicle.economic_number ?? '',
    brand: props.vehicle.brand,
    model: props.vehicle.model,
    year: props.vehicle.year,
    engine_type: props.vehicle.engine_type ?? '',
    current_mileage: props.vehicle.current_mileage,
    status: props.vehicle.status,
    status_notes: props.vehicle.status_notes ?? '',
    attachments: [],
    _method: 'put',
});

const onFilesChange = (event) => {
    form.attachments = Array.from(event.target.files || []);
};

const submit = () => {
    form.post(route('customers.vehicles.update', [props.customer.id, props.vehicle.id]), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Editar unidad">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar {{ vehicle.license_plate }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos de la unidad
                    </template>
                    <template #description>
                        El kilometraje no puede disminuir. Los cambios de estatus quedan auditados.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="customer_id" value="Cliente" />
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
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
                            <TextInput id="license_plate" v-model="form.license_plate" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.license_plate" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="vin" value="VIN" />
                            <TextInput id="vin" v-model="form.vin" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.vin" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="economic_number" value="Número económico" />
                            <TextInput id="economic_number" v-model="form.economic_number" type="text" class="mt-1 block w-full" />
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
                                :min="vehicle.current_mileage"
                                required
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
                            <InputLabel for="status_notes" value="Observaciones de estatus" />
                            <textarea
                                id="status_notes"
                                v-model="form.status_notes"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                            <InputError class="mt-2" :message="form.errors.status_notes" />
                        </div>

                        <div v-if="attachments?.length" class="col-span-6">
                            <InputLabel value="Adjuntos actuales" />
                            <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                                <li v-for="file in attachments" :key="file.id">
                                    {{ file.original_name }}
                                </li>
                            </ul>
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="attachments" value="Agregar adjuntos" />
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
                        <Link :href="route('customers.vehicles.show', [customer.id, vehicle.id])">
                            <SecondaryButton type="button">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton class="ms-3" :disabled="form.processing">
                            Guardar cambios
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
