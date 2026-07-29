<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    types: Array,
    statuses: Array,
});

const includeFiscalProfile = ref(false);

const form = useForm({
    type: 'individual',
    name: '',
    trade_name: '',
    phone: '',
    email: '',
    status: 'active',
    fiscal_profiles: [],
});

const isCompany = computed(() => form.type === 'company');

const emptyFiscalProfile = () => ({
    legal_name: '',
    rfc: '',
    tax_regime_code: '',
    cfdi_use_code: '',
    postal_code: '',
    email: '',
    is_default: true,
});

const toggleFiscalProfile = () => {
    includeFiscalProfile.value = !includeFiscalProfile.value;
    form.fiscal_profiles = includeFiscalProfile.value ? [emptyFiscalProfile()] : [];
};

const submit = () => {
    form.post(route('customers.store'));
};
</script>

<template>
    <AppLayout title="Nuevo cliente">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo cliente
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos generales
                    </template>

                    <template #description>
                        Registra una persona física o moral. El cliente quedará activo por defecto.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Tipo" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option v-for="option in types" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" :value="isCompany ? 'Razón social' : 'Nombre completo'" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div v-if="isCompany" class="col-span-6 sm:col-span-4">
                            <InputLabel for="trade_name" value="Nombre comercial" />
                            <TextInput id="trade_name" v-model="form.trade_name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.trade_name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="phone" value="Teléfono" />
                            <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.phone" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="status" value="Estatus" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option v-for="option in statuses" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </template>
                </FormSection>

                <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Perfil fiscal (opcional)</h3>
                            <p class="text-sm text-gray-600">RFC, régimen, uso CFDI y CP para facturación.</p>
                        </div>
                        <SecondaryButton type="button" @click="toggleFiscalProfile">
                            {{ includeFiscalProfile ? 'Quitar perfil' : 'Agregar perfil' }}
                        </SecondaryButton>
                    </div>

                    <div v-if="includeFiscalProfile && form.fiscal_profiles[0]" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="legal_name" value="Razón social / Nombre fiscal" />
                            <TextInput id="legal_name" v-model="form.fiscal_profiles[0].legal_name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors['fiscal_profiles.0.legal_name']" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="rfc" value="RFC" />
                            <TextInput id="rfc" v-model="form.fiscal_profiles[0].rfc" type="text" class="mt-1 block w-full uppercase" maxlength="13" />
                            <InputError :message="form.errors['fiscal_profiles.0.rfc']" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="tax_regime_code" value="Régimen fiscal" />
                            <TextInput id="tax_regime_code" v-model="form.fiscal_profiles[0].tax_regime_code" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors['fiscal_profiles.0.tax_regime_code']" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="cfdi_use_code" value="Uso CFDI" />
                            <TextInput id="cfdi_use_code" v-model="form.fiscal_profiles[0].cfdi_use_code" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors['fiscal_profiles.0.cfdi_use_code']" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="postal_code" value="Código postal" />
                            <TextInput id="postal_code" v-model="form.fiscal_profiles[0].postal_code" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors['fiscal_profiles.0.postal_code']" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="fiscal_email" value="Email fiscal" />
                            <TextInput id="fiscal_email" v-model="form.fiscal_profiles[0].email" type="email" class="mt-1 block w-full" />
                            <InputError :message="form.errors['fiscal_profiles.0.email']" class="mt-2" />
                        </div>
                        <div class="flex items-center">
                            <Checkbox id="is_default" v-model:checked="form.fiscal_profiles[0].is_default" />
                            <InputLabel for="is_default" value="Perfil predeterminado" class="ms-2" />
                        </div>
                    </div>
                    <InputError :message="form.errors.fiscal_profiles" class="mt-2" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
