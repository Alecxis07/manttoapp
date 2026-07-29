<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    settings: Object,
    sequences: Array,
    defaultMimes: Array,
});

const form = useForm({
    tax_iva_rate: props.settings.tax_iva_rate,
    attachments_max_size_kb: props.settings.attachments_max_size_kb,
    attachments_allowed_mimes: props.settings.attachments_allowed_mimes,
    sequences: props.sequences.map((sequence) => ({
        document_type: sequence.document_type,
        prefix: sequence.prefix,
        padding: sequence.padding,
        reset_counter: false,
    })),
});

const submit = () => {
    form.put(route('settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Configuración">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Configuración del sistema
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="$page.props.flash?.success"
                    class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded"
                >
                    {{ $page.props.flash.success }}
                </div>

                <form class="space-y-6" @submit.prevent="submit">
                    <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Impuestos</h3>
                        <p class="text-sm text-gray-500">
                            La tasa de IVA aplica solo a documentos nuevos; los existentes conservan su snapshot.
                        </p>
                        <div>
                            <InputLabel for="tax_iva_rate" value="Tasa de IVA (%)" />
                            <TextInput
                                id="tax_iva_rate"
                                v-model="form.tax_iva_rate"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="mt-1 block w-full sm:w-48"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.tax_iva_rate" />
                        </div>
                    </div>

                    <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Archivos adjuntos</h3>
                        <div>
                            <InputLabel for="attachments_max_size_kb" value="Tamaño máximo (KB)" />
                            <TextInput
                                id="attachments_max_size_kb"
                                v-model="form.attachments_max_size_kb"
                                type="number"
                                min="1"
                                class="mt-1 block w-full sm:w-48"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.attachments_max_size_kb" />
                        </div>
                        <div>
                            <InputLabel
                                for="attachments_allowed_mimes"
                                value="Tipos MIME permitidos (uno por línea o separados por coma)"
                            />
                            <textarea
                                id="attachments_allowed_mimes"
                                v-model="form.attachments_allowed_mimes"
                                rows="6"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.attachments_allowed_mimes" />
                            <p class="mt-2 text-xs text-gray-500">
                                Predeterminados: {{ defaultMimes.join(', ') }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Prefijos de folios</h3>
                        <p class="text-sm text-gray-500">
                            Cambiar el prefijo no reinicia el correlativo. Marca reinicio solo si es necesario.
                        </p>

                        <div
                            v-for="(sequence, index) in sequences"
                            :key="sequence.document_type"
                            class="border border-gray-100 rounded-lg p-4 space-y-3"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ sequence.label }}</p>
                                    <p class="text-xs text-gray-500">
                                        Actual: {{ sequence.prefix }}-{{ sequence.year }}-{{ String(sequence.last_number).padStart(sequence.padding, '0') }}
                                    </p>
                                </div>
                                <label class="inline-flex items-center text-sm text-gray-600">
                                    <input
                                        v-model="form.sequences[index].reset_counter"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    >
                                    <span class="ms-2">Reiniciar correlativo</span>
                                </label>
                            </div>
                            <input v-model="form.sequences[index].document_type" type="hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel :for="`prefix_${index}`" value="Prefijo" />
                                    <TextInput
                                        :id="`prefix_${index}`"
                                        v-model="form.sequences[index].prefix"
                                        class="mt-1 block w-full uppercase"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors[`sequences.${index}.prefix`]" />
                                </div>
                                <div>
                                    <InputLabel :for="`padding_${index}`" value="Dígitos del correlativo" />
                                    <TextInput
                                        :id="`padding_${index}`"
                                        v-model="form.sequences[index].padding"
                                        type="number"
                                        min="1"
                                        max="10"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors[`sequences.${index}.padding`]" />
                                </div>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.sequences" />
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton :disabled="form.processing">
                            Guardar configuración
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
