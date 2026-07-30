<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';

defineProps<{
    can?: Record<string, boolean>;
}>();

const reports = [
    {
        title: 'Historial por unidad',
        description: 'Órdenes, servicios y refacciones de una unidad en un periodo.',
        href: 'reports.vehicle-history',
        icon: 'mdi-truck-outline',
    },
    {
        title: 'Órdenes por periodo',
        description: 'Filtra por fechas de recepción, entrega o terminación.',
        href: 'reports.orders',
        icon: 'mdi-wrench-outline',
    },
    {
        title: 'Cotizaciones por estado',
        description: 'Emisiones, estados y tasa de conversión a orden.',
        href: 'reports.quotations',
        icon: 'mdi-file-document-outline',
    },
] as const;
</script>

<template>
    <AppLayout title="Reportes">
        <PageHeader
            title="Reportes"
            subtitle="Consultas operativas y exportación."
        />

        <v-row>
            <v-col
                v-for="report in reports"
                :key="report.href"
                cols="12"
                md="4"
            >
                <Link :href="route(report.href)" class="text-decoration-none">
                    <PanelCard :title="report.title">
                        <template #actions>
                            <v-avatar color="primary" variant="tonal" size="40" rounded="lg">
                                <v-icon :icon="report.icon" />
                            </v-avatar>
                        </template>
                        <p class="text-body-2 text-medium-emphasis mb-0">
                            {{ report.description }}
                        </p>
                    </PanelCard>
                </Link>
            </v-col>
        </v-row>
    </AppLayout>
</template>
