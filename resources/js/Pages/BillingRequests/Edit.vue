<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    billingRequest: Object,
    fiscalProfiles: Array,
    paymentMethods: Array,
    paymentForms: Array,
});

const form = useForm({
    customer_fiscal_profile_id: props.billingRequest.customer_fiscal_profile_id ?? '',
    payment_method_code: props.billingRequest.payment_method_code ?? 'PUE',
    payment_form_code: props.billingRequest.payment_form_code ?? '03',
    notes: props.billingRequest.notes ?? '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        customer_fiscal_profile_id: data.customer_fiscal_profile_id || null,
    })).put(route('billing-requests.update', props.billingRequest.id));
};
</script>

<template>
    <AppLayout :title="`Editar ${billingRequest.folio}`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar {{ billingRequest.folio }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <form class="bg-white shadow-xl sm:rounded-lg p-6 space-y-6" @submit.prevent="submit">
                    <div>
                        <InputLabel value="Perfil fiscal" />
                        <select
                            v-model="form.customer_fiscal_profile_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Mantener snapshot actual</option>
                            <option
                                v-for="option in fiscalProfiles"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.customer_fiscal_profile_id" />
                        <p class="mt-2 text-xs text-gray-500">
                            Al seleccionar un perfil se regenera el snapshot fiscal de la solicitud.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Método de pago (SAT)" />
                            <select
                                v-model="form.payment_method_code"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option
                                    v-for="option in paymentMethods"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.payment_method_code" />
                        </div>
                        <div>
                            <InputLabel value="Forma de pago (SAT)" />
                            <select
                                v-model="form.payment_form_code"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option
                                    v-for="option in paymentForms"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.payment_form_code" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="notes" value="Observaciones" />
                        <TextInput
                            id="notes"
                            v-model="form.notes"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
