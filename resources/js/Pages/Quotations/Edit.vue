<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import MoneyText from '@/Components/Ui/MoneyText.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';

interface SelectOption {
    value: string | number;
    label: string;
    code?: string;
    description?: string;
    base_price?: number | string;
}

interface VehicleOption {
    id: number;
    license_plate: string;
    brand?: string;
    model?: string;
}

interface QuotationItemForm {
    item_type: string;
    service_catalog_id: string | number;
    part_catalog_id: string | number;
    code: string;
    description: string;
    quantity: number;
    unit_price: number;
    discount: number;
    notes: string;
}

interface QuotationEdit {
    id: number;
    folio: string;
    version: number;
    customer_id: number;
    vehicle_id: number;
    valid_until?: string | null;
    commercial_terms?: string | null;
    items: Array<{
        item_type: string;
        service_catalog_id?: number | null;
        part_catalog_id?: number | null;
        code?: string | null;
        description: string;
        quantity: number;
        unit_price: number;
        discount: number;
        notes?: string | null;
    }>;
}

const props = defineProps<{
    quotation: QuotationEdit;
    customers: SelectOption[];
    itemTypes: SelectOption[];
    services: SelectOption[];
    parts: SelectOption[];
}>();

const vehicles = ref<VehicleOption[]>([]);

const form = useForm({
    customer_id: props.quotation.customer_id,
    vehicle_id: props.quotation.vehicle_id,
    valid_until: props.quotation.valid_until ?? '',
    commercial_terms: props.quotation.commercial_terms ?? '',
    items: props.quotation.items.map(
        (item): QuotationItemForm => ({
            item_type: item.item_type,
            service_catalog_id: item.service_catalog_id ?? '',
            part_catalog_id: item.part_catalog_id ?? '',
            code: item.code ?? '',
            description: item.description,
            quantity: item.quantity,
            unit_price: item.unit_price,
            discount: item.discount,
            notes: item.notes ?? '',
        }),
    ),
});

async function loadVehicles(customerId: string | number | null | undefined): Promise<void> {
    if (!customerId) {
        vehicles.value = [];
        return;
    }

    const response = await fetch(route('quotations.customer-vehicles', customerId), {
        headers: { Accept: 'application/json' },
    });
    const payload = await response.json();
    vehicles.value = payload.data ?? [];
}

watch(
    () => form.customer_id,
    (value, oldValue) => {
        if (oldValue !== undefined && value !== oldValue) {
            form.vehicle_id = 0;
        }

        void loadVehicles(value);
    },
    { immediate: true },
);

function addItem(): void {
    form.items.push({
        item_type: 'service',
        service_catalog_id: '',
        part_catalog_id: '',
        code: '',
        description: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        notes: '',
    });
}

function removeItem(index: number): void {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(index, 1);
}

function onCatalogChange(index: number): void {
    const item = form.items[index];

    if (item.item_type === 'service') {
        const service = props.services.find(
            (row) => String(row.value) === String(item.service_catalog_id),
        );

        if (service) {
            item.code = service.code ?? '';
            item.description = service.description ?? '';
            item.unit_price = Number(service.base_price);
            item.part_catalog_id = '';
        }
    } else {
        const part = props.parts.find(
            (row) => String(row.value) === String(item.part_catalog_id),
        );

        if (part) {
            item.code = part.code ?? '';
            item.description = part.description ?? '';
            item.unit_price = Number(part.base_price);
            item.service_catalog_id = '';
        }
    }
}

const estimated = computed(() => {
    let subtotal = 0;
    let discount = 0;

    form.items.forEach((item) => {
        subtotal += Number(item.quantity || 0) * Number(item.unit_price || 0);
        discount += Number(item.discount || 0);
    });

    const taxable = Math.max(subtotal - discount, 0);
    const tax = taxable * 0.16;

    return {
        total: taxable + tax,
    };
});

function submit(): void {
    form.put(route('quotations.update', props.quotation.id));
}

const customerItems = props.customers.map((customer) => ({
    title: customer.label,
    value: customer.value,
}));

const itemTypeItems = props.itemTypes.map((type) => ({
    title: type.label,
    value: type.value,
}));

const serviceItems = props.services.map((service) => ({
    title: service.label,
    value: service.value,
}));

const partItems = props.parts.map((part) => ({
    title: part.label,
    value: part.value,
}));

const vehicleItems = computed(() =>
    vehicles.value.map((vehicle) => ({
        title: `${vehicle.license_plate} — ${vehicle.brand ?? ''} ${vehicle.model ?? ''}`.trim(),
        value: vehicle.id,
    })),
);
</script>

<template>
    <AppLayout :title="`Editar ${quotation.folio}`">
        <PageHeader
            :title="`Editar ${quotation.folio} v${quotation.version}`"
            subtitle="Solo cotizaciones en borrador pueden editarse."
        />

        <FormSection
            title="Borrador editable"
            description="Actualiza cliente, partidas y condiciones."
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
                        <v-text-field
                            v-model="form.valid_until"
                            type="date"
                            label="Vigencia"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            v-model="form.commercial_terms"
                            label="Condiciones comerciales"
                            rows="3"
                        />
                    </v-col>
                </v-row>

                <div class="d-flex align-center justify-space-between mt-4 mb-3">
                    <h3 class="text-subtitle-1 font-weight-bold mb-0">Partidas</h3>
                    <v-btn
                        type="button"
                        variant="tonal"
                        prepend-icon="mdi-plus"
                        @click="addItem"
                    >
                        Agregar partida
                    </v-btn>
                </div>

                <PanelCard
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="mb-3"
                    :title="`Partida ${index + 1}`"
                >
                    <template #actions>
                        <v-btn
                            type="button"
                            variant="text"
                            color="error"
                            size="small"
                            :disabled="form.items.length === 1"
                            @click="removeItem(index)"
                        >
                            Quitar
                        </v-btn>
                    </template>

                    <v-row dense>
                        <v-col cols="12" md="3">
                            <v-select
                                v-model="item.item_type"
                                :items="itemTypeItems"
                                label="Tipo"
                            />
                        </v-col>
                        <v-col cols="12" md="5">
                            <v-select
                                v-if="item.item_type === 'service'"
                                v-model="item.service_catalog_id"
                                :items="serviceItems"
                                label="Servicio"
                                @update:model-value="onCatalogChange(index)"
                            />
                            <v-select
                                v-else
                                v-model="item.part_catalog_id"
                                :items="partItems"
                                label="Refacción"
                                @update:model-value="onCatalogChange(index)"
                            />
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-text-field v-model="item.description" label="Descripción" />
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-text-field
                                v-model.number="item.quantity"
                                type="number"
                                step="0.01"
                                label="Cantidad"
                            />
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-text-field
                                v-model.number="item.unit_price"
                                type="number"
                                step="0.01"
                                label="Precio unitario"
                            />
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-text-field
                                v-model.number="item.discount"
                                type="number"
                                step="0.01"
                                label="Descuento"
                            />
                        </v-col>
                    </v-row>
                </PanelCard>

                <v-alert type="info" variant="tonal" density="comfortable">
                    Estimado — Total: <MoneyText :amount="estimated.total" />
                </v-alert>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Guardar cambios"
                    @cancel="router.visit(route('quotations.show', quotation.id))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
