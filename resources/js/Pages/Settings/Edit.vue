<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';

interface SequenceRow {
    document_type: string;
    label: string;
    prefix: string;
    year: number | string;
    last_number: number;
    padding: number;
}

const props = defineProps<{
    settings: {
        tax_iva_rate: number | string;
        attachments_max_size_kb: number | string;
        attachments_allowed_mimes: string;
    };
    sequences: SequenceRow[];
    defaultMimes: string[];
}>();

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

function submit(): void {
    form.put(route('settings.update'), {
        preserveScroll: true,
    });
}

function currentExample(sequence: SequenceRow): string {
    return `${sequence.prefix}-${sequence.year}-${String(sequence.last_number).padStart(sequence.padding, '0')}`;
}
</script>

<template>
    <AppLayout title="Configuración">
        <PageHeader
            title="Configuración del sistema"
            subtitle="Impuestos, adjuntos y prefijos de folios."
        />

        <form class="d-flex flex-column ga-6" @submit.prevent="submit">
            <PanelCard
                title="Impuestos"
                subtitle="La tasa de IVA aplica solo a documentos nuevos."
            >
                <v-text-field
                    v-model="form.tax_iva_rate"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    label="Tasa de IVA (%)"
                    style="max-width: 12rem"
                    :error-messages="form.errors.tax_iva_rate"
                />
            </PanelCard>

            <PanelCard title="Archivos adjuntos">
                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field
                            v-model="form.attachments_max_size_kb"
                            type="number"
                            min="1"
                            label="Tamaño máximo (KB)"
                            :error-messages="form.errors.attachments_max_size_kb"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            v-model="form.attachments_allowed_mimes"
                            label="Tipos MIME permitidos (uno por línea o separados por coma)"
                            rows="6"
                            class="font-mono"
                            :error-messages="form.errors.attachments_allowed_mimes"
                        />
                        <p class="text-caption text-medium-emphasis mb-0">
                            Predeterminados: {{ defaultMimes.join(', ') }}
                        </p>
                    </v-col>
                </v-row>
            </PanelCard>

            <PanelCard
                title="Prefijos de folios"
                subtitle="Cambiar el prefijo no reinicia el correlativo. Marca reinicio solo si es necesario."
            >
                <div
                    v-for="(sequence, index) in sequences"
                    :key="sequence.document_type"
                    class="mb-4"
                >
                    <v-card variant="outlined" class="pa-4">
                        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-3">
                            <div>
                                <div class="font-weight-medium">{{ sequence.label }}</div>
                                <div class="text-caption text-medium-emphasis">
                                    Actual: {{ currentExample(sequence) }}
                                </div>
                            </div>
                            <v-checkbox
                                v-model="form.sequences[index].reset_counter"
                                label="Reiniciar correlativo"
                                hide-details
                                density="compact"
                            />
                        </div>
                        <input v-model="form.sequences[index].document_type" type="hidden">
                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.sequences[index].prefix"
                                    label="Prefijo"
                                    class="text-uppercase"
                                    :error-messages="form.errors[`sequences.${index}.prefix`]"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.sequences[index].padding"
                                    type="number"
                                    min="1"
                                    max="10"
                                    label="Dígitos del correlativo"
                                    :error-messages="form.errors[`sequences.${index}.padding`]"
                                />
                            </v-col>
                        </v-row>
                    </v-card>
                </div>
                <v-alert
                    v-if="form.errors.sequences"
                    type="error"
                    variant="tonal"
                >
                    {{ form.errors.sequences }}
                </v-alert>
            </PanelCard>

            <FormActions
                :processing="form.processing"
                save-text="Guardar configuración"
                :show-cancel="false"
            />
        </form>
    </AppLayout>
</template>
