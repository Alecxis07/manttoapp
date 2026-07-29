<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    orders: Array,
    quotations: Array,
    paymentMethods: Array,
    paymentForms: Array,
    prefill: Object,
    fiscalProfiles: Array,
});

const form = useForm({
    maintenance_order_id: props.prefill?.maintenance_order_id ?? '',
    quotation_id: props.prefill?.quotation_id ?? '',
    customer_fiscal_profile_id: '',
    payment_method_code: 'PUE',
    payment_form_code: '03',
    notes: '',
});

const clearOtherOrigin = (field) => {
    if (field === 'order' && form.maintenance_order_id) {
        form.quotation_id = '';
    }
    if (field === 'quotation' && form.quotation_id) {
        form.maintenance_order_id = '';
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        maintenance_order_id: data.maintenance_order_id || null,
        quotation_id: data.quotation_id || null,
        customer_fiscal_profile_id: data.customer_fiscal_profile_id || null,
    })).post(route('billing-requests.store'));
};
</script>

<template>
    <AppLayout title="Nueva solicitud de facturación">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva solicitud de facturación
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <form class="bg-white shadow-xl sm:rounded-lg p-6 space-y-6" @submit.prevent="submit">
                    <div>
                        <InputLabel value="Orden de origen" />
                        <select
                            v-model="form.maintenance_order_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            @change="clearOtherOrigin('order')"
                        >
                            <option value="">— Sin orden —</option>
                            <option
                                v-for="option in orders"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.maintenance_order_id || form.errors.origin" />
                    </div>

                    <div>
                        <InputLabel value="Cotización de origen" />
                        <select
                            v-model="form.quotation_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            @change="clearOtherOrigin('quotation')"
                        >
                            <option value="">— Sin cotización —</option>
                            <option
                                v-for="option in quotations"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.quotation_id" />
                    </div>

                    <div v-if="fiscalProfiles?.length">
                        <InputLabel value="Perfil fiscal" />
                        <select
                            v-model="form.customer_fiscal_profile_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Perfil default del cliente</option>
                            <option
                                v-for="option in fiscalProfiles"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.customer_fiscal_profile_id" />
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
                            Crear solicitud
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
