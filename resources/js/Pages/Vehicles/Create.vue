<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface NamedOption {
    id: number;
    name: string;
}

const props = defineProps<{
    customer?: NamedOption | null;
    customers: NamedOption[];
    vehicleTypes: NamedOption[];
    statuses: SelectOption[];
    yearRange?: { min?: number; max?: number } | null;
}>();

const form = useForm({
    customer_id: props.customer?.id ?? ('' as number | ''),
    vehicle_type_id: props.vehicleTypes?.[0]?.id ?? ('' as number | ''),
    license_plate: '',
    vin: '',
    economic_number: '',
    brand: '',
    model: '',
    year: props.yearRange?.max ? props.yearRange.max - 1 : new Date().getFullYear(),
    engine_type: '',
    current_mileage: 0,
    status: 'active',
    status_notes: '',
    attachments: [] as File[],
});

const customerItems = computed(() =>
    props.customers.map((option) => ({ value: option.id, title: option.name })),
);

const typeItems = computed(() =>
    props.vehicleTypes.map((type) => ({ value: type.id, title: type.name })),
);

const statusItems = computed(() =>
    props.statuses.map((option) => ({ value: option.value, title: option.label })),
);

function onFilesChange(files: File[] | File | null): void {
    if (!files) {
        form.attachments = [];
        return;
    }

    form.attachments = Array.isArray(files) ? files : [files];
}

function submit(): void {
    if (props.customer?.id) {
        form.post(route('customers.vehicles.store', props.customer.id), {
            forceFormData: true,
        });
        return;
    }

    form.post(route('vehicles.store'), {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout title="Nueva unidad">
        <PageHeader
            title="Nueva unidad"
            subtitle="Registra una unidad asociada a un cliente activo."
            :breadcrumbs="[
                { title: 'Unidades', href: route('vehicles.index') },
                { title: 'Nueva', disabled: true },
            ]"
        />

        <FormSection
            title="Identificación"
            description="Las placas se normalizan para unicidad."
            @submitted="submit"
        >
            <template #form>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.customer_id"
                            :items="customerItems"
                            label="Cliente"
                            :disabled="!!customer"
                            :error-messages="form.errors.customer_id"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.vehicle_type_id"
                            :items="typeItems"
                            label="Tipo de unidad"
                            :error-messages="form.errors.vehicle_type_id"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.license_plate"
                            label="Placas"
                            required
                            :error-messages="form.errors.license_plate"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.vin"
                            label="VIN (opcional)"
                            :error-messages="form.errors.vin"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.economic_number"
                            label="Número económico"
                            :error-messages="form.errors.economic_number"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.brand"
                            label="Marca"
                            required
                            :error-messages="form.errors.brand"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.model"
                            label="Modelo"
                            required
                            :error-messages="form.errors.model"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            v-model.number="form.year"
                            type="number"
                            label="Año"
                            :min="yearRange?.min"
                            :max="yearRange?.max"
                            required
                            :error-messages="form.errors.year"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            v-model.number="form.current_mileage"
                            type="number"
                            label="Kilometraje"
                            min="0"
                            :error-messages="form.errors.current_mileage"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.engine_type"
                            label="Tipo de motor"
                            :error-messages="form.errors.engine_type"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.status"
                            :items="statusItems"
                            label="Estatus"
                            :error-messages="form.errors.status"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-file-input
                            label="Adjuntos"
                            multiple
                            accept=".jpg,.jpeg,.png,.pdf,.webp"
                            prepend-icon=""
                            prepend-inner-icon="mdi-paperclip"
                            :error-messages="form.errors.attachments"
                            @update:model-value="onFilesChange"
                        />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Guardar"
                    @cancel="router.visit(route('vehicles.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
