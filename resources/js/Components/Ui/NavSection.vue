<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { NavItem } from '@/Components/Ui/types';

defineProps<{
    label: string;
    items: NavItem[];
}>();
</script>

<template>
    <div class="mb-4">
        <p class="text-overline text-medium-emphasis px-4 mb-1">
            {{ label }}
        </p>
        <v-list nav density="compact" class="bg-transparent py-0">
            <Link
                v-for="item in items"
                :key="item.key"
                :href="item.href"
                class="text-decoration-none"
            >
                <v-list-item
                    :active="item.active"
                    :prepend-icon="item.icon"
                    :title="item.label"
                    color="primary"
                    rounded="lg"
                    class="mb-1"
                />
            </Link>
        </v-list>
    </div>
</template>
