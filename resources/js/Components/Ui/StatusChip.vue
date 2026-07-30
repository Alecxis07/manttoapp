<script setup lang="ts">
import { computed } from 'vue';

export type StatusMeta = {
    label: string;
    color: string;
};

const props = withDefaults(
    defineProps<{
        status: string;
        map: Record<string, StatusMeta>;
        size?: string;
    }>(),
    {
        size: 'small',
    },
);

const meta = computed((): StatusMeta =>
    props.map[props.status] ?? {
        label: props.status,
        color: 'default',
    },
);
</script>

<template>
    <v-chip :color="meta.color" :size="size" variant="tonal" label>
        {{ meta.label }}
    </v-chip>
</template>
