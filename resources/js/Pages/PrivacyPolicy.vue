<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps<{
    policy: string;
}>();
</script>

<template>
    <GuestLayout title="Política de privacidad" :max-width="720">
        <div class="text-body-2 text-high-emphasis" v-html="policy" />
    </GuestLayout>
</template>
