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
    category: Object,
});

const form = useForm({
    code: props.category.code,
    name: props.category.name,
    description: props.category.description ?? '',
    is_active: props.category.is_active,
});

const submit = () => {
    form.put(route('service-categories.update', props.category.id));
};
</script>

<template>
    <AppLayout title="Editar categoría">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar categoría
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos de la categoría
                    </template>

                    <template #description>
                        Actualiza la categoría. Las inactivas no aparecen en selectores nuevos.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="code" value="Código" />
                            <TextInput id="code" v-model="form.code" type="text" class="mt-1 block w-full" required autofocus />
                            <InputError :message="form.errors.code" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Nombre" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="description" value="Descripción" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.is_active" name="is_active" />
                                <span class="ms-2 text-sm text-gray-600">Activa</span>
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
