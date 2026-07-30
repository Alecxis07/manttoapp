<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashSnackbar from '@/Components/Ui/FlashSnackbar.vue';
import GlobalSearchInput from '@/Components/Ui/GlobalSearchInput.vue';
import NavSection from '@/Components/Ui/NavSection.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';
import UserMenu from '@/Components/Ui/UserMenu.vue';
import type { NavSectionData } from '@/Components/Ui/types';

const props = withDefaults(
    defineProps<{
        title?: string;
        navSections: NavSectionData[];
    }>(),
    {
        title: 'ManttoApp',
    },
);

const page = usePage();
const drawer = ref(true);
const globalSearch = ref('');

const canViewReports = computed(() => !!(page.props.can as Record<string, boolean> | undefined)?.viewReports);
const userEmail = computed(() => (page.props.auth as { user?: { email?: string } })?.user?.email ?? '');

watch(
    () => page.url,
    () => {
        if (window.innerWidth < 1280) {
            drawer.value = false;
        }
    },
);
</script>

<template>
    <v-app>
        <v-navigation-drawer
            v-model="drawer"
            theme="dark"
            color="#0F1419"
            width="280"
            :permanent="$vuetify.display.lgAndUp"
            :temporary="!$vuetify.display.lgAndUp"
        >
            <div class="mantto-hazard-rail" style="height: 6px; width: 100%" aria-hidden="true" />

            <div class="d-flex align-center ga-3 px-4 py-4">
                <Link
                    :href="route('dashboard')"
                    class="d-flex align-center ga-3 text-decoration-none"
                >
                    <v-avatar
                        color="primary"
                        size="36"
                        rounded="lg"
                        class="text-on-primary font-weight-bold"
                    >
                        M
                    </v-avatar>
                    <div class="min-w-0">
                        <div class="text-subtitle-1 font-weight-bold text-white text-uppercase">
                            Mantto
                        </div>
                        <div class="text-caption text-medium-emphasis text-uppercase">
                            Taller · flota
                        </div>
                    </div>
                </Link>
            </div>

            <v-divider class="border-opacity-25" />

            <div class="px-2 py-4 flex-grow-1 overflow-y-auto">
                <NavSection
                    v-for="section in props.navSections"
                    :key="section.label"
                    :label="section.label"
                    :items="section.items"
                />
            </div>

            <template #append>
                <div class="px-4 py-3">
                    <p class="text-caption text-medium-emphasis text-uppercase mb-1">
                        ManttoApp
                    </p>
                    <p class="text-caption text-truncate mb-0 text-white">
                        {{ userEmail }}
                    </p>
                </div>
            </template>
        </v-navigation-drawer>

        <v-app-bar flat border density="comfortable" color="surface">
            <v-app-bar-nav-icon
                class="d-lg-none"
                aria-label="Abrir menú"
                @click="drawer = !drawer"
            />

            <v-toolbar-title class="text-h6 font-weight-bold text-uppercase">
                {{ title }}
            </v-toolbar-title>

            <v-spacer />

            <GlobalSearchInput
                v-if="canViewReports"
                v-model="globalSearch"
                class="d-none d-md-flex me-3"
            />

            <ThemeToggle class="me-2" />
            <UserMenu />
        </v-app-bar>

        <v-main>
            <div v-if="$slots.header" class="border-b px-4 px-sm-6 py-4 bg-surface">
                <slot name="header" />
            </div>
            <div class="mantto-page">
                <slot />
            </div>
        </v-main>

        <FlashSnackbar />
    </v-app>
</template>
