<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    customer: Object,
    types: Array,
    statuses: Array,
});

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
    })),
});

const isCompany = computed(() => form.type === 'company');

const addFiscalProfile = () => {
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
};

const removeFiscalProfile = (index) => {
    form.fiscal_profiles.splice(index, 1);
    if (form.fiscal_profiles.length > 0 && !form.fiscal_profiles.some((p) => p.is_default)) {
        form.fiscal_profiles[0].is_default = true;
    }
};

const setDefaultProfile = (index) => {
    form.fiscal_profiles.forEach((profile, i) => {
        profile.is_default = i === index;
    });
};

const submit = () => {
    form.put(route('customers.update', props.customer.id));
};
</script>

<template>
    <AppLayout title="Editar cliente">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar cliente
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
                <FormSection @submitted="submit">
                    <template #title>
                        Datos generales
                    </template>

                    <template #description>
                        Actualiza datos del cliente y su estatus. El historial se conserva.
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
                            Actualizar
                        </PrimaryButton>
                    </template>
                </FormSection>

                <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Perfiles fiscales</h3>
                            <p class="text-sm text-gray-600">Uno debe marcarse como predeterminado (RN-CLI-003).</p>
                        </div>
                        <SecondaryButton type="button" @click="addFiscalProfile">
                            Agregar perfil
                        </SecondaryButton>
                    </div>

                    <div
                        v-for="(profile, index) in form.fiscal_profiles"
                        :key="profile.id ?? `new-${index}`"
                        class="border border-gray-200 rounded-lg p-4 space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h4 class="font-medium text-gray-800">Perfil {{ index + 1 }}</h4>
                            <button type="button" class="text-sm text-red-600 hover:text-red-800" @click="removeFiscalProfile(index)">
                                Eliminar
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel :for="`legal_name_${index}`" value="Razón social / Nombre fiscal" />
                                <TextInput :id="`legal_name_${index}`" v-model="profile.legal_name" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors[`fiscal_profiles.${index}.legal_name`]" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel :for="`rfc_${index}`" value="RFC" />
                                <TextInput :id="`rfc_${index}`" v-model="profile.rfc" type="text" class="mt-1 block w-full uppercase" maxlength="13" />
                                <InputError :message="form.errors[`fiscal_profiles.${index}.rfc`]" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel :for="`tax_regime_${index}`" value="Régimen fiscal" />
                                <TextInput :id="`tax_regime_${index}`" v-model="profile.tax_regime_code" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel :for="`cfdi_use_${index}`" value="Uso CFDI" />
                                <TextInput :id="`cfdi_use_${index}`" v-model="profile.cfdi_use_code" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel :for="`postal_${index}`" value="Código postal" />
                                <TextInput :id="`postal_${index}`" v-model="profile.postal_code" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel :for="`fiscal_email_${index}`" value="Email fiscal" />
                                <TextInput :id="`fiscal_email_${index}`" v-model="profile.email" type="email" class="mt-1 block w-full" />
                            </div>
                            <div class="flex items-center">
                                <Checkbox
                                    :id="`is_default_${index}`"
                                    :checked="profile.is_default"
                                    @update:checked="(checked) => checked && setDefaultProfile(index)"
                                />
                                <InputLabel :for="`is_default_${index}`" value="Predeterminado" class="ms-2" />
                            </div>
                        </div>
                    </div>
                    <InputError :message="form.errors.fiscal_profiles" class="mt-2" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
