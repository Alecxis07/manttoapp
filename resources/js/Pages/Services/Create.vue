<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    categories: Array,
    types: Array,
});

const form = useForm({
    code: '',
    description: '',
    service_category_id: props.categories[0]?.value ?? '',
    type: props.types[0]?.value ?? 'preventive',
    base_price: '',
    unit_of_measure: 'servicio',
    estimated_minutes: '',
    is_active: true,
});

const submit = () => {
    form.post(route('service-catalog.store'));
};
</script>

<template>
    <AppLayout title="Nuevo servicio">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo servicio
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos del servicio
                    </template>

                    <template #description>
                        El precio base es referencial (RN-CAT-002): se sugiere al armar partidas y no altera documentos históricos.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="code" value="Código" />
                            <TextInput id="code" v-model="form.code" type="text" class="mt-1 block w-full" required autofocus />
                            <InputError :message="form.errors.code" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="description" value="Descripción" />
                            <TextInput id="description" v-model="form.description" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="service_category_id" value="Categoría" />
                            <select
                                id="service_category_id"
                                v-model="form.service_category_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option v-for="category in categories" :key="category.value" :value="category.value">
                                    {{ category.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.service_category_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Tipo" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option v-for="type in types" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="base_price" value="Precio base (referencial)" />
                            <TextInput id="base_price" v-model="form.base_price" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.base_price" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="unit_of_measure" value="Unidad de medida" />
                            <TextInput id="unit_of_measure" v-model="form.unit_of_measure" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.unit_of_measure" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="estimated_minutes" value="Tiempo estimado (minutos)" />
                            <TextInput id="estimated_minutes" v-model="form.estimated_minutes" type="number" min="0" class="mt-1 block w-full" />
                            <InputError :message="form.errors.estimated_minutes" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.is_active" name="is_active" />
                                <span class="ms-2 text-sm text-gray-600">Activo</span>
                            </label>
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
