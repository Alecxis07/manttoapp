<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    const base =
        'group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium outline-none transition-[background-color,color,box-shadow] duration-150 focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 focus-visible:ring-offset-steel-950';

    if (props.active) {
        return `${base} bg-steel-800/90 text-white shadow-[inset_3px_0_0_0_#e8a317]`;
    }

    return `${base} text-steel-300 hover:bg-steel-800/60 hover:text-white`;
});
</script>

<template>
    <Link :href="href" :class="classes" :aria-current="active ? 'page' : undefined">
        <span
            class="flex size-5 shrink-0 items-center justify-center text-steel-400 group-hover:text-amber-400"
            :class="{ 'text-amber-400': active }"
            aria-hidden="true"
        >
            <slot name="icon" />
        </span>
        <span class="truncate">
            <slot />
        </span>
    </Link>
</template>
