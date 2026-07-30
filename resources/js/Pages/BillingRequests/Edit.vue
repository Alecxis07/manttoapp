<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

interface SelectOption {
    value: string | number;
    label: string;
}

interface BillingRequestEdit {
    id: number;
    folio: string;
    customer_fiscal_profile_id?: number | null;
    payment_method_code?: string | null;
    payment_form_code?: string | null;
    notes?: string | null;
}

const props = defineProps<{
    billingRequest: BillingRequestEdit;
    fiscalProfiles: SelectOption[];
    paymentMethods: SelectOption[];
    paymentForms: SelectOption[];
}>();

const form = useForm({
    customer_fiscal_profile_id: props.billingRequest.customer_fiscal_profile_id ?? '',
    payment_method_code: props.billingRequest.payment_method_code ?? 'PUE',
    payment_form_code: props.billingRequest.payment_form_code ?? '03',
    notes: props.billingRequest.notes ?? '',
});

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            customer_fiscal_profile_id: data.customer_fiscal_profile_id || null,
        }))
        .put(route('billing-requests.update', props.billingRequest.id));
}

const fiscalItems = [
    { title: 'Mantener snapshot actual', value: '' },
    ...props.fiscalProfiles.map((option) => ({
        title: option.label,
        value: option.value,
    })),
];

const paymentMethodItems = props.paymentMethods.map((option) => ({
    title: option.label,
    value: option.value,
}));

const paymentFormItems = props.paymentForms.map((option) => ({
    title: option.label,
    value: option.value,
}));
</script>

<template>
    <AppLayout :title="`Editar ${billingRequest.folio}`">
        <PageHeader
            :title="`Editar ${billingRequest.folio}`"
            subtitle="Actualiza perfil fiscal y datos de pago."
        />

        <FormSection
            title="Datos fiscales y pago"
            description="Al seleccionar un perfil se regenera el snapshot fiscal de la solicitud."
            @submitted="submit"
        >
            <template #form>
                <v-row>
                    <v-col cols="12">
                        <v-select
                            v-model="form.customer_fiscal_profile_id"
                            :items="fiscalItems"
                            label="Perfil fiscal"
                            :error-messages="form.errors.customer_fiscal_profile_id"
                            hint="Al seleccionar un perfil se regenera el snapshot fiscal de la solicitud."
                            persistent-hint
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.payment_method_code"
                            :items="paymentMethodItems"
                            label="Método de pago (SAT)"
                            :error-messages="form.errors.payment_method_code"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.payment_form_code"
                            :items="paymentFormItems"
                            label="Forma de pago (SAT)"
                            :error-messages="form.errors.payment_form_code"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            v-model="form.notes"
                            label="Observaciones"
                            :error-messages="form.errors.notes"
                        />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Guardar"
                    @cancel="router.visit(route('billing-requests.show', billingRequest.id))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
