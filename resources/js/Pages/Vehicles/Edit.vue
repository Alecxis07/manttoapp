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

interface VehicleProp {
    id: number;
    customer_id: number;
    vehicle_type_id: number;
    license_plate: string;
    vin?: string | null;
    economic_number?: string | null;
    brand: string;
    model: string;
    year: number;
    engine_type?: string | null;
    current_mileage: number;
    status: string;
    status_notes?: string | null;
}

interface Attachment {
    id: number;
    original_name: string;
}

const props = defineProps<{
    customer: NamedOption;
    vehicle: VehicleProp;
    customers: NamedOption[];
    vehicleTypes: NamedOption[];
    statuses: SelectOption[];
    yearRange?: { min?: number; max?: number } | null;
    attachments: Attachment[];
}>();

const form = useForm({
    customer_id: props.vehicle.customer_id,
    vehicle_type_id: props.vehicle.vehicle_type_id,
    license_plate: props.vehicle.license_plate,
    vin: props.vehicle.vin ?? '',
    economic_number: props.vehicle.economic_number ?? '',
    brand: props.vehicle.brand,
    model: props.vehicle.model,
    year: props.vehicle.year,
    engine_type: props.vehicle.engine_type ?? '',
    current_mileage: props.vehicle.current_mileage,
    status: props.vehicle.status,
    status_notes: props.vehicle.status_notes ?? '',
    attachments: [] as File[],
    _method: 'put',
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
    form.post(route('customers.vehicles.update', [props.customer.id, props.vehicle.id]), {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout title="Editar unidad">
        <PageHeader
            :title="`Editar ${vehicle.license_plate}`"
            subtitle="El kilometraje no puede disminuir. Los cambios de estatus quedan auditados."
            :breadcrumbs="[
                { title: 'Unidades', href: route('vehicles.index') },
                {
                    title: vehicle.license_plate,
                    href: route('customers.vehicles.show', [customer.id, vehicle.id]),
                },
                { title: 'Editar', disabled: true },
            ]"
        />

        <FormSection
            title="Datos de la unidad"
            description="Actualiza identificación, kilometraje y estatus."
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
                            label="VIN"
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
                            :min="vehicle.current_mileage"
                            required
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
                        <v-textarea
                            v-model="form.status_notes"
                            label="Observaciones de estatus"
                            rows="3"
                            :error-messages="form.errors.status_notes"
                        />
                    </v-col>
                    <v-col v-if="attachments?.length" cols="12">
                        <div class="text-caption text-medium-emphasis mb-2">Adjuntos actuales</div>
                        <v-list density="compact">
                            <v-list-item
                                v-for="file in attachments"
                                :key="file.id"
                                :title="file.original_name"
                                prepend-icon="mdi-paperclip"
                            />
                        </v-list>
                    </v-col>
                    <v-col cols="12">
                        <v-file-input
                            label="Agregar adjuntos"
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
                    save-text="Guardar cambios"
                    @cancel="router.visit(route('customers.vehicles.show', [customer.id, vehicle.id]))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
