<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';
import { useAppPage } from '@/Composables/useAppPage';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
}>();

const page = useAppPage();
const authUser = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Bienvenido" />

    <v-app>
        <v-main class="bg-background">
            <div class="mantto-hazard-rail" style="height: 6px; width: 100%" aria-hidden="true" />

            <v-container class="py-8" style="max-width: 960px">
                <header class="d-flex align-center justify-space-between flex-wrap ga-4 mb-12">
                    <div class="d-flex align-center ga-3">
                        <v-avatar
                            color="primary"
                            size="44"
                            rounded="lg"
                            class="text-on-primary font-weight-bold"
                        >
                            M
                        </v-avatar>
                        <div>
                            <div class="text-h5 font-weight-bold text-high-emphasis text-uppercase lh-1">
                                Mantto
                            </div>
                            <div class="text-caption text-medium-emphasis text-uppercase">
                                Taller · flota
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-center ga-2">
                        <ThemeToggle />
                        <template v-if="canLogin">
                            <v-btn
                                v-if="authUser"
                                :href="route('dashboard')"
                                color="primary"
                                variant="flat"
                            >
                                Dashboard
                            </v-btn>
                            <template v-else>
                                <v-btn :href="route('login')" variant="text">
                                    Iniciar sesión
                                </v-btn>
                                <v-btn
                                    v-if="canRegister"
                                    :href="route('register')"
                                    color="primary"
                                    variant="flat"
                                >
                                    Registrarse
                                </v-btn>
                            </template>
                        </template>
                    </div>
                </header>

                <section class="mb-12">
                    <h1 class="text-h3 font-weight-bold text-high-emphasis mb-4" style="max-width: 18ch">
                        Mantenimiento de flota, sin ruido
                    </h1>
                    <p class="text-body-1 text-medium-emphasis mb-8" style="max-width: 42ch">
                        Órdenes, cotizaciones, facturación y catálogos en un solo lugar para tu taller.
                    </p>
                    <div class="d-flex flex-wrap ga-3">
                        <v-btn
                            v-if="!authUser && canLogin"
                            :href="route('login')"
                            color="primary"
                            variant="flat"
                            size="large"
                        >
                            Entrar a Mantto
                        </v-btn>
                        <v-btn
                            v-else-if="authUser"
                            :href="route('dashboard')"
                            color="primary"
                            variant="flat"
                            size="large"
                        >
                            Ir al dashboard
                        </v-btn>
                        <v-btn
                            v-if="!authUser && canRegister"
                            :href="route('register')"
                            variant="outlined"
                            size="large"
                        >
                            Crear cuenta
                        </v-btn>
                    </div>
                </section>

                <v-row>
                    <v-col cols="12" md="4">
                        <div class="mb-6">
                            <v-icon icon="mdi-wrench-outline" color="primary" size="28" class="mb-2" />
                            <h2 class="text-subtitle-1 font-weight-bold mb-1">Órdenes</h2>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Seguimiento de trabajo en taller con estados claros.
                            </p>
                        </div>
                    </v-col>
                    <v-col cols="12" md="4">
                        <div class="mb-6">
                            <v-icon icon="mdi-file-document-outline" color="primary" size="28" class="mb-2" />
                            <h2 class="text-subtitle-1 font-weight-bold mb-1">Cotizaciones</h2>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Presupuestos listos para convertir en órdenes.
                            </p>
                        </div>
                    </v-col>
                    <v-col cols="12" md="4">
                        <div class="mb-6">
                            <v-icon icon="mdi-truck-outline" color="primary" size="28" class="mb-2" />
                            <h2 class="text-subtitle-1 font-weight-bold mb-1">Unidades</h2>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Historial por vehículo y cliente de flota.
                            </p>
                        </div>
                    </v-col>
                </v-row>

                <footer class="mt-12 pt-6 text-caption text-medium-emphasis">
                    Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
                </footer>
            </v-container>
        </v-main>
    </v-app>
</template>
