<script setup>
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDark, toggle } = useDarkMode();
</script>

<template>
    <button
        type="button"
        class="inline-flex size-9 items-center justify-center rounded-md border border-steel-200 bg-white text-steel-600 shadow-sm outline-none transition-[background-color,border-color,color] duration-150 hover:border-amber-500/40 hover:text-amber-700 focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:border-steel-700 dark:bg-steel-900 dark:text-steel-300 dark:hover:border-amber-500/50 dark:hover:text-amber-400 dark:focus-visible:ring-offset-steel-950"
        :aria-label="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        :title="isDark ? 'Modo claro' : 'Modo oscuro'"
        @click="toggle"
    >
        <!-- Moon (show when light) -->
        <svg
            v-if="! isDark"
            class="size-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"
            />
        </svg>
        <!-- Sun (show when dark) -->
        <svg
            v-else
            class="size-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
            />
        </svg>
    </button>
</template>
