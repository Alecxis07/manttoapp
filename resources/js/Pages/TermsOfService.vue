<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps<{
    terms: string;
}>();
</script>

<template>
    <GuestLayout title="Términos de servicio" :max-width="720">
        <div class="text-body-2 text-high-emphasis" v-html="terms" />
    </GuestLayout>
</template>
