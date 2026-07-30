<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

interface SelectOption {
    value: string | number;
    label: string;
}

const props = defineProps<{
    categories: SelectOption[];
    types: SelectOption[];
}>();

const form = useForm({
    code: '',
    description: '',
    service_category_id: props.categories[0]?.value ?? '',
    type: props.types[0]?.value ?? 'preventive',
    base_price: '',
    unit_of_measure: 'servicio',
    estimated_minutes: '' as string | number,
    is_active: true,
});

const categoryItems = computed(() =>
    props.categories.map((category) => ({ value: category.value, title: category.label })),
);

const typeItems = computed(() =>
    props.types.map((type) => ({ value: type.value, title: type.label })),
);

function submit(): void {
    form.post(route('service-catalog.store'));
}
</script>

<template>
    <AppLayout title="Nuevo servicio">
        <PageHeader
            title="Nuevo servicio"
            subtitle="El precio base es referencial y no altera documentos históricos."
            :breadcrumbs="[
                { title: 'Servicios', href: route('service-catalog.index') },
                { title: 'Nuevo', disabled: true },
            ]"
        />

        <FormSection
            title="Datos del servicio"
            description="Precio base referencial (RN-CAT-002)."
            @submitted="submit"
        >
            <template #form>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.code"
                            label="Código"
                            required
                            autofocus
                            :error-messages="form.errors.code"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.description"
                            label="Descripción"
                            required
                            :error-messages="form.errors.description"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.service_category_id"
                            :items="categoryItems"
                            label="Categoría"
                            :error-messages="form.errors.service_category_id"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.type"
                            :items="typeItems"
                            label="Tipo"
                            :error-messages="form.errors.type"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.base_price"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Precio base (referencial)"
                            required
                            :error-messages="form.errors.base_price"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.unit_of_measure"
                            label="Unidad de medida"
                            required
                            :error-messages="form.errors.unit_of_measure"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.estimated_minutes"
                            type="number"
                            min="0"
                            label="Tiempo estimado (minutos)"
                            :error-messages="form.errors.estimated_minutes"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-checkbox v-model="form.is_active" label="Activo" hide-details />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Guardar"
                    @cancel="router.visit(route('service-catalog.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
