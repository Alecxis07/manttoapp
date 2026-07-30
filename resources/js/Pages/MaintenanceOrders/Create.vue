<script setup lang="ts">
import { watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface CustomerOption {
    id: number;
    name: string;
}

interface VehicleOption {
    id: number;
    license_plate: string;
    brand?: string;
    model?: string;
}

interface TechnicianOption {
    id: number;
    name: string;
}

const props = defineProps<{
    customers: CustomerOption[];
    types: SelectOption[];
    technicians: TechnicianOption[];
    prefill?: {
        customer_id?: number | string;
        vehicle_id?: number | string;
    };
    vehicles?: VehicleOption[];
}>();

const form = useForm({
    customer_id: props.prefill?.customer_id ? String(props.prefill.customer_id) : '',
    vehicle_id: props.prefill?.vehicle_id ? String(props.prefill.vehicle_id) : '',
    type: 'corrective',
    reason: '',
    mileage: 0,
    received_at: new Date().toISOString().slice(0, 16),
    assigned_user_id: '',
    technical_notes: '',
});

const vehicleOptions = props.vehicles ?? [];

watch(
    () => form.customer_id,
    (customerId) => {
        if (!customerId) {
            form.vehicle_id = '';
            return;
        }

        window.location.href = route('maintenance-orders.create', { customer_id: customerId });
    },
);

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            customer_id: Number(data.customer_id),
            vehicle_id: Number(data.vehicle_id),
            mileage: Number(data.mileage),
            assigned_user_id: data.assigned_user_id ? Number(data.assigned_user_id) : null,
        }))
        .post(route('maintenance-orders.store'));
}

const customerItems = props.customers.map((customer) => ({
    title: customer.name,
    value: String(customer.id),
}));

const vehicleItems = vehicleOptions.map((vehicle) => ({
    title: `${vehicle.license_plate} — ${vehicle.brand ?? ''} ${vehicle.model ?? ''}`.trim(),
    value: String(vehicle.id),
}));

const typeItems = props.types.map((option) => ({
    title: option.label,
    value: option.value,
}));

const technicianItems = [
    { title: 'Sin asignar', value: '' },
    ...props.technicians.map((user) => ({
        title: user.name,
        value: String(user.id),
    })),
];
</script>

<template>
    <AppLayout title="Nueva orden">
        <PageHeader
            title="Nueva orden de mantenimiento"
            subtitle="Asocia cliente, unidad y motivo. El folio se genera automáticamente."
        />

        <FormSection
            title="Recepción"
            description="Datos de ingreso de la unidad al taller."
            @submitted="submit"
        >
            <template #form>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.customer_id"
                            :items="customerItems"
                            label="Cliente"
                            :error-messages="form.errors.customer_id"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.vehicle_id"
                            :items="vehicleItems"
                            label="Unidad"
                            :error-messages="form.errors.vehicle_id"
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
                            v-model.number="form.mileage"
                            type="number"
                            label="Kilometraje"
                            min="0"
                            :error-messages="form.errors.mileage"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.received_at"
                            type="datetime-local"
                            label="Fecha de recepción"
                            :error-messages="form.errors.received_at"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.assigned_user_id"
                            :items="technicianItems"
                            label="Responsable"
                            :error-messages="form.errors.assigned_user_id"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            v-model="form.reason"
                            label="Motivo"
                            rows="3"
                            :error-messages="form.errors.reason"
                        />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Crear orden"
                    @cancel="router.visit(route('maintenance-orders.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
