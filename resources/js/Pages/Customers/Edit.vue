<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';

interface SelectOption {
    value: string;
    label: string;
}

interface FiscalProfileForm {
    id: number | null;
    legal_name: string;
    rfc: string;
    tax_regime_code: string;
    cfdi_use_code: string;
    postal_code: string;
    email: string;
    is_default: boolean;
}

interface CustomerProp {
    id: number;
    type: string;
    name: string;
    trade_name?: string | null;
    phone?: string | null;
    email?: string | null;
    status: string;
    fiscal_profiles?: Array<{
        id: number;
        legal_name: string;
        rfc: string;
        tax_regime_code: string;
        cfdi_use_code: string;
        postal_code: string;
        email?: string | null;
        is_default: boolean;
    }>;
}

const props = defineProps<{
    customer: CustomerProp;
    types: SelectOption[];
    statuses: SelectOption[];
}>();

const form = useForm({
    type: props.customer.type,
    name: props.customer.name,
    trade_name: props.customer.trade_name ?? '',
    phone: props.customer.phone ?? '',
    email: props.customer.email ?? '',
    status: props.customer.status,
    fiscal_profiles: (props.customer.fiscal_profiles ?? []).map((profile) => ({
        id: profile.id,
        legal_name: profile.legal_name,
        rfc: profile.rfc,
        tax_regime_code: profile.tax_regime_code,
        cfdi_use_code: profile.cfdi_use_code,
        postal_code: profile.postal_code,
        email: profile.email ?? '',
        is_default: profile.is_default,
    })) as FiscalProfileForm[],
});

const isCompany = computed(() => form.type === 'company');

const typeItems = computed(() =>
    props.types.map((option) => ({ value: option.value, title: option.label })),
);

const statusItems = computed(() =>
    props.statuses.map((option) => ({ value: option.value, title: option.label })),
);

function addFiscalProfile(): void {
    form.fiscal_profiles.push({
        id: null,
        legal_name: '',
        rfc: '',
        tax_regime_code: '',
        cfdi_use_code: '',
        postal_code: '',
        email: '',
        is_default: form.fiscal_profiles.length === 0,
    });
}

function removeFiscalProfile(index: number): void {
    form.fiscal_profiles.splice(index, 1);
    if (form.fiscal_profiles.length > 0 && !form.fiscal_profiles.some((p) => p.is_default)) {
        form.fiscal_profiles[0].is_default = true;
    }
}

function setDefaultProfile(index: number): void {
    form.fiscal_profiles.forEach((profile, i) => {
        profile.is_default = i === index;
    });
}

function submit(): void {
    form.put(route('customers.update', props.customer.id));
}
</script>

<template>
    <AppLayout title="Editar cliente">
        <PageHeader
            title="Editar cliente"
            :subtitle="customer.name"
            :breadcrumbs="[
                { title: 'Clientes', href: route('customers.index') },
                { title: customer.name, href: route('customers.show', customer.id) },
                { title: 'Editar', disabled: true },
            ]"
        />

        <div class="d-flex flex-column ga-6">
            <FormSection
                title="Datos generales"
                description="Actualiza datos del cliente y su estatus. El historial se conserva."
                @submitted="submit"
            >
                <template #form>
                    <v-row>
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
                                v-model="form.name"
                                :label="isCompany ? 'Razón social' : 'Nombre completo'"
                                required
                                autofocus
                                :error-messages="form.errors.name"
                            />
                        </v-col>
                        <v-col v-if="isCompany" cols="12" md="6">
                            <v-text-field
                                v-model="form.trade_name"
                                label="Nombre comercial"
                                :error-messages="form.errors.trade_name"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="form.phone"
                                label="Teléfono"
                                :error-messages="form.errors.phone"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="form.email"
                                type="email"
                                label="Email"
                                :error-messages="form.errors.email"
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
                    </v-row>
                </template>
                <template #actions>
                    <FormActions
                        :processing="form.processing"
                        save-text="Actualizar"
                        @cancel="router.visit(route('customers.show', customer.id))"
                    />
                </template>
            </FormSection>

            <PanelCard
                title="Perfiles fiscales"
                subtitle="Uno debe marcarse como predeterminado (RN-CLI-003)."
            >
                <template #actions>
                    <v-btn variant="tonal" prepend-icon="mdi-plus" @click="addFiscalProfile">
                        Agregar perfil
                    </v-btn>
                </template>

                <div
                    v-for="(profile, index) in form.fiscal_profiles"
                    :key="profile.id ?? `new-${index}`"
                    class="mb-4"
                >
                    <v-card variant="outlined" class="pa-4">
                        <div class="d-flex align-center justify-space-between mb-3">
                            <h4 class="text-subtitle-1 font-weight-medium mb-0">
                                Perfil {{ index + 1 }}
                            </h4>
                            <v-btn
                                variant="text"
                                color="error"
                                size="small"
                                @click="removeFiscalProfile(index)"
                            >
                                Eliminar
                            </v-btn>
                        </div>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profile.legal_name"
                                    label="Razón social / Nombre fiscal"
                                    :error-messages="form.errors[`fiscal_profiles.${index}.legal_name`]"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profile.rfc"
                                    label="RFC"
                                    maxlength="13"
                                    class="text-uppercase"
                                    :error-messages="form.errors[`fiscal_profiles.${index}.rfc`]"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profile.tax_regime_code"
                                    label="Régimen fiscal"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profile.cfdi_use_code"
                                    label="Uso CFDI"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profile.postal_code"
                                    label="Código postal"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="profile.email"
                                    type="email"
                                    label="Email fiscal"
                                />
                            </v-col>
                            <v-col cols="12">
                                <v-checkbox
                                    :model-value="profile.is_default"
                                    label="Predeterminado"
                                    hide-details
                                    @update:model-value="(checked) => checked && setDefaultProfile(index)"
                                />
                            </v-col>
                        </v-row>
                    </v-card>
                </div>
                <v-alert
                    v-if="form.errors.fiscal_profiles"
                    type="error"
                    variant="tonal"
                >
                    {{ form.errors.fiscal_profiles }}
                </v-alert>
            </PanelCard>
        </div>
    </AppLayout>
</template>
