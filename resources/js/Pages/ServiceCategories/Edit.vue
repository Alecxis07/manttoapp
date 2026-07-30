<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

const props = defineProps<{
    category: {
        id: number;
        code: string;
        name: string;
        description?: string | null;
        is_active: boolean;
    };
}>();

const form = useForm({
    code: props.category.code,
    name: props.category.name,
    description: props.category.description ?? '',
    is_active: props.category.is_active,
});

function submit(): void {
    form.put(route('service-categories.update', props.category.id));
}
</script>

<template>
    <AppLayout title="Editar categoría">
        <PageHeader
            title="Editar categoría"
            :subtitle="category.name"
            :breadcrumbs="[
                { title: 'Categorías', href: route('service-categories.index') },
                { title: category.name, href: route('service-categories.show', category.id) },
                { title: 'Editar', disabled: true },
            ]"
        />

        <FormSection
            title="Datos de la categoría"
            description="Las inactivas no aparecen en selectores nuevos."
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
                            v-model="form.name"
                            label="Nombre"
                            required
                            :error-messages="form.errors.name"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            v-model="form.description"
                            label="Descripción"
                            rows="3"
                            :error-messages="form.errors.description"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-checkbox
                            v-model="form.is_active"
                            label="Activa"
                            hide-details
                            :error-messages="form.errors.is_active"
                        />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Actualizar"
                    @cancel="router.visit(route('service-categories.show', category.id))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
