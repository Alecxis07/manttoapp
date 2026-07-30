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
    type: props.types[0]?.value ?? 'part',
    service_category_id: '' as string | number,
    base_price: '',
    unit_of_measure: 'pieza',
    is_active: true,
});

const typeItems = computed(() =>
    props.types.map((type) => ({ value: type.value, title: type.label })),
);

const categoryItems = computed(() => [
    { value: '', title: 'Sin categoría' },
    ...props.categories.map((category) => ({ value: category.value, title: category.label })),
]);

function submit(): void {
    form.transform((data) => ({
        ...data,
        service_category_id: data.service_category_id || null,
    })).post(route('part-catalog.store'));
}
</script>

<template>
    <AppLayout title="Nuevo concepto">
        <PageHeader
            title="Nuevo concepto / refacción"
            subtitle="Precio base referencial. La categoría es opcional."
            :breadcrumbs="[
                { title: 'Refacciones', href: route('part-catalog.index') },
                { title: 'Nuevo', disabled: true },
            ]"
        />

        <FormSection
            title="Datos del concepto"
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
                            v-model="form.type"
                            :items="typeItems"
                            label="Tipo"
                            :error-messages="form.errors.type"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.service_category_id"
                            :items="categoryItems"
                            label="Categoría (opcional)"
                            :error-messages="form.errors.service_category_id"
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
                    <v-col cols="12">
                        <v-checkbox v-model="form.is_active" label="Activo" hide-details />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Guardar"
                    @cancel="router.visit(route('part-catalog.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
