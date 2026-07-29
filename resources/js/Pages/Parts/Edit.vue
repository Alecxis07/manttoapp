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
    part: Object,
    categories: Array,
    types: Array,
});

const form = useForm({
    code: props.part.code,
    description: props.part.description,
    type: props.part.type,
    service_category_id: props.part.service_category_id ?? '',
    base_price: props.part.base_price,
    unit_of_measure: props.part.unit_of_measure,
    is_active: props.part.is_active,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        service_category_id: data.service_category_id || null,
    })).put(route('part-catalog.update', props.part.id));
};
</script>

<template>
    <AppLayout title="Editar concepto">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar concepto / refacción
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos del concepto
                    </template>

                    <template #description>
                        Precio base referencial (RN-CAT-002). Los inactivos no son seleccionables en operaciones nuevas.
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
                            <InputLabel for="type" value="Tipo" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option v-for="typeOption in types" :key="typeOption.value" :value="typeOption.value">
                                    {{ typeOption.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="service_category_id" value="Categoría (opcional)" />
                            <select
                                id="service_category_id"
                                v-model="form.service_category_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Sin categoría</option>
                                <option v-for="category in categories" :key="category.value" :value="category.value">
                                    {{ category.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.service_category_id" class="mt-2" />
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
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.is_active" name="is_active" />
                                <span class="ms-2 text-sm text-gray-600">Activo</span>
                            </label>
                            <InputError :message="form.errors.is_active" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Actualizar
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
