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

const props = defineProps<{
    orders: SelectOption[];
    quotations: SelectOption[];
    paymentMethods: SelectOption[];
    paymentForms: SelectOption[];
    prefill?: {
        maintenance_order_id?: number | string;
        quotation_id?: number | string;
    };
    fiscalProfiles?: SelectOption[];
}>();

const form = useForm({
    maintenance_order_id: props.prefill?.maintenance_order_id ?? '',
    quotation_id: props.prefill?.quotation_id ?? '',
    customer_fiscal_profile_id: '',
    payment_method_code: 'PUE',
    payment_form_code: '03',
    notes: '',
});

function clearOtherOrigin(field: 'order' | 'quotation'): void {
    if (field === 'order' && form.maintenance_order_id) {
        form.quotation_id = '';
    }

    if (field === 'quotation' && form.quotation_id) {
        form.maintenance_order_id = '';
    }
}

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            maintenance_order_id: data.maintenance_order_id || null,
            quotation_id: data.quotation_id || null,
            customer_fiscal_profile_id: data.customer_fiscal_profile_id || null,
        }))
        .post(route('billing-requests.store'));
}

const orderItems = [
    { title: '— Sin orden —', value: '' },
    ...props.orders.map((option) => ({ title: option.label, value: option.value })),
];

const quotationItems = [
    { title: '— Sin cotización —', value: '' },
    ...props.quotations.map((option) => ({ title: option.label, value: option.value })),
];

const fiscalItems = [
    { title: 'Perfil default del cliente', value: '' },
    ...(props.fiscalProfiles ?? []).map((option) => ({
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
    <AppLayout title="Nueva solicitud de facturación">
        <PageHeader
            title="Nueva solicitud de facturación"
            subtitle="Elige un origen (orden o cotización) y datos de pago SAT."
        />

        <FormSection
            title="Origen y pago"
            description="Solo uno de los orígenes debe seleccionarse."
            @submitted="submit"
        >
            <template #form>
                <v-row>
                    <v-col cols="12">
                        <v-select
                            v-model="form.maintenance_order_id"
                            :items="orderItems"
                            label="Orden de origen"
                            :error-messages="form.errors.maintenance_order_id || (form.errors as Record<string, string>).origin"
                            @update:model-value="clearOtherOrigin('order')"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-select
                            v-model="form.quotation_id"
                            :items="quotationItems"
                            label="Cotización de origen"
                            :error-messages="form.errors.quotation_id"
                            @update:model-value="clearOtherOrigin('quotation')"
                        />
                    </v-col>
                    <v-col v-if="fiscalProfiles?.length" cols="12">
                        <v-select
                            v-model="form.customer_fiscal_profile_id"
                            :items="fiscalItems"
                            label="Perfil fiscal"
                            :error-messages="form.errors.customer_fiscal_profile_id"
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
                    save-text="Crear solicitud"
                    @cancel="router.visit(route('billing-requests.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
