<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

const form = useForm({
    code: '',
    name: '',
    description: '',
    is_active: true,
});

function submit(): void {
    form.post(route('service-categories.store'));
}
</script>

<template>
    <AppLayout title="Nueva categoría">
        <PageHeader
            title="Nueva categoría"
            subtitle="Agrupa servicios para filtros y reportes."
            :breadcrumbs="[
                { title: 'Categorías', href: route('service-categories.index') },
                { title: 'Nueva', disabled: true },
            ]"
        />

        <FormSection
            title="Datos de la categoría"
            description="Código y nombre únicos del catálogo."
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
                        />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Guardar"
                    @cancel="router.visit(route('service-categories.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
