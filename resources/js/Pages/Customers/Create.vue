<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
    legal_name: string;
    rfc: string;
    tax_regime_code: string;
    cfdi_use_code: string;
    postal_code: string;
    email: string;
    is_default: boolean;
}

const props = defineProps<{
    types: SelectOption[];
    statuses: SelectOption[];
}>();

const includeFiscalProfile = ref(false);

const form = useForm({
    type: 'individual',
    name: '',
    trade_name: '',
    phone: '',
    email: '',
    status: 'active',
    fiscal_profiles: [] as FiscalProfileForm[],
});

const isCompany = computed(() => form.type === 'company');

const typeItems = computed(() =>
    props.types.map((option) => ({ value: option.value, title: option.label })),
);

const statusItems = computed(() =>
    props.statuses.map((option) => ({ value: option.value, title: option.label })),
);

function emptyFiscalProfile(): FiscalProfileForm {
    return {
        legal_name: '',
        rfc: '',
        tax_regime_code: '',
        cfdi_use_code: '',
        postal_code: '',
        email: '',
        is_default: true,
    };
}

function toggleFiscalProfile(): void {
    includeFiscalProfile.value = !includeFiscalProfile.value;
    form.fiscal_profiles = includeFiscalProfile.value ? [emptyFiscalProfile()] : [];
}

function submit(): void {
    form.post(route('customers.store'));
}
</script>

<template>
    <AppLayout title="Nuevo cliente">
        <PageHeader
            title="Nuevo cliente"
            subtitle="Registra una persona física o moral."
            :breadcrumbs="[
                { title: 'Clientes', href: route('customers.index') },
                { title: 'Nuevo', disabled: true },
            ]"
        />

        <div class="d-flex flex-column ga-6">
            <FormSection
                title="Datos generales"
                description="El cliente quedará activo por defecto."
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
                        save-text="Guardar"
                        @cancel="router.visit(route('customers.index'))"
                    />
                </template>
            </FormSection>

            <PanelCard
                title="Perfil fiscal (opcional)"
                subtitle="RFC, régimen, uso CFDI y CP para facturación."
            >
                <template #actions>
                    <v-btn variant="tonal" @click="toggleFiscalProfile">
                        {{ includeFiscalProfile ? 'Quitar perfil' : 'Agregar perfil' }}
                    </v-btn>
                </template>

                <v-row v-if="includeFiscalProfile && form.fiscal_profiles[0]">
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fiscal_profiles[0].legal_name"
                            label="Razón social / Nombre fiscal"
                            :error-messages="form.errors['fiscal_profiles.0.legal_name']"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fiscal_profiles[0].rfc"
                            label="RFC"
                            maxlength="13"
                            class="text-uppercase"
                            :error-messages="form.errors['fiscal_profiles.0.rfc']"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fiscal_profiles[0].tax_regime_code"
                            label="Régimen fiscal"
                            :error-messages="form.errors['fiscal_profiles.0.tax_regime_code']"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fiscal_profiles[0].cfdi_use_code"
                            label="Uso CFDI"
                            :error-messages="form.errors['fiscal_profiles.0.cfdi_use_code']"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fiscal_profiles[0].postal_code"
                            label="Código postal"
                            :error-messages="form.errors['fiscal_profiles.0.postal_code']"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fiscal_profiles[0].email"
                            type="email"
                            label="Email fiscal"
                            :error-messages="form.errors['fiscal_profiles.0.email']"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-checkbox
                            v-model="form.fiscal_profiles[0].is_default"
                            label="Perfil predeterminado"
                            hide-details
                        />
                    </v-col>
                </v-row>
                <v-alert
                    v-if="form.errors.fiscal_profiles"
                    type="error"
                    variant="tonal"
                    class="mt-2"
                >
                    {{ form.errors.fiscal_profiles }}
                </v-alert>
            </PanelCard>
        </div>
    </AppLayout>
</template>
