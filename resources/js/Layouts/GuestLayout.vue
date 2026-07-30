<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';

withDefaults(
    defineProps<{
        title?: string;
        maxWidth?: string | number;
        subtitle?: string;
    }>(),
    {
        title: undefined,
        maxWidth: 460,
        subtitle: undefined,
    },
);
</script>

<template>
    <v-app>
        <Head v-if="title" :title="title" />

        <v-main class="bg-background">
            <div
                class="d-flex align-center justify-center px-4 py-8"
                style="min-height: 100dvh"
            >
                <div class="d-flex flex-column align-center ga-6 w-100" :style="{ maxWidth: typeof maxWidth === 'number' ? `${maxWidth}px` : maxWidth }">
                    <div class="d-flex align-center justify-space-between w-100">
                        <Link
                            href="/"
                            class="d-flex align-center ga-3 text-decoration-none"
                        >
                            <v-avatar
                                color="primary"
                                size="40"
                                rounded="lg"
                                class="text-on-primary font-weight-bold"
                            >
                                M
                            </v-avatar>
                            <div class="min-w-0">
                                <div class="text-h6 font-weight-bold text-high-emphasis text-uppercase lh-1">
                                    Mantto
                                </div>
                                <div class="text-caption text-medium-emphasis text-uppercase">
                                    Taller · flota
                                </div>
                            </div>
                        </Link>
                        <ThemeToggle />
                    </div>

                    <v-card width="100%" class="pa-1">
                        <v-card-title v-if="title || subtitle" class="pb-0">
                            <div v-if="title" class="text-h6 font-weight-bold">
                                {{ title }}
                            </div>
                            <div v-if="subtitle" class="text-body-2 text-medium-emphasis font-weight-regular mt-1">
                                {{ subtitle }}
                            </div>
                        </v-card-title>
                        <v-card-text>
                            <slot />
                        </v-card-text>
                    </v-card>

                    <slot name="footer" />
                </div>
            </div>
        </v-main>
    </v-app>
</template>
