<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import PanelCard from '@/Components/Ui/PanelCard.vue';

defineProps<{
    query: string;
    customers: Array<{
        id: number;
        name: string;
        email?: string | null;
        url: string;
    }>;
    vehicles: Array<{
        id: number;
        license_plate: string;
        brand?: string;
        model?: string;
        customer_name?: string;
        url: string;
    }>;
    orders: Array<{
        id: number;
        folio: string;
        status_label?: string;
        customer_name?: string;
        license_plate?: string;
        url: string;
    }>;
}>();
</script>

<template>
    <AppLayout title="Búsqueda">
        <PageHeader
            title="Búsqueda global"
            :subtitle="query ? `Resultados para: ${query}` : 'Escribe un término en la barra de búsqueda.'"
        />

        <PanelCard v-if="!query">
            <EmptyState
                title="Sin término de búsqueda"
                message="Usa la barra de búsqueda del encabezado para encontrar clientes, unidades u órdenes."
                icon="mdi-magnify"
            />
        </PanelCard>

        <template v-else>
            <div class="d-flex flex-column ga-4">
                <PanelCard title="Clientes">
                    <v-list v-if="customers.length" lines="two">
                        <Link
                            v-for="customer in customers"
                            :key="customer.id"
                            :href="customer.url"
                            class="text-decoration-none"
                        >
                            <v-list-item
                                :title="customer.name"
                                :subtitle="customer.email || undefined"
                            />
                        </Link>
                    </v-list>
                    <p v-else class="text-body-2 text-medium-emphasis mb-0">Sin resultados</p>
                </PanelCard>

                <PanelCard title="Unidades">
                    <v-list v-if="vehicles.length" lines="two">
                        <Link
                            v-for="vehicle in vehicles"
                            :key="vehicle.id"
                            :href="vehicle.url"
                            class="text-decoration-none"
                        >
                            <v-list-item
                                :title="vehicle.license_plate"
                                :subtitle="`${vehicle.brand ?? ''} ${vehicle.model ?? ''} — ${vehicle.customer_name ?? ''}`.trim()"
                            />
                        </Link>
                    </v-list>
                    <p v-else class="text-body-2 text-medium-emphasis mb-0">Sin resultados</p>
                </PanelCard>

                <PanelCard title="Órdenes">
                    <v-list v-if="orders.length" lines="two">
                        <Link
                            v-for="order in orders"
                            :key="order.id"
                            :href="order.url"
                            class="text-decoration-none"
                        >
                            <v-list-item
                                :title="order.folio"
                                :subtitle="`${order.status_label ?? ''} — ${order.customer_name ?? ''} — ${order.license_plate ?? ''}`.trim()"
                            />
                        </Link>
                    </v-list>
                    <p v-else class="text-body-2 text-medium-emphasis mb-0">Sin resultados</p>
                </PanelCard>
            </div>
        </template>
    </AppLayout>
</template>
