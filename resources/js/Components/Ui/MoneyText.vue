<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        amount: number | string | null | undefined;
        currency?: string;
        locale?: string;
    }>(),
    {
        currency: 'MXN',
        locale: 'es-MX',
    },
);

const formatted = computed(() => {
    const value = Number(props.amount ?? 0);

    if (Number.isNaN(value)) {
        return '—';
    }

    return value.toLocaleString(props.locale, {
        style: 'currency',
        currency: props.currency,
    });
});
</script>

<template>
    <span class="font-weight-medium text-no-wrap">{{ formatted }}</span>
</template>
