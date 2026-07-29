<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    as: String,
});
</script>

<template>
    <div>
        <button
            v-if="as == 'button'"
            type="submit"
            class="block w-full px-4 py-2 text-start text-sm leading-5 text-steel-700 outline-none transition duration-150 ease-in-out hover:bg-steel-100 focus:bg-steel-100 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-hazard dark:text-steel-200 dark:hover:bg-steel-800 dark:focus:bg-steel-800"
        >
            <slot />
        </button>

        <a
            v-else-if="as =='a'"
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-steel-700 outline-none transition duration-150 ease-in-out hover:bg-steel-100 focus:bg-steel-100 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-hazard dark:text-steel-200 dark:hover:bg-steel-800 dark:focus:bg-steel-800"
        >
            <slot />
        </a>

        <Link
            v-else
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-steel-700 outline-none transition duration-150 ease-in-out hover:bg-steel-100 focus:bg-steel-100 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-hazard dark:text-steel-200 dark:hover:bg-steel-800 dark:focus:bg-steel-800"
        >
            <slot />
        </Link>
    </div>
</template>
