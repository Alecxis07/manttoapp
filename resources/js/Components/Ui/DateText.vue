<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        value: string | number | Date | null | undefined;
        locale?: string;
        dateStyle?: 'full' | 'long' | 'medium' | 'short';
        timeStyle?: 'full' | 'long' | 'medium' | 'short' | null;
        fallback?: string;
    }>(),
    {
        locale: 'es-MX',
        dateStyle: 'medium',
        timeStyle: null,
        fallback: '—',
    },
);

const formatted = computed(() => {
    if (props.value === null || props.value === undefined || props.value === '') {
        return props.fallback;
    }

    const date = props.value instanceof Date ? props.value : new Date(props.value);

    if (Number.isNaN(date.getTime())) {
        return props.fallback;
    }

    const options: Intl.DateTimeFormatOptions = {
        dateStyle: props.dateStyle,
    };

    if (props.timeStyle) {
        options.timeStyle = props.timeStyle;
    }

    return date.toLocaleString(props.locale, options);
});
</script>

<template>
    <time v-if="value" :datetime="String(value)">{{ formatted }}</time>
    <span v-else>{{ fallback }}</span>
</template>
